<?php

use App\Http\Controllers\Admin\AuthController;
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
use App\Http\Controllers\BlogController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PageController;
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
Route::get('/consultation', [PageController::class, 'consultation'])->name('consultation');
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

        Route::resource('posts', PostController::class)
            ->except(['show']);

        Route::patch('/gallery/{galleryItem}/toggle', [GalleryController::class, 'toggle'])
            ->name('gallery.toggle');
        Route::resource('gallery', GalleryController::class)
            ->except(['show']);

        Route::get('inquiries', [AdminInquiryController::class, 'index'])->name('inquiries.index');
        Route::get('inquiries/{inquiry}', [AdminInquiryController::class, 'show'])->name('inquiries.show');
        Route::post('inquiries/{inquiry}/resend', [AdminInquiryController::class, 'resend'])->name('inquiries.resend');
    });
});
