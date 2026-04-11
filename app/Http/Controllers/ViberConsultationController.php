<?php

namespace App\Http\Controllers;

use App\Models\ChatConsultationBooking;
use App\Models\ConsultationService;
use App\Models\PhoneConsultationBooking;
use App\Models\ViberConsultationBooking;
use App\Support\ConsultationAvailabilityService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ViberConsultationController extends Controller
{
    public function __construct(
        private readonly ConsultationAvailabilityService $availability
    ) {}

    // ── Page ─────────────────────────────────────────────────────────

    public function show()
    {
        $pricing = ConsultationService::where('type', 'video')->first();

        return view('pages.viber-consultation', compact('pricing'));
    }

    // ── Slots JSON endpoint ───────────────────────────────────────────

    /**
     * GET /consultation/viber/slots?date=YYYY-MM-DD&duration=30|60
     */
    public function slots(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date'     => ['required', 'date_format:Y-m-d'],
            'duration' => ['required', 'integer', 'in:30,60'],
        ]);

        $duration = (int) $validated['duration'];
        $date     = Carbon::createFromFormat('Y-m-d', $validated['date'], 'Europe/Sofia');
        $today    = Carbon::now('Europe/Sofia')->startOfDay();

        if ($date->lt($today)) {
            return response()->json(['slots' => []]);
        }

        if ($date->gt($today->copy()->addMonths(3))) {
            return response()->json(['slots' => []]);
        }

        // Generate all theoretical start times for the requested duration.
        $slots = $this->availability->slotsForDate($date, $duration);

        $dateStr = $date->toDateString();

        // Collect all 30-min grid blocks occupied by phone bookings.
        $phoneBlocked = PhoneConsultationBooking::whereIn('status', PhoneConsultationBooking::BLOCKING_STATUSES)
            ->whereDate('starts_at', $dateStr)
            ->pluck('starts_at')
            ->map(fn ($s) => Carbon::parse($s, 'Europe/Sofia')->format('H:i'));

        // Collect all 30-min grid blocks occupied by viber bookings.
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

        $blockedGrid = $phoneBlocked->merge($viberBlocked)->merge($chatBlocked)->flip();

        // For a 60-min slot the start time is only valid if BOTH the start
        // block AND the next 30-min block are free.
        $available = array_filter(
            array_map(fn (Carbon $s) => $s->format('H:i'), $slots),
            function (string $t) use ($blockedGrid, $duration) {
                if (isset($blockedGrid[$t])) {
                    return false;
                }
                if ($duration === 60) {
                    // Check the second 30-min block as well.
                    $next = Carbon::createFromFormat('H:i', $t)->addMinutes(30)->format('H:i');
                    if (isset($blockedGrid[$next])) {
                        return false;
                    }
                }
                return true;
            }
        );

        return response()->json(['slots' => array_values($available)]);
    }

    // ── Submit booking ────────────────────────────────────────────────

    /**
     * POST /consultation/viber
     */
    public function submit(Request $request)
    {
        $data = $request->validate([
            'duration'       => ['required', 'integer', 'in:30,60'],
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
            'duration.required'       => 'Моля, изберете продължителност.',
            'duration.in'             => 'Невалидна продължителност.',
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

        $duration = (int) $data['duration'];

        // ── Build slot datetimes ─────────────────────────────────────
        $startsAt = Carbon::createFromFormat(
            'Y-m-d H:i',
            $data['selected_date'] . ' ' . $data['selected_slot'],
            'Europe/Sofia'
        );
        $endsAt = $startsAt->copy()->addMinutes($duration);

        // ── Guard: slot must not be in the past ──────────────────────
        if ($startsAt->isPast()) {
            throw ValidationException::withMessages([
                'selected_slot' => 'Избраният час е вече в миналото.',
            ]);
        }

        // ── Guard: slot must be a valid generated slot ───────────────
        $validSlots = $this->availability->slotsForDate($startsAt->copy()->startOfDay(), $duration);
        $validTimes = array_map(fn (Carbon $s) => $s->format('H:i'), $validSlots);

        if (! in_array($data['selected_slot'], $validTimes, true)) {
            throw ValidationException::withMessages([
                'selected_slot' => 'Избраният час не е наличен за тази дата.',
            ]);
        }

        // ── Snapshot pricing (outside transaction — read-only) ───────
        // Viber uses the 'video' consultation service pricing.
        $pricing = ConsultationService::where('type', 'video')->first();

        // Pick the correct price based on duration.
        $priceEur = $duration === 60
            ? ($pricing?->price_eur_60 ?? $pricing?->price_eur ?? 0)
            : ($pricing?->price_eur ?? 0);

        $priceBgn = $duration === 60
            ? ($pricing?->price_bgn_60 ?? $pricing?->price_bgn)
            : ($pricing?->price_bgn);

        // ── Atomic conflict check + create ───────────────────────────
        // Three-layer defence identical to phone booking:
        // Layer 1: lockForUpdate inside transaction (serialises concurrent requests with existing rows).
        // Layer 2: No UNIQUE index on starts_at here because Viber can have 30 or 60 min durations
        //          and a 60-min booking occupies two 30-min blocks — a simple starts_at unique
        //          index would not prevent a phone 30-min booking at the same starts_at.
        //          Cross-table uniqueness is enforced by the overlap query below.
        // Layer 3: QueryException catch for any unexpected DB integrity error.
        //
        // Remaining pragmatic risk: two concurrent Viber submits for the same slot on an empty
        // result set may both pass lockForUpdate (gap lock not guaranteed in InnoDB REPEATABLE READ).
        // This is documented in the output section I) REMAINING RISKS.
        try {
            $booking = DB::transaction(function () use ($data, $duration, $startsAt, $endsAt, $pricing, $priceEur, $priceBgn) {

                // Check phone bookings for overlap.
                $phoneConflict = PhoneConsultationBooking::whereIn('status', PhoneConsultationBooking::BLOCKING_STATUSES)
                    ->where('starts_at', '<', $endsAt)
                    ->where('ends_at', '>', $startsAt)
                    ->lockForUpdate()
                    ->exists();

                // Check viber bookings for overlap.
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

                return ViberConsultationBooking::create([
                    'first_name'       => $data['first_name'],
                    'last_name'        => $data['last_name'],
                    'email'            => $data['email'],
                    'phone'            => $data['phone'],
                    'description'      => $data['description'] ?? null,
                    'duration_minutes' => $duration,
                    'starts_at'        => $startsAt,
                    'ends_at'          => $endsAt,
                    'payment_method'   => $data['payment_method'],
                    'status'           => ViberConsultationBooking::STATUS_BOOKED,
                    'price_eur'        => $priceEur,
                    'price_bgn'        => $priceBgn,
                    'show_bgn_price'   => $pricing?->show_bgn_price ?? false,
                ]);
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                throw ValidationException::withMessages([
                    'selected_slot' => 'Избраният час вече е зает. Моля, изберете друг.',
                ]);
            }
            throw $e;
        }

        return redirect()->route('viber-consultation.success', [
            'token' => $booking->public_token,
        ]);
    }

    // ── Success page ─────────────────────────────────────────────────

    public function success(string $token)
    {
        if (strlen($token) < 32) {
            abort(404);
        }

        $booking = ViberConsultationBooking::where('public_token', $token)->first();

        if (! $booking) {
            abort(404);
        }

        return view('pages.viber-consultation-success', compact('booking'));
    }
}
