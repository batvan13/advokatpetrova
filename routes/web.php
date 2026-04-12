<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ConsultationClosureController;
use App\Http\Controllers\Admin\ConsultationServiceController;
use App\Http\Controllers\Admin\ConsultationWorkingHoursController;
use App\Http\Controllers\Admin\ChatBookingController;
use App\Http\Controllers\Admin\PhoneBookingController;
use App\Http\Controllers\Admin\ViberBookingController;
use App\Http\Controllers\Admin\WrittenConsultationRequestController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ForgotPasswordController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\InquiryController as AdminInquiryController;
use App\Http\Controllers\Admin\PageSectionController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ResetPasswordController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ChatConsultationController;
use App\Http\Controllers\PhoneConsultationController;
use App\Http\Controllers\ViberConsultationController;
use App\Http\Controllers\WrittenConsultationController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/services', [PageController::class, 'services'])->name('services');
Route::get('/services/{slug}', [PageController::class, 'serviceShow'])->name('services.show');
Route::get('/gallery', [PageController::class, 'gallery'])->name('gallery');
Route::get('/blog', [BlogController::class, 'index'])->name('blog');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/contacts', [PageController::class, 'contacts'])->name('contacts');
Route::get('/moyata-praktika', [PageController::class, 'practice'])->name('practice');
Route::get('/obshti-usloviya', [PageController::class, 'terms'])->name('terms');
Route::get('/politika-za-biskvitki', [PageController::class, 'cookiePolicy'])->name('cookie_policy');
Route::get('/politika-za-poveritelnost', [PageController::class, 'privacy'])->name('privacy');
Route::get('/consultation/success', [PageController::class, 'consultationSuccess'])->name('consultation.success');
Route::get('/consultation', [PageController::class, 'consultation'])->name('consultation');
Route::get('/consultation/phone', [PhoneConsultationController::class, 'show'])->name('phone-consultation.show');
Route::get('/consultation/phone/slots', [PhoneConsultationController::class, 'slots'])->name('phone-consultation.slots')->middleware('throttle:60,1');
Route::post('/consultation/phone', [PhoneConsultationController::class, 'submit'])->name('phone-consultation.submit')->middleware('throttle:phone-consultation');
Route::get('/consultation/phone/success/{token}', [PhoneConsultationController::class, 'success'])->name('phone-consultation.success');
Route::get('/consultation/viber', [ViberConsultationController::class, 'show'])->name('viber-consultation.show');
Route::get('/consultation/viber/slots', [ViberConsultationController::class, 'slots'])->name('viber-consultation.slots')->middleware('throttle:60,1');
Route::post('/consultation/viber', [ViberConsultationController::class, 'submit'])->name('viber-consultation.submit')->middleware('throttle:viber-consultation');
Route::get('/consultation/viber/success/{token}', [ViberConsultationController::class, 'success'])->name('viber-consultation.success');
Route::get('/consultation/chat', [ChatConsultationController::class, 'show'])->name('chat-consultation.show');
Route::get('/consultation/chat/slots', [ChatConsultationController::class, 'slots'])->name('chat-consultation.slots')->middleware('throttle:60,1');
Route::post('/consultation/chat', [ChatConsultationController::class, 'submit'])->name('chat-consultation.submit')->middleware('throttle:chat-consultation');
Route::get('/consultation/chat/success/{token}', [ChatConsultationController::class, 'success'])->name('chat-consultation.success');
Route::get('/consultation/written', [WrittenConsultationController::class, 'show'])->name('written-consultation.show');
Route::post('/consultation/written', [WrittenConsultationController::class, 'submit'])->name('written-consultation.submit')->middleware('throttle:written-consultation');
Route::get('/consultation/written/success', [WrittenConsultationController::class, 'success'])->name('written-consultation.success');
Route::post('/contact', [InquiryController::class, 'submit'])->name('inquiry.submit')->middleware('throttle:contact-form');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Allow: /',
        '',
        'Disallow: /admin',
        '',
        'Sitemap: '.url('/sitemap.xml'),
    ];

    return response(implode("\n", $lines), 200)
        ->header('Content-Type', 'text/plain');
})->name('robots');

