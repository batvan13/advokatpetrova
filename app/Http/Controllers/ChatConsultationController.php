<?php

namespace App\Http\Controllers;

use App\Exceptions\GoogleCalendarUnavailableException;
use App\Mail\BookingConfirmationMail;
use App\Models\ChatConsultationBooking;
use App\Models\ChatSession;
use App\Models\ConsultationService;
use App\Models\PhoneConsultationBooking;
use App\Models\SiteSetting;
use App\Models\ViberConsultationBooking;
use App\Support\ChatSessionLifecycleService;
use App\Support\ConsultationAvailabilityService;
use App\Support\GoogleCalendarBusyService;
use App\Support\PaymentService;
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
        private readonly GoogleCalendarBusyService     $googleBusy,
        private readonly PaymentService                  $paymentService,
        private readonly ChatSessionLifecycleService   $sessionLifecycle,
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

        $phoneBlocked = PhoneConsultationBooking::whereIn('status', PhoneConsultationBooking::BLOCKING_STATUSES)
            ->whereDate('starts_at', $dateStr)
            ->pluck('starts_at')
            ->map(fn ($s) => Carbon::parse($s, 'Europe/Sofia')->format('H:i'));

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

        try {
            $busyPeriods = $this->googleBusy->busyPeriodsForDate($date, useCache: true);

            $available = $this->availability->filterAvailableSlotTimes(
                $slots,
                array_keys($blockedStarts->all()),
                $busyPeriods,
                30,
            );
        } catch (GoogleCalendarUnavailableException $e) {
            Log::error('Google Calendar FreeBusy check failed', [
                'context'     => 'chat_slots',
                'date'        => $date->toDateString(),
                'starts_at'   => null,
                'ends_at'     => null,
                'calendar_id' => config('services.google_calendar.calendar_id'),
                'error'       => $e->getMessage(),
                'class'       => get_class($e),
            ]);

            return response()->json([
                'slots'                => [],
                'calendar_unavailable' => true,
            ]);
        }

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

        $startsAt = Carbon::createFromFormat(
            'Y-m-d H:i',
            $data['selected_date'] . ' ' . $data['selected_slot'],
            'Europe/Sofia'
        );
        $endsAt = $startsAt->copy()->addMinutes(ChatConsultationBooking::DURATION_MINUTES);

        if ($startsAt->isPast()) {
            throw ValidationException::withMessages([
                'selected_slot' => 'Избраният час е вече в миналото.',
            ]);
        }

        $validSlots = $this->availability->slotsForDate($startsAt->copy()->startOfDay());
        $validTimes = array_map(fn (Carbon $s) => $s->format('H:i'), $validSlots);

        if (! in_array($data['selected_slot'], $validTimes, true)) {
            throw ValidationException::withMessages([
                'selected_slot' => 'Избраният час не е наличен за тази дата.',
            ]);
        }

        try {
            if ($this->googleBusy->intervalIsBusy($startsAt, $endsAt, useCache: false)) {
                throw ValidationException::withMessages([
                    'selected_slot' => 'Избраният час вече е зает. Моля, изберете друг.',
                ]);
            }
        } catch (GoogleCalendarUnavailableException $e) {
            Log::error('Google Calendar FreeBusy check failed', [
                'context'     => 'chat_submit',
                'date'        => $startsAt->toDateString(),
                'starts_at'   => $startsAt->toIso8601String(),
                'ends_at'     => $endsAt->toIso8601String(),
                'calendar_id' => config('services.google_calendar.calendar_id'),
                'error'       => $e->getMessage(),
                'class'       => get_class($e),
            ]);

            throw ValidationException::withMessages([
                'selected_slot' => 'Временно не можем да проверим календара. Моля, опитайте отново след малко.',
            ]);
        }

        $pricing = ConsultationService::where('type', 'chat')->first();

        if (! $pricing || $pricing->price_eur <= 0) {
            abort(503, 'Chat consultation pricing is not configured.');
        }

        // ChatSession is NOT created here — it is created after successful payment.
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

                return ChatConsultationBooking::create([
                    'first_name'     => $data['first_name'],
                    'last_name'      => $data['last_name'],
                    'email'          => $data['email'],
                    'phone'          => $data['phone'],
                    'description'    => $data['description'] ?? null,
                    'starts_at'      => $startsAt,
                    'ends_at'        => $endsAt,
                    'payment_method' => $data['payment_method'],
                    'status'         => ChatConsultationBooking::STATUS_PENDING_PAYMENT,
                    'price_eur'      => $pricing->price_eur,
                    'price_bgn'      => $pricing->price_bgn,
                    'show_bgn_price' => $pricing->show_bgn_price,
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

        $payment = $this->paymentService->createPendingPayment(
            payable:       $booking,
            amount:        (float) $pricing->price_eur,
            currency:      'EUR',
            paymentMethod: $data['payment_method'],
            description:   'Чат консултация — ' . $booking->fullName(),
        );

        $this->sendAcknowledgementEmail($booking);

        return redirect()->route('payment.simulate', ['invoice' => $payment->invoice_number]);
    }

    // ── Success / status page ─────────────────────────────────────────

    public function success(string $token)
    {
        if (strlen($token) < 32) {
            abort(404);
        }

        $booking = ChatConsultationBooking::with(['session', 'payment'])
            ->where('public_token', $token)
            ->first();

        if (! $booking) {
            abort(404);
        }

        return view('pages.chat-consultation-success', compact('booking'));
    }

    // ── Client waiting room (read-only) ───────────────────────────────

    /**
     * GET /consultation/chat/room/{client_access_token}
     */
    public function room(string $clientAccessToken)
    {
        if (strlen($clientAccessToken) < 32) {
            abort(404);
        }

        $session = ChatSession::with(['booking.payment'])
            ->where('client_access_token', $clientAccessToken)
            ->first();

        if (! $session || ! $session->booking) {
            abort(404);
        }

        $booking = $session->booking;

        if (! $this->isRoomEligible($booking)) {
            abort(404);
        }

        $now     = Carbon::now('Europe/Sofia');
        $opensAt = $this->roomOpensAt($booking);

        if ($booking->status === ChatConsultationBooking::STATUS_CONFIRMED
            && $now->gte($booking->ends_at)) {
            $this->sessionLifecycle->completeIfExpired($session, $now);
            $session->refresh();
            $booking->refresh();
        }

        if ($session->isCompleted() || $booking->status === ChatConsultationBooking::STATUS_COMPLETED) {
            return $this->roomView('completed', $booking, $session, $opensAt);
        }

        if ($now->lt($opensAt)) {
            return $this->roomView('early', $booking, $session, $opensAt);
        }

        $state = match ($session->phase) {
            ChatSession::PHASE_WAITING   => 'waiting',
            ChatSession::PHASE_ACTIVE,
            ChatSession::PHASE_ENDING    => 'active',
            ChatSession::PHASE_COMPLETED => 'completed',
            default                      => null,
        };

        if ($state === null) {
            abort(404);
        }

        return $this->roomView($state, $booking, $session, $opensAt);
    }

    private function isRoomEligible(ChatConsultationBooking $booking): bool
    {
        if (! in_array($booking->status, [
            ChatConsultationBooking::STATUS_CONFIRMED,
            ChatConsultationBooking::STATUS_COMPLETED,
        ], true)) {
            return false;
        }

        $payment = $booking->payment;

        return $payment !== null && $payment->isPaid();
    }

    private function roomOpensAt(ChatConsultationBooking $booking): Carbon
    {
        return $booking->starts_at->copy()->subMinutes(10);
    }

    private function roomView(string $state, ChatConsultationBooking $booking, ChatSession $session, Carbon $opensAt)
    {
        return view('pages.chat-consultation-room', [
            'booking' => $booking,
            'session' => $session,
            'state'   => $state,
            'opensAt' => $opensAt,
        ]);
    }

    // ── Initial acknowledgement email ─────────────────────────────────

    private function sendAcknowledgementEmail(ChatConsultationBooking $booking): void
    {
        $contactEmail = SiteSetting::get('contact_email');
        $successUrl   = route('chat-consultation.success', ['token' => $booking->public_token]);

        try {
            Mail::to($booking->email)->send(
                new BookingConfirmationMail($booking, 'chat', null, $successUrl, $contactEmail)
            );
        } catch (\Throwable $e) {
            Log::error('Chat booking acknowledgement mail failed', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
