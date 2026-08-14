<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SchemeController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\MarriageEventController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\Admin\WhatsAppController;
use App\Http\Controllers\Admin\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
})->name('/');

Route::get('/home', function () {
    return redirect()->route('admin.dashboard');
})->name('home');

Route::name('admin.')->prefix('admin')->group(function () {
    Route::get('/', [AdminAuthController::class, 'index']);

    Route::get('login', [AdminAuthController::class, 'login'])->name('login');
    Route::post('login', [AdminAuthController::class, 'postLogin'])->name('login.post');

    Route::get('forget-password', [AdminAuthController::class, 'showForgetPasswordForm'])->name('forget.password.get');
    Route::post('forget-password', [AdminAuthController::class, 'submitForgetPasswordForm'])->name('forget.password.post');

    Route::get('reset-password/{token}', [AdminAuthController::class, 'showResetPasswordForm'])->name('reset.password.get');
    Route::post('reset-password', [AdminAuthController::class, 'submitResetPasswordForm'])->name('reset.password.post');

    Route::middleware(['admin'])->group(function () {
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Schemes & Age Slabs
        Route::get('schemes', [SchemeController::class, 'index'])->name('schemes.index');
        Route::get('age-slabs', [SchemeController::class, 'ageSlabs'])->name('schemes.age-slabs');
        Route::post('age-slabs', [SchemeController::class, 'storeAgeSlab'])->name('schemes.age-slabs.store');
        Route::get('api/slab-by-age', [SchemeController::class, 'getSlabByAge'])->name('api.slab-by-age');

        // Member Enrolment
        Route::resource('members', MemberController::class);

        // Agent Network
        Route::resource('agents', AgentController::class)->only(['index', 'store', 'show']);

        // Collections & Payments
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payment-entry', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('receipts', [PaymentController::class, 'receipts'])->name('receipts.index');
        Route::get('receipts/{id}', [PaymentController::class, 'receipt'])->name('payments.receipt');
        Route::get('ledger', [PaymentController::class, 'ledger'])->name('ledger.index');

        // Certificates
        Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::get('certificates/{id}', [CertificateController::class, 'show'])->name('certificates.show');

        // Marriage Events
        Route::get('events', [MarriageEventController::class, 'index'])->name('events.index');
        Route::post('events', [MarriageEventController::class, 'store'])->name('events.store');
        Route::post('events/billing', [MarriageEventController::class, 'billMembers'])->name('events.billing');

        // Beneficiary Payouts
        Route::get('payouts', [PayoutController::class, 'index'])->name('payouts.index');
        Route::post('payouts', [PayoutController::class, 'store'])->name('payouts.store');

        // WhatsApp Center
        Route::get('whatsapp', [WhatsAppController::class, 'index'])->name('whatsapp.index');
        Route::post('whatsapp/send', [WhatsAppController::class, 'send'])->name('whatsapp.send');

        // Reports Center
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');

        // Profile & Account Settings
        Route::get('change-password', [AdminAuthController::class, 'changePassword'])->name('change.password');
        Route::post('update-password', [AdminAuthController::class, 'updatePassword'])->name('update.password');
        Route::get('logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('profile', [AdminAuthController::class, 'adminProfile'])->name('profile');
        Route::post('profile', [AdminAuthController::class, 'updateAdminProfile'])->name('update.profile');
    });
});