Route::prefix('admin')->name('admin.')->group(function () {
    // Guest-only routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:admin-login');

        Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('throttle:password-reset');
        Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.store')->middleware('throttle:password-reset');
    });

    // Protected admin routes
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('password', [PasswordController::class, 'edit'])->name('password.edit');
        Route::put('password', [PasswordController::class, 'update'])->name('password.update');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('sections', PageSectionController::class)
            ->only(['index', 'edit', 'update'])
            ->parameters(['sections' => 'pageSection']);

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        Route::patch('/services/{service}/toggle', [ServiceController::class, 'toggle'])
            ->name('services.toggle');
        Route::resource('services', ServiceController::class)
            ->except(['show']);

        Route::patch('/team-members/{team_member}/toggle', [TeamMemberController::class, 'toggle'])
            ->name('team-members.toggle');
        Route::resource('team-members', TeamMemberController::class)
            ->except(['show']);

        Route::resource('posts', PostController::class)
            ->except(['show']);

        Route::patch('/gallery/{galleryItem}/toggle', [GalleryController::class, 'toggle'])
            ->name('gallery.toggle');
        Route::resource('gallery', GalleryController::class)
            ->except(['show']);

        Route::get('inquiries', [AdminInquiryController::class, 'index'])->name('inquiries.index');
        Route::get('inquiries/{inquiry}', [AdminInquiryController::class, 'show'])->name('inquiries.show');
        Route::post('inquiries/{inquiry}/resend', [AdminInquiryController::class, 'resend'])->name('inquiries.resend');

        Route::resource('consultation-services', ConsultationServiceController::class)
            ->only(['index', 'edit', 'update'])
            ->parameters(['consultation-services' => 'consultationService']);

        Route::resource('working-hours', ConsultationWorkingHoursController::class)
            ->only(['index', 'edit', 'update'])
            ->parameters(['working-hours' => 'workingHour']);

        Route::resource('closures', ConsultationClosureController::class)
            ->except(['show']);

        Route::get('phone-bookings', [PhoneBookingController::class, 'index'])->name('phone-bookings.index');
        Route::get('phone-bookings/{phoneBooking}', [PhoneBookingController::class, 'show'])->name('phone-bookings.show');

        Route::get('viber-bookings', [ViberBookingController::class, 'index'])->name('viber-bookings.index');
        Route::get('viber-bookings/{viberBooking}', [ViberBookingController::class, 'show'])->name('viber-bookings.show');

        Route::get('chat-bookings', [ChatBookingController::class, 'index'])->name('chat-bookings.index');
        Route::get('chat-bookings/{chatBooking}', [ChatBookingController::class, 'show'])->name('chat-bookings.show');

        Route::get('written-consultations', [WrittenConsultationRequestController::class, 'index'])->name('written-consultations.index');
        Route::get('written-consultations/{writtenConsultationRequest}', [WrittenConsultationRequestController::class, 'show'])->name('written-consultations.show');
        Route::patch('written-consultations/{writtenConsultationRequest}/mark-answered', [WrittenConsultationRequestController::class, 'markAnswered'])->name('written-consultations.mark-answered');
        Route::get('written-consultations/{writtenConsultationRequest}/attachments/{attachment}', [WrittenConsultationRequestController::class, 'download'])->name('written-consultations.download');
    });
});

// ── TEMPORARY: SMTP transport smoke-test ─────────────────────────────────────
// REMOVE THIS ROUTE before going to production.
// Only active when APP_ENV != production.
if (app()->environment() !== 'production') {
    Route::get('/_test/mail', function () {
        if (! request()->hasValidSignature() && ! app()->isLocal()) {
            abort(403);
        }

        $to      = request()->query('to', config('mail.from.address'));
        $subject = 'PETROVA — SMTP smoke test ' . now()->format('Y-m-d H:i:s');
        $body    = implode("\n", [
            'This is an automated SMTP transport smoke-test.',
            '',
            'Sent at : ' . now('Europe/Sofia')->toDateTimeString(),
            'Mailer  : ' . config('mail.default'),
            'Host    : ' . config('mail.mailers.smtp.host'),
            'Port    : ' . config('mail.mailers.smtp.port'),
            'From    : ' . config('mail.from.address'),
            'To      : ' . $to,
            '',
            'If you see this in Mailtrap, the SMTP transport is working correctly.',
        ]);

        try {
            \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });

            return response()->json([
                'status'  => 'ok',
                'message' => 'Mail dispatched successfully.',
                'to'      => $to,
                'subject' => $subject,
                'mailer'  => config('mail.default'),
                'host'    => config('mail.mailers.smtp.host'),
                'port'    => config('mail.mailers.smtp.port'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
                'class'   => get_class($e),
            ], 500);
        }
    });
}
