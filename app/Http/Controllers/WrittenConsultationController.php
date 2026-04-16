<?php

namespace App\Http\Controllers;

use App\Mail\WrittenRequestConfirmationMail;
use App\Models\ConsultationService;
use App\Models\SiteSetting;
use App\Models\WrittenConsultationAttachment;
use App\Models\WrittenConsultationRequest;
use App\Support\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class WrittenConsultationController extends Controller
{
    private const ALLOWED_MIMES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png',
    ];

    private const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

    public function __construct(private readonly PaymentService $paymentService) {}

    public function show()
    {
        $pricing = ConsultationService::where('type', 'written')->first();

        return view('pages.written-consultation', [
            'pricing' => $pricing,
        ]);
    }

    public function submit(Request $request)
    {
        $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'files'       => ['nullable', 'array', 'max:5'],
            'files.*'     => [
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,jpg,jpeg,png',
            ],
        ], [
            'title.required'       => 'Моля, въведете заглавие на казуса.',
            'description.required' => 'Моля, опишете Вашия казус.',
            'description.max'      => 'Описанието не може да надвишава 5000 символа.',
            'files.max'            => 'Може да прикачите максимум 5 файла.',
            'files.*.max'          => 'Всеки файл не може да надвишава 10 MB.',
            'files.*.mimes'        => 'Позволени формати: PDF, DOC, DOCX, JPG, JPEG, PNG.',
        ]);

        $request->validate([
            'first_name'     => ['required', 'string', 'max:255'],
            'last_name'      => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255'],
            'phone'          => ['required', 'string', 'max:50'],
            'payment_method' => ['required', 'in:card,easypay,epay'],
            'consent'        => ['accepted'],
        ], [
            'first_name.required'     => 'Моля, въведете Вашето ime.',
            'last_name.required'      => 'Моля, въведете Вашата фамилия.',
            'email.required'          => 'Моля, въведете имейл адрес.',
            'email.email'             => 'Моля, въведете валиден имейл адрес.',
            'phone.required'          => 'Моля, въведете телефонен номер.',
            'payment_method.required' => 'Моля, изберете метод на плащане.',
            'payment_method.in'       => 'Невалиден метод на плащане.',
            'consent.accepted'        => 'Трябва да се съгласите с Политиката за поверителност.',
        ]);

        $pricing = ConsultationService::where('type', 'written')->first();

        $consultationRequest = WrittenConsultationRequest::create([
            'first_name'     => $request->input('first_name'),
            'last_name'      => $request->input('last_name'),
            'email'          => $request->input('email'),
            'phone'          => $request->input('phone'),
            'title'          => $request->input('title'),
            'description'    => $request->input('description'),
            'payment_method' => $request->input('payment_method'),
            'status'         => WrittenConsultationRequest::STATUS_PENDING_PAYMENT,
            'price_eur'      => $pricing?->price_eur ?? 0,
            'price_bgn'      => $pricing?->price_bgn,
            'show_bgn_price' => $pricing?->show_bgn_price ?? false,
            'submitted_at'   => now(),
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if (! $file->isValid()) {
                    continue;
                }

                $path = $file->store(
                    'written-consultations/' . $consultationRequest->id,
                    'local'
                );

                WrittenConsultationAttachment::create([
                    'written_consultation_request_id' => $consultationRequest->id,
                    'original_name'                   => $file->getClientOriginalName(),
                    'path'                            => $path,
                    'size'                            => $file->getSize(),
                    'mime_type'                       => $file->getMimeType(),
                ]);
            }
        }

        $payment = $this->paymentService->createPendingPayment(
            payable:       $consultationRequest,
            amount:        (float) ($pricing?->price_eur ?? 0),
            currency:      'EUR',
            paymentMethod: $request->input('payment_method'),
            description:   'Писмена консултация — ' . $consultationRequest->fullName(),
        );

        $this->sendAcknowledgementEmail($consultationRequest);

        return redirect()->route('payment.simulate', ['invoice' => $payment->invoice_number]);
    }

    public function success(Request $request)
    {
        $token = $request->query('ref');

        if (! $token || ! is_string($token) || strlen($token) < 32) {
            abort(404);
        }

        $consultationRequest = WrittenConsultationRequest::with(['attachments', 'payment'])
            ->where('public_token', $token)
            ->first();

        if (! $consultationRequest) {
            abort(404);
        }

        return view('pages.written-consultation-success', [
            'consultationRequest' => $consultationRequest,
        ]);
    }

    // ── Initial acknowledgement email ─────────────────────────────────

    private function sendAcknowledgementEmail(WrittenConsultationRequest $consultationRequest): void
    {
        $contactEmail = SiteSetting::get('contact_email');
        $successUrl   = route('written-consultation.success', ['ref' => $consultationRequest->public_token]);

        $consultationRequest->loadMissing('attachments');

        try {
            Mail::to($consultationRequest->email)->send(
                new WrittenRequestConfirmationMail($consultationRequest, $successUrl, $contactEmail)
            );
        } catch (\Throwable $e) {
            Log::error('Written consultation acknowledgement mail failed', [
                'request_id' => $consultationRequest->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
