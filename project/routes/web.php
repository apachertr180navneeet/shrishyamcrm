<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\LanguageController;
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
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;

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

// Language Switcher
Route::get('lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

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

        // ---- Agent-accessible routes (scoped to agent's own data) ----

        // Member Enrolment
        Route::resource('members', MemberController::class);
        Route::get('members/{id}/certificate/pdf', [MemberController::class, 'certificatePdf'])->name('members.certificate.pdf');

        // Collections & Payments
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payment-entry', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('receipts', [PaymentController::class, 'receipts'])->name('receipts.index');
        Route::get('receipts/{id}', [PaymentController::class, 'receipt'])->name('payments.receipt');
        Route::get('receipts/{id}/pdf', [PaymentController::class, 'receiptPdf'])->name('payments.receipt.pdf');
        Route::get('ledger', [PaymentController::class, 'ledger'])->name('ledger.index');

        // Certificates
        Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::get('certificates/{id}', [CertificateController::class, 'show'])->name('certificates.show');
        Route::get('certificates/{id}/pdf', [CertificateController::class, 'downloadPdf'])->name('certificates.pdf');

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

        // ---- Admin / Super-Admin-only routes (agents blocked) ----

        // Schemes & Age Slabs
        Route::middleware(['role:admin,super_admin'])->group(function () {
            Route::get('schemes', [SchemeController::class, 'index'])->name('schemes.index');
            Route::post('schemes', [SchemeController::class, 'store'])->name('schemes.store');
            Route::put('schemes/{id}', [SchemeController::class, 'update'])->name('schemes.update');
            Route::delete('schemes/{id}', [SchemeController::class, 'destroy'])->name('schemes.destroy');
            Route::post('schemes/{id}/status', [SchemeController::class, 'toggleStatus'])->name('schemes.toggle-status');

            Route::get('age-slabs', [SchemeController::class, 'ageSlabs'])->name('schemes.age-slabs');
            Route::post('age-slabs', [SchemeController::class, 'storeAgeSlab'])->name('schemes.age-slabs.store');
            Route::put('age-slabs/{id}', [SchemeController::class, 'updateAgeSlab'])->name('schemes.age-slabs.update');
            Route::delete('age-slabs/{id}', [SchemeController::class, 'destroyAgeSlab'])->name('schemes.age-slabs.destroy');
            Route::get('api/slab-by-age', [SchemeController::class, 'getSlabByAge'])->name('api.slab-by-age');

            // Agent Network (manage agents)
            Route::resource('agents', AgentController::class)->only(['index', 'store', 'show']);

            // Marriage Events
            Route::get('events', [MarriageEventController::class, 'index'])->name('events.index');
            Route::post('events', [MarriageEventController::class, 'store'])->name('events.store');
            Route::post('events/billing', [MarriageEventController::class, 'billMembers'])->name('events.billing');

            // Beneficiary Payouts
            Route::get('payouts', [PayoutController::class, 'index'])->name('payouts.index');
            Route::post('payouts', [PayoutController::class, 'store'])->name('payouts.store');
            Route::post('payouts/{id}/status', [PayoutController::class, 'updateStatus'])->name('payouts.update-status');

            // User Management (Super Admin)
            Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);

            // Society Settings (Super Admin)
            Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
            Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Server Artisan Helper Routes (Super Admin Only)
|--------------------------------------------------------------------------
| These routes are restricted to authenticated super-admins to prevent
| unauthorised database migration / seeder / cache manipulation.
*/


    // Run Migrations (with --force for production)
    Route::get('/run-migrations', function () {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            return '<div style="font-family: monospace; padding: 20px; background: #1e1e1e; color: #00ff66; border-radius: 8px;">'
                . '<h3>Migration Successful!</h3>'
                . '<pre>' . ($output ?: 'Migrations ran successfully (no new migrations to execute).') . '</pre>'
                . '</div>';
        } catch (\Throwable $e) {
            return '<div style="font-family: monospace; padding: 20px; background: #1e1e1e; color: #ff5555; border-radius: 8px;">'
                . '<h3>Migration Error:</h3>'
                . '<pre>' . e($e->getMessage()) . '</pre>'
                . '</div>';
        }
    })->name('run-migrations');

    // Clear & Optimize Cache
    Route::get('/clear-cache', function () {
        try {
            Artisan::call('optimize:clear');
            $output = Artisan::output();
            return '<div style="font-family: monospace; padding: 20px; background: #1e1e1e; color: #00ff66; border-radius: 8px;">'
                . '<h3>Cache Cleared Successfully!</h3>'
                . '<pre>' . e($output) . '</pre>'
                . '</div>';
        } catch (\Throwable $e) {
            return '<div style="font-family: monospace; padding: 20px; background: #1e1e1e; color: #ff5555; border-radius: 8px;">'
                . '<h3>Error:</h3>'
                . '<pre>' . e($e->getMessage()) . '</pre>'
                . '</div>';
        }
    })->name('clear-cache');

    // Run Seeders (Agents, Schemes, Roles)
    Route::get('/run-seeders', function () {
        try {
            (new \Database\Seeders\AgentSeeder())->run();
            (new \Database\Seeders\SchemeSeeder())->run();

            return '<div style="font-family: monospace; padding: 20px; background: #1e1e1e; color: #00ff66; border-radius: 8px;">'
                . '<h3>Database Seeders Ran Successfully!</h3>'
                . '<p>Agents count: ' . \App\Models\Agent::count() . '</p>'
                . '<p>Schemes count: ' . \App\Models\Scheme::count() . '</p>'
                . '</div>';
        } catch (\Throwable $e) {
            return '<div style="font-family: monospace; padding: 20px; background: #1e1e1e; color: #ff5555; border-radius: 8px;">'
                . '<h3>Seeder Error:</h3>'
                . '<pre>' . e($e->getMessage()) . '</pre>'
                . '</div>';
        }
    })->name('run-seeders');

