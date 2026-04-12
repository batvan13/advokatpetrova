<?php

namespace App\Support;

use Carbon\Carbon;
use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendar;
use Google\Service\Calendar\Event as CalendarEvent;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Facades\Log;

/**
 * Creates Google Calendar events for slot-based consultation bookings.
 *
 * Responsibilities:
 *   - Service Account authentication via JSON credentials file.
 *   - Building the event payload (title, description, extendedProperties, timezone).
 *   - Calling the Google Calendar API and returning the created event ID.
 *
 * This service does NOT:
 *   - Write to the database.
 *   - Update booking models.
 *   - Handle retries.
 *   - Catch exceptions — callers are responsible for try/catch.
 *
 * Supported $type values: 'phone' | 'viber' | 'chat'
 */
class GoogleCalendarService
{
    private const TIMEZONE = 'Europe/Sofia';

    private readonly string $calendarId;
    private readonly string $credentialsPath;

    public function __construct()
    {
        $this->calendarId      = (string) config('services.google_calendar.calendar_id', '');
        $this->credentialsPath = (string) config('services.google_calendar.credentials_path', '');
    }

    /**
     * Create a Google Calendar event for the given booking.
     *
     * @param  object $booking  PhoneConsultationBooking | ViberConsultationBooking | ChatConsultationBooking
     * @param  string $type     'phone' | 'viber' | 'chat'
     * @return string           The created Google Calendar event ID.
     *
     * @throws \Exception  On any Google API or configuration error.
     */
    public function createEvent(object $booking, string $type): string
    {
        $this->guardConfiguration();

        $client = $this->buildClient();
        $service = new GoogleCalendar($client);

        $event = new CalendarEvent([
            'summary'            => $this->buildTitle($booking, $type),
            'description'        => $this->buildDescription($booking, $type),
            'start'              => $this->buildEventDateTime($booking->starts_at),
            'end'                => $this->buildEventDateTime($booking->ends_at),
            'extendedProperties' => [
                'private' => [
                    'app'          => 'petrova',
                    'booking_type' => $type,
                    'booking_id'   => (string) $booking->id,
                    'public_token' => (string) $booking->public_token,
                ],
            ],
        ]);

        $created = $service->events->insert($this->calendarId, $event);

        return $created->getId();
    }

    // ── Private helpers ───────────────────────────────────────────────

    private function guardConfiguration(): void
    {
        if (empty($this->calendarId)) {
            throw new \RuntimeException('GOOGLE_CALENDAR_ID is not configured.');
        }

        if (empty($this->credentialsPath)) {
            throw new \RuntimeException('GOOGLE_SERVICE_ACCOUNT_JSON is not configured.');
        }

        if (! file_exists($this->credentialsPath)) {
            throw new \RuntimeException(
                'Google service account credentials file not found: ' . $this->credentialsPath
            );
        }
    }

    private function buildClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setAuthConfig($this->credentialsPath);
        $client->addScope(GoogleCalendar::CALENDAR_EVENTS);

        return $client;
    }

    private function buildTitle(object $booking, string $type): string
    {
        $name = $booking->first_name . ' ' . $booking->last_name;

        return match ($type) {
            'phone' => 'Телефонна консултация — ' . $name,
            'viber' => 'Viber консултация (' . ($booking->duration_minutes ?? 30) . ' мин.) — ' . $name,
            'chat'  => 'Чат консултация — ' . $name,
            default => 'Консултация — ' . $name,
        };
    }

    private function buildDescription(object $booking, string $type): string
    {
        $typeLabel = match ($type) {
            'phone' => 'Телефонна консултация',
            'viber' => 'Viber видео консултация',
            'chat'  => 'Чат консултация',
            default => 'Консултация',
        };

        $lines = [
            'Тип консултация: ' . $typeLabel,
            'Клиент: '         . $booking->first_name . ' ' . $booking->last_name,
            'Имейл: '          . $booking->email,
        ];

        if (! empty($booking->phone)) {
            $lines[] = 'Телефон: ' . $booking->phone;
        }

        if (! empty($booking->payment_method) && method_exists($booking, 'paymentMethodLabel')) {
            $lines[] = 'Метод на плащане: ' . $booking->paymentMethodLabel();
        }

        if (isset($booking->price_eur)) {
            $lines[] = 'Цена: ' . number_format((float) $booking->price_eur, 2) . ' EUR';
        }

        if (! empty($booking->description)) {
            $lines[] = '';
            $lines[] = 'Бележка от клиента:';
            $lines[] = $booking->description;
        }

        // Admin show URL — built from known route names per type.
        $adminUrl = $this->buildAdminUrl($booking, $type);
        if ($adminUrl) {
            $lines[] = '';
            $lines[] = 'Администрация: ' . $adminUrl;
        }

        return implode("\n", $lines);
    }

    private function buildAdminUrl(object $booking, string $type): string
    {
        try {
            return match ($type) {
                'phone' => route('admin.phone-bookings.show', $booking),
                'viber' => route('admin.viber-bookings.show', $booking),
                'chat'  => route('admin.chat-bookings.show', $booking),
                default => '',
            };
        } catch (\Throwable) {
            return '';
        }
    }

    private function buildEventDateTime(\DateTimeInterface|string $datetime): EventDateTime
    {
        $carbon = Carbon::parse($datetime)->setTimezone(self::TIMEZONE);

        $dt = new EventDateTime();
        $dt->setDateTime($carbon->toRfc3339String());
        $dt->setTimeZone(self::TIMEZONE);

        return $dt;
    }
}
