<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmationMail;
use App\Mail\BookingNotificationMail;
use App\Models\ChatConsultationBooking;
use App\Models\ConsultationService;
use App\Models\PhoneConsultationBooking;
use App\Models\SiteSetting;
use App\Models\ViberConsultationBooking;
use App\Support\ConsultationAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class PhoneConsultationController extends Controller
{
    public function __construct(
        private readonly ConsultationAvailabilityService $availability
    ) {}

    // ── Page ─────────────────────────────────────────────────────────

    public function show()
    {
        $pricing = ConsultationService::where('type', 'phone')->first();

        return view('pages.phone-consultation', compact('pricing'));
    }

    // ── Slots JSON endpoint ───────────────────────────────────────────

    /**
     * GET /consultation/phone/slots?date=YYYY-MM-DD
     */
    public function slots(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $date  = Carbon::createFromFormat('Y-m-d', $validated['date'], 'Europe/Sofia');
        $today = Carbon::now('Europe/Sofia')->startOfDay();

        if ($date->lt($today)) {
            return response()->json(['slots' => []]);
        }

        if ($date->gt($today->copy()->addMonths(3))) {
            return response()->json(['slots' => []]);
        }

        $slots = $this->availability->slotsForDate($date);

        // Filter out slots occupied by any blocking-status booking in EITHER table.
        // Phone and Viber share the same time resource.
        $dateStr = $date->toDateString();

        $phoneBlocked = PhoneConsultationBooking::whereIn('status', PhoneConsultationBooking::BLOCKING_STATUSES)
            ->whereDate('starts_at', $dateStr)
            ->pluck('starts_at')
            ->map(fn ($s) => Carbon::parse($s, 'Europe/Sofia')->format('H:i'));

        // A 60-minute Viber booking occupies two consecutive 30-min blocks.
        // We collect all 30-min grid times covered by each Viber booking.
        $viberBlocked = ViberConsultationBooking::whereIn('status', ViberConsultationBooking::BLOCKING_STATUSES)
            ->whereDate('starts_at', $dateStr)
            ->get(['starts_at', 'ends_at'])
            ->flatMap(function ($b) {
                $cursor = Carbon::parse($b->starts_at, 'Europe/Sofia');
                $end    = Carbon::parse($b->ends_at,   'Europe/Sofia');
                $times  = [];
                while ($cursor->lt($end)) {
                    $times[] = $cursor->format('H:i');
                    $cursor->addMinutes(30);
                }
                return $times;
            });

        // Chat bookings are always 30 min — each occupies exactly one grid block.
        $chatBlocked = ChatConsultationBooking::whereIn('status', ChatConsultationBooking::BLOCKING_STATUSES)
            ->whereDate('starts_at', $dateStr)
            ->pluck('starts_at')
            ->map(fn ($s) => Carbon::parse($s, 'Europe/Sofia')->format('H:i'));

        $blockedStarts = $phoneBlocked->merge($viberBlocked)->merge($chatBlocked)->flip();

        $available = array_filter(
            array_map(fn (Carbon $s) => $s->format('H:i'), $slots),
            fn (string $t) => ! isset($blockedStarts[$t])
        );

        return response()->json(['slots' => array_values($available)]);
    }

    // ── Submit booking ────────────────────────────────────────────────

    /**
     * POST /consultation/phone
     */
    public function submit(Request $request)
    {
        // ── Validate all fields ──────────────────────────────────────
        $data = $request->validate([
            'selected_date'  => ['required', 'date_format:Y-m-d'],
            'selected_slot'  => ['required', 'date_format:H:i'],
            'first_name'     => ['required', 'string', 'max:255'],
            'last_name'      => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255'],
            'phone'          => ['required', 'string', 'max:50'],
            'description'    => ['nullable', 'string', 'max:2000'],
            'payment_method' => ['required', 'in:card,easypay,epay'],
            'consent'        => ['accepted'],
        ], [
            'selected_date.required'  => 'Моля, изберете ден.',
            'selected_date.date_format' => 'Невалидна дата.',
            'selected_slot.required'  => 'Моля, изберете час.',
            'selected_slot.date_format' => 'Невалиден час.',
            'first_name.required'     => 'Моля, въведете Вашето име.',
            'last_name.required'      => 'Моля, въведете Вашата фамилия.',
            'email.required'          => 'Моля, въведете имейл адрес.',
            'email.email'             => 'Моля, въведете валиден имейл адрес.',
            'phone.required'          => 'Моля, въведете телефонен номер.',
            'payment_method.required' => 'Моля, изберете метод на плащане.',
            'payment_method.in'       => 'Невалиден метод на плащане.',
            'consent.accepted'        => 'Трябва да се съгласите с Политиката за поверителност.',
        ]);

        // ── Build slot datetimes ─────────────────────────────────────
        $startsAt = Carbon::createFromFormat(
            'Y-m-d H:i',
            $data['selected_date'] . ' ' . $data['selected_slot'],
            'Europe/Sofia'
        );
        $endsAt = $startsAt->copy()->addMinutes(30);

        // ── Guard: slot must not be in the past ──────────────────────
        if ($startsAt->isPast()) {
            throw ValidationException::withMessages([
                'selected_slot' => 'Избраният час е вече в миналото.',
            ]);
        }

        // ── Guard: slot must be a valid generated slot ───────────────
        $validSlots = $this->availability->slotsForDate($startsAt->copy()->startOfDay());
        $validTimes = array_map(fn (Carbon $s) => $s->format('H:i'), $validSlots);

        if (! in_array($data['selected_slot'], $validTimes, true)) {
            throw ValidationException::withMessages([
                'selected_slot' => 'Избраният час не е наличен за тази дата.',
            ]);
        }

        // ── Snapshot pricing (outside transaction — read-only, safe) ─
        $pricing = ConsultationService::where('type', 'phone')->first();

        // ── Atomic conflict check + create ───────────────────────────
        // Defence-in-depth: three layers against double-booking.
        //
        // Layer 1 — lockForUpdate inside a transaction: serialises concurrent
        //   requests that find existing overlapping rows (common case).
        //
        // Layer 2 — UNIQUE index on starts_at (pcb_unique_starts_at): the
        //   database-level hard stop. Fires when two requests race on an
        //   empty result set (no prior booking for this slot), where
        //   lockForUpdate cannot acquire a gap lock.
        //
        // Layer 3 — QueryException catch: converts the duplicate-key
        //   IntegrityConstraintViolation (SQLSTATE 23000) into the same
        //   user-facing validation error instead of a 500.
        try {
            $booking = DB::transaction(function () use ($data, $startsAt, $endsAt, $pricing) {

                // Check conflict against BOTH phone and viber bookings (shared resource).
                $phoneConflict = PhoneConsultationBooking::whereIn('status', PhoneConsultationBooking::BLOCKING_STATUSES)
                    ->where('starts_at', '<', $endsAt)
                    ->where('ends_at', '>', $startsAt)
                    ->lockForUpdate()
                    ->exists();

                $viberConflict = ViberConsultationBooking::whereIn('status', ViberConsultationBooking::BLOCKING_STATUSES)
                    ->where('starts_at', '<', $endsAt)
                    ->where('ends_at', '>', $startsAt)
                    ->lockForUpdate()
                    ->exists();

                // Check chat bookings for overlap (shared resource).
                $chatConflict = ChatConsultationBooking::whereIn('status', ChatConsultationBooking::BLOCKING_STATUSES)
                    ->where('starts_at', '<', $endsAt)
                    ->where('ends_at', '>', $startsAt)
                    ->lockForUpdate()
                    ->exists();

                if ($phoneConflict || $viberConflict || $chatConflict) {
                    throw ValidationException::withMessages([
                        'selected_slot' => 'Избраният час вече е зает. Моля, изберете друг.',
                    ]);
                }

                return PhoneConsultationBooking::create([
                    'first_name'     => $data['first_name'],
                    'last_name'      => $data['last_name'],
                    'email'          => $data['email'],
                    'phone'          => $data['phone'],
                    'description'    => $data['description'] ?? null,
                    'starts_at'      => $startsAt,
                    'ends_at'        => $endsAt,
                    'payment_method' => $data['payment_method'],
                    'status'         => PhoneConsultationBooking::STATUS_BOOKED,
                    'price_eur'      => $pricing?->price_eur ?? 0,
                    'price_bgn'      => $pricing?->price_bgn,
                    'show_bgn_price' => $pricing?->show_bgn_price ?? false,
                ]);
            });
        } catch (QueryException $e) {
            // SQLSTATE 23000 = Integrity constraint violation (duplicate key).
            // Triggered by the UNIQUE index on starts_at when two concurrent
            // requests both pass the lockForUpdate check on an empty result set.
            if ($e->getCode() === '23000') {
                throw ValidationException::withMessages([
                    'selected_slot' => 'Избраният час вече е зает. Моля, изберете друг.',
                ]);
            }
            throw $e; // re-throw unrelated DB errors
        }

        $this->sendBookingEmails($booking);

        return redirect()->route('phone-consultation.success', [
            'token' => $booking->public_token,
        ]);
    }

    // ── Success page ─────────────────────────────────────────────────

    public function success(string $token)
    {
        if (strlen($token) < 32) {
            abort(404);
        }

        $booking = PhoneConsultationBooking::where('public_token', $token)->first();

        if (! $booking) {
            abort(404);
        }

        return view('pages.phone-consultation-success', compact('booking'));
    }

    // ── Mail ──────────────────────────────────────────────────────────

    private function sendBookingEmails(PhoneConsultationBooking $booking): void
    {
        $contactNumber = SiteSetting::get('consultation_phone_number');
        $contactEmail  = SiteSetting::get('contact_email');
        $successUrl    = route('phone-consultation.success', ['token' => $booking->public_token]);
        $adminUrl      = route('admin.phone-bookings.show', $booking);

        try {
            Mail::to($booking->email)->send(
                new BookingConfirmationMail($booking, 'phone', $contactNumber, $successUrl, $contactEmail)
            );
        } catch (\Throwable $e) {
            Log::error('Phone booking client mail failed', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
        }

        if ($contactEmail) {
            try {
                Mail::to($contactEmail)->send(
                    new BookingNotificationMail($booking, 'phone', $adminUrl)
                );
            } catch (\Throwable $e) {
                Log::error('Phone booking admin mail failed', [
                    'booking_id' => $booking->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }
    }
}
