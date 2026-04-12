<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmationMail;
use App\Mail\BookingNotificationMail;
use App\Models\ChatConsultationBooking;
use App\Models\ChatSession;
use App\Models\ConsultationService;
use App\Models\PhoneConsultationBooking;
use App\Models\SiteSetting;
use App\Models\ViberConsultationBooking;
use App\Support\ConsultationAvailabilityService;
use App\Support\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ChatConsultationController extends Controller
{
    public function __construct(
        private readonly ConsultationAvailabilityService $availability,
        private readonly GoogleCalendarService           $calendar,
    ) {}

    // ── Page ─────────────────────────────────────────────────────────

    public function show()
    {
        $pricing = ConsultationService::where('type', 'chat')->first();

        return view('pages.chat-consultation', compact('pricing'));
    }

    // ── Slots JSON endpoint ───────────────────────────────────────────

    /**
     * GET /consultation/chat/slots?date=YYYY-MM-DD
     *
     * Chat is always 30 minutes — no duration parameter needed.
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

        $slots   = $this->availability->slotsForDate($date);
        $dateStr = $date->toDateString();

        // Collect blocked 30-min grid positions from all three booking tables.
        $phoneBlocked = PhoneConsultationBooking::whereIn('status', PhoneConsultationBooking::BLOCKING_STATUSES)
            ->whereDate('starts_at', $dateStr)
            ->pluck('starts_at')
            ->map(fn ($s) => Carbon::parse($s, 'Europe/Sofia')->format('H:i'));

        // Viber bookings may span 30 or 60 min — expand to grid blocks.
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
     * POST /consultation/chat
     */
    public function submit(Request $request)
    {
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
            'selected_date.required'    => 'Моля, изберете ден.',
            'selected_date.date_format' => 'Невалидна дата.',
            'selected_slot.required'    => 'Моля, изберете час.',
            'selected_slot.date_format' => 'Невалиден час.',
            'first_name.required'       => 'Моля, въведете Вашето име.',
            'last_name.required'        => 'Моля, въведете Вашата фамилия.',
            'email.required'            => 'Моля, въведете имейл адрес.',
            'email.email'               => 'Моля, въведете валиден имейл адрес.',
            'phone.required'            => 'Моля, въведете телефонен номер.',
            'payment_method.required'   => 'Моля, изберете метод на плащане.',
            'payment_method.in'         => 'Невалиден метод на плащане.',
            'consent.accepted'          => 'Трябва да се съгласите с Политиката за поверителност.',
        ]);

        // ── Build slot datetimes ─────────────────────────────────────
        $startsAt = Carbon::createFromFormat(
            'Y-m-d H:i',
            $data['selected_date'] . ' ' . $data['selected_slot'],
            'Europe/Sofia'
        );
        $endsAt = $startsAt->copy()->addMinutes(ChatConsultationBooking::DURATION_MINUTES);

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

        // ── Snapshot pricing (outside transaction — read-only) ───────
        $pricing = ConsultationService::where('type', 'chat')->first();

        // ── Fail-fast: pricing row must exist and have a positive price ─
        // A missing or zero-price row means the admin has not configured
        // the chat consultation service. Proceeding would create a booking
        // with price = 0, which is a silent business logic error.
        // Abort with a controlled 503-style abort rather than a 500 or a
        // silent €0 booking. No user-facing form error is appropriate here
        // because this is a configuration problem, not a user input problem.
        if (! $pricing || $pricing->price_eur <= 0) {
            abort(503, 'Chat consultation pricing is not configured.');
        }

        // ── Atomic conflict check + create booking + bootstrap session ─
        // Three-layer defence against double-booking:
        //
        // Layer 1 — lockForUpdate inside a transaction: serialises concurrent
        //   requests that find existing overlapping rows (common case).
        //
        // Layer 2 — UNIQUE index on starts_at (ccb_unique_starts_at): the
        //   database-level hard stop. Fires when two requests race on an
        //   empty result set (no prior booking for this slot), where
        //   lockForUpdate cannot acquire a gap lock.
        //
        // Layer 3 — QueryException catch: converts the duplicate-key
        //   IntegrityConstraintViolation (SQLSTATE 23000) into the same
        //   user-facing validation error instead of a 500.
        try {
            $booking = DB::transaction(function () use ($data, $startsAt, $endsAt, $pricing) {

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

                $booking = ChatConsultationBooking::create([
                    'first_name'     => $data['first_name'],
                    'last_name'      => $data['last_name'],
                    'email'          => $data['email'],
                    'phone'          => $data['phone'],
                    'description'    => $data['description'] ?? null,
                    'starts_at'      => $startsAt,
                    'ends_at'        => $endsAt,
                    'payment_method' => $data['payment_method'],
                    'status'         => ChatConsultationBooking::STATUS_BOOKED,
                    'price_eur'      => $pricing->price_eur,
                    'price_bgn'      => $pricing->price_bgn,
                    'show_bgn_price' => $pricing->show_bgn_price,
                ]);

                // Bootstrap session record atomically with the booking.
                // Phase starts as 'waiting'; no session transitions in Phase 3A.
                ChatSession::create([
                    'booking_id' => $booking->id,
                    'phase'      => ChatSession::DEFAULT_PHASE,
                    'started_at' => null,
                    'ended_at'   => null,
                ]);

                return $booking;
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                throw ValidationException::withMessages([
                    'selected_slot' => 'Избраният час вече е зает. Моля, изберете друг.',
                ]);
            }
            throw $e;
        }

        $this->sendBookingEmails($booking);
        $this->syncToGoogleCalendar($booking);

        return redirect()->route('chat-consultation.success', [
            'token' => $booking->public_token,
        ]);
    }

    // ── Success page ─────────────────────────────────────────────────

    public function success(string $token)
    {
        if (strlen($token) < 32) {
            abort(404);
        }

        $booking = ChatConsultationBooking::with('session')
            ->where('public_token', $token)
            ->first();

        if (! $booking) {
            abort(404);
        }

        return view('pages.chat-consultation-success', compact('booking'));
    }

    // ── Google Calendar ───────────────────────────────────────────────

    private function syncToGoogleCalendar(ChatConsultationBooking $booking): void
    {
        if (! empty($booking->google_event_id)) {
            return;
        }

        try {
            $eventId = $this->calendar->createEvent($booking, 'chat');

            $booking->update([
                'google_event_id'    => $eventId,
                'google_sync_status' => ChatConsultationBooking::GOOGLE_SYNC_SYNCED,
            ]);
        } catch (\Throwable $e) {
            Log::error('Google Calendar sync failed — chat booking', [
                'booking_id' => $booking->id,
                'starts_at'  => $booking->starts_at->toIso8601String(),
                'error'      => $e->getMessage(),
                'error_class'=> get_class($e),
            ]);

            try {
                $booking->update(['google_sync_status' => ChatConsultationBooking::GOOGLE_SYNC_FAILED]);
            } catch (\Throwable) {
                // Status update failure must not propagate.
            }
        }
    }

    // ── Mail ──────────────────────────────────────────────────────────

    private function sendBookingEmails(ChatConsultationBooking $booking): void
    {
        $contactEmail = SiteSetting::get('contact_email');
        $successUrl   = route('chat-consultation.success', ['token' => $booking->public_token]);
        $adminUrl     = route('admin.chat-bookings.show', $booking);

        try {
            Mail::to($booking->email)->send(
                new BookingConfirmationMail($booking, 'chat', null, $successUrl, $contactEmail)
            );
        } catch (\Throwable $e) {
            Log::error('Chat booking client mail failed', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
        }

        if ($contactEmail) {
            try {
                Mail::to($contactEmail)->send(
                    new BookingNotificationMail($booking, 'chat', $adminUrl)
                );
            } catch (\Throwable $e) {
                Log::error('Chat booking admin mail failed', [
                    'booking_id' => $booking->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }
    }
}
