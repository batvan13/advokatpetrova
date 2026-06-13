<?php

use App\Http\Controllers\Admin\AdminUserController;
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
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ChatConsultationController;
use App\Http\Controllers\PaymentController;
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
Route::get('/consultation/chat/room/{client_access_token}', [ChatConsultationController::class, 'room'])->name('chat-consultation.room');
Route::get('/consultation/chat/room/{client_access_token}/status', [ChatConsultationController::class, 'status'])->name('chat-consultation.status')->middleware('throttle:120,1');
Route::get('/consultation/written', [WrittenConsultationController::class, 'show'])->name('written-consultation.show');
Route::post('/consultation/written', [WrittenConsultationController::class, 'submit'])->name('written-consultation.submit')->middleware('throttle:written-consultation');
Route::get('/consultation/written/success', [WrittenConsultationController::class, 'success'])->name('written-consultation.success');
Route::post('/contact', [InquiryController::class, 'submit'])->name('inquiry.submit')->middleware('throttle:contact-form');

Route::get('/otzyvi/dobavi', [ReviewController::class, 'create'])->name('reviews.create');
Route::post('/otzyvi', [ReviewController::class, 'submit'])->name('reviews.submit')->middleware('throttle:reviews');
Route::get('/otzyvi/blagodarim', [ReviewController::class, 'success'])->name('reviews.success');

// ── Fake payment layer ────────────────────────────────────────────────────────
// GET  /payment/{invoice}/simulate — shows the fake payment terminal
// POST /payment/notify/fake-epay   — ONLY endpoint that finalizes payment state
Route::get('/payment/{invoice}/simulate', [PaymentController::class, 'simulate'])->name('payment.simulate');
Route::post('/payment/notify/fake-epay', [PaymentController::class, 'notify'])->name('payment.notify');
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

        Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::get('reviews/{review}', [AdminReviewController::class, 'show'])->name('reviews.show');
        Route::patch('reviews/{review}/publish', [AdminReviewController::class, 'publish'])->name('reviews.publish');
        Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

        Route::resource('consultation-services', ConsultationServiceController::class)
            ->only(['index', 'edit', 'update'])
            ->parameters(['consultation-services' => 'consultationService']);

        Route::resource('working-hours', ConsultationWorkingHoursController::class)
            ->only(['index', 'edit', 'update'])
            ->parameters(['working-hours' => 'workingHour']);

        Route::resource('closures', ConsultationClosureController::class)
            ->except(['show']);

        Route::get('phone-bookings', [PhoneBookingController::class, 'index'])->name('phone-bookings.index');
        Route::get('phone-bookings/archived', [PhoneBookingController::class, 'archiveIndex'])->name('phone-bookings.archived');
        Route::get('phone-bookings/{phoneBooking}', [PhoneBookingController::class, 'show'])->name('phone-bookings.show');
        Route::patch('phone-bookings/{phoneBooking}/complete', [PhoneBookingController::class, 'complete'])->name('phone-bookings.complete');
        Route::patch('phone-bookings/{phoneBooking}/archive', [PhoneBookingController::class, 'archive'])->name('phone-bookings.archive');
        Route::delete('phone-bookings/{phoneBooking}', [PhoneBookingController::class, 'destroy'])->name('phone-bookings.destroy');

        Route::get('viber-bookings', [ViberBookingController::class, 'index'])->name('viber-bookings.index');
        Route::get('viber-bookings/archived', [ViberBookingController::class, 'archiveIndex'])->name('viber-bookings.archived');
        Route::get('viber-bookings/{viberBooking}', [ViberBookingController::class, 'show'])->name('viber-bookings.show');
        Route::patch('viber-bookings/{viberBooking}/complete', [ViberBookingController::class, 'complete'])->name('viber-bookings.complete');
        Route::patch('viber-bookings/{viberBooking}/archive', [ViberBookingController::class, 'archive'])->name('viber-bookings.archive');
        Route::delete('viber-bookings/{viberBooking}', [ViberBookingController::class, 'destroy'])->name('viber-bookings.destroy');

        Route::get('chat-bookings', [ChatBookingController::class, 'index'])->name('chat-bookings.index');
        Route::get('chat-bookings/archived', [ChatBookingController::class, 'archiveIndex'])->name('chat-bookings.archived');
        Route::get('chat-bookings/{chatBooking}', [ChatBookingController::class, 'show'])->name('chat-bookings.show');
        Route::patch('chat-bookings/{chatBooking}/start', [ChatBookingController::class, 'start'])->name('chat-bookings.start');
        Route::patch('chat-bookings/{chatBooking}/complete', [ChatBookingController::class, 'complete'])->name('chat-bookings.complete');
        Route::patch('chat-bookings/{chatBooking}/archive', [ChatBookingController::class, 'archive'])->name('chat-bookings.archive');
        Route::delete('chat-bookings/{chatBooking}', [ChatBookingController::class, 'destroy'])->name('chat-bookings.destroy');

        Route::get('admins', [AdminUserController::class, 'index'])->name('admins.index');
        Route::get('admins/create', [AdminUserController::class, 'create'])->name('admins.create');
        Route::post('admins', [AdminUserController::class, 'store'])->name('admins.store');
        Route::get('admins/{admin}/edit', [AdminUserController::class, 'edit'])->name('admins.edit');
        Route::put('admins/{admin}', [AdminUserController::class, 'update'])->name('admins.update');
        Route::delete('admins/{admin}', [AdminUserController::class, 'destroy'])->name('admins.destroy');

        Route::get('written-consultations', [WrittenConsultationRequestController::class, 'index'])->name('written-consultations.index');
        Route::get('written-consultations/archived', [WrittenConsultationRequestController::class, 'archiveIndex'])->name('written-consultations.archived');
        Route::get('written-consultations/{writtenConsultationRequest}', [WrittenConsultationRequestController::class, 'show'])->name('written-consultations.show');
        Route::patch('written-consultations/{writtenConsultationRequest}/mark-answered', [WrittenConsultationRequestController::class, 'markAnswered'])->name('written-consultations.mark-answered');
        Route::patch('written-consultations/{writtenConsultationRequest}/archive', [WrittenConsultationRequestController::class, 'archive'])->name('written-consultations.archive');
        Route::delete('written-consultations/{writtenConsultationRequest}', [WrittenConsultationRequestController::class, 'destroy'])->name('written-consultations.destroy');
        Route::get('written-consultations/{writtenConsultationRequest}/attachments/{attachment}', [WrittenConsultationRequestController::class, 'download'])->name('written-consultations.download');
    });
});
