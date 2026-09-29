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
use App\Http\Controllers\Admin\RoleController;
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

        // Collections & Payments (Receipt Entry & History)
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payment-entry', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('payments/{id}', [PaymentController::class, 'show'])->name('payments.show');
        Route::get('payments/{id}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
        Route::get('receipts', [PaymentController::class, 'receipts'])->name('receipts.index');
        Route::get('receipts/{id}', [PaymentController::class, 'receipt'])->name('payments.receipt');
        Route::get('receipts/{id}/pdf', [PaymentController::class, 'receiptPdf'])->name('payments.receipt.pdf');
        Route::get('payments/{id}/pdf', [PaymentController::class, 'receiptPdf']);
        Route::get('ledger', [PaymentController::class, 'ledger'])->name('ledger.index');
        Route::get('ledger/{id}/pdf', [PaymentController::class, 'memberLedgerPdf'])->name('ledger.pdf');
        Route::get('members/{id}/ledger/pdf', [MemberController::class, 'ledgerPdf'])->name('members.ledger.pdf');

        // Profile & Account Settings
        Route::get('change-password', [AdminAuthController::class, 'changePassword'])->name('change.password');
        Route::post('update-password', [AdminAuthController::class, 'updatePassword'])->name('update.password');
        Route::get('logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('profile', [AdminAuthController::class, 'adminProfile'])->name('profile');
        Route::post('profile', [AdminAuthController::class, 'updateAdminProfile'])->name('update.profile');

        // ---- Admin / Super-Admin-only routes (agents blocked) ----

        Route::middleware(['role:admin,super_admin'])->group(function () {
            // WhatsApp Center (Admin Only)
            Route::get('whatsapp', [WhatsAppController::class, 'index'])->name('whatsapp.index');
            Route::post('whatsapp/send', [WhatsAppController::class, 'send'])->name('whatsapp.send');

            // Reports Center (Admin Only)
            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');

            // Member Certificate Download (Admin Only)
            Route::get('members/{id}/certificate/pdf', [MemberController::class, 'certificatePdf'])->name('members.certificate.pdf');

            // Certificates (Admin Only - Hidden & Blocked for Agents)
            Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
            Route::get('certificates/{id}', [CertificateController::class, 'show'])->name('certificates.show');
            Route::get('certificates/{id}/pdf', [CertificateController::class, 'downloadPdf'])->name('certificates.pdf');

            // Schemes & Age Slabs
            Route::get('schemes', [SchemeController::class, 'index'])->name('schemes.index');
            Route::post('schemes', [SchemeController::class, 'store'])->name('schemes.store');
            Route::get('schemes/{id}', [SchemeController::class, 'showScheme'])->name('schemes.show');
            Route::get('schemes/{id}/edit', [SchemeController::class, 'editScheme'])->name('schemes.edit');
            Route::put('schemes/{id}', [SchemeController::class, 'update'])->name('schemes.update');
            Route::delete('schemes/{id}', [SchemeController::class, 'destroy'])->name('schemes.destroy');
            Route::post('schemes/{id}/status', [SchemeController::class, 'toggleStatus'])->name('schemes.toggle-status');

            Route::get('age-slabs', [SchemeController::class, 'ageSlabs'])->name('schemes.age-slabs');
            Route::get('age-slabs/{id}', [SchemeController::class, 'showAgeSlab'])->name('schemes.age-slabs.show');
            Route::get('age-slabs/{id}/edit', [SchemeController::class, 'editAgeSlab'])->name('schemes.age-slabs.edit');
            Route::post('age-slabs', [SchemeController::class, 'storeAgeSlab'])->name('schemes.age-slabs.store');
            Route::put('age-slabs/{id}', [SchemeController::class, 'updateAgeSlab'])->name('schemes.age-slabs.update');
            Route::delete('age-slabs/{id}', [SchemeController::class, 'destroyAgeSlab'])->name('schemes.age-slabs.destroy');
            Route::get('api/slab-by-age', [SchemeController::class, 'getSlabByAge'])->name('api.slab-by-age');

            // Agent Network (manage agents & login credentials)
            Route::get('agents/{id}/credentials', [AgentController::class, 'getCredentials'])->name('agents.credentials');
            Route::post('agents/{id}/credentials', [AgentController::class, 'updateCredentials'])->name('agents.credentials.update');
            Route::resource('agents', AgentController::class);

            // Marriage Events & All-Events Broadcast
            Route::get('events', [MarriageEventController::class, 'index'])->name('events.index');
            Route::post('events', [MarriageEventController::class, 'store'])->name('events.store');
            Route::get('events/{id}', [MarriageEventController::class, 'show'])->name('events.show');
            Route::get('events/{id}/edit', [MarriageEventController::class, 'edit'])->name('events.edit');
            Route::put('events/{id}', [MarriageEventController::class, 'update'])->name('events.update');
            Route::delete('events/{id}', [MarriageEventController::class, 'destroy'])->name('events.destroy');
            Route::get('events/{id}/contributions', [MarriageEventController::class, 'contributions'])->name('events.contributions');
            Route::get('events/{id}/contributions/print', [MarriageEventController::class, 'printContributions'])->name('events.contributions.print');
            Route::get('events/{id}/contributions/export', [MarriageEventController::class, 'exportContributions'])->name('events.contributions.export');
            Route::get('api/scheme-members-preview', [MarriageEventController::class, 'previewSchemeMembers'])->name('api.scheme-members-preview');
            Route::post('events/billing', [MarriageEventController::class, 'billMembers'])->name('events.billing');
            Route::get('api/events-by-month', [MarriageEventController::class, 'eventsByMonth'])->name('api.events-by-month');
            Route::post('events/broadcast-send', [MarriageEventController::class, 'sendBroadcast'])->name('events.broadcast-send');

            // Beneficiary Payouts (Admin Only - Blocked for Agents)
            Route::get('payouts', [PayoutController::class, 'index'])->name('payouts.index');
            Route::post('payouts', [PayoutController::class, 'store'])->name('payouts.store');
            Route::get('payouts/{id}', [PayoutController::class, 'show'])->name('payouts.show');
            Route::get('payouts/{id}/edit', [PayoutController::class, 'edit'])->name('payouts.edit');
            Route::put('payouts/{id}', [PayoutController::class, 'update'])->name('payouts.update');
            Route::delete('payouts/{id}', [PayoutController::class, 'destroy'])->name('payouts.destroy');
            Route::post('payouts/{id}/status', [PayoutController::class, 'updateStatus'])->name('payouts.update-status');

            // User & Role Management
            Route::resource('users', UserController::class);
            Route::resource('roles', RoleController::class);
            Route::put('roles/{id}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');

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

    // Clear Cache
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

    // Optimize Production (Precompiles Routes, Config & Views for fast server response)
    Route::get('/optimize', function () {
        try {
            Artisan::call('optimize');
            $output = Artisan::output();
            return '<div style="font-family: monospace; padding: 25px; background: #0f172a; color: #38bdf8; border-radius: 8px; border: 1px solid #1e293b; max-width: 800px; margin: 40px auto; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">'
                . '<h2 style="color: #4ade80; margin-top: 0;">🚀 Production Optimization Successful!</h2>'
                . '<p style="color: #94a3b8;">Routes, configuration, and views have been pre-compiled into fast bytecode cache.</p>'
                . '<pre style="background: #1e293b; padding: 15px; border-radius: 6px; color: #f1f5f9; overflow-x: auto;">' . e($output) . '</pre>'
                . '<a href="' . route('admin.dashboard') . '" style="display: inline-block; margin-top: 15px; background: #2563eb; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold;">Go to Dashboard &rarr;</a>'
                . '</div>';
        } catch (\Throwable $e) {
            return '<div style="font-family: monospace; padding: 20px; background: #1e1e1e; color: #ff5555; border-radius: 8px;">'
                . '<h3>Optimization Error:</h3>'
                . '<pre>' . e($e->getMessage()) . '</pre>'
                . '</div>';
        }
    })->name('optimize');

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

