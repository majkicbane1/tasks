<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Platform\PlatformSettingsController;
use App\Http\Controllers\Platform\TenantController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SharedClientController;
use App\Http\Controllers\TwoFactorChallengeController;
use App\Http\Controllers\UpgradeController;
use App\Http\Controllers\WorkEntryController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:20,1')->name('login.store');

    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:10,1')->name('register.store');

    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'form'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:10,1')->name('password.update');

    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');

    Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'show'])->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'verify'])->middleware('throttle:20,1')->name('two-factor.verify');
});

Route::get('/brand/{tenant}/{slot}', [BrandController::class, 'asset'])->whereNumber('tenant')->name('brand.asset');

Route::get('/share/{token}', [SharedClientController::class, 'dashboard'])->name('shared.dashboard');
Route::get('/share/{token}/projects', [SharedClientController::class, 'projects'])->name('shared.projects');
Route::get('/share/{token}/more', [SharedClientController::class, 'more'])->name('shared.more');
Route::get('/share/{token}/projects/{project}', [SharedClientController::class, 'project'])
    ->whereNumber('project')
    ->name('shared.projects.show');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// signed in, but possibly not verified yet
Route::middleware('auth')->group(function () {
    // one-time upgrade for the pre-multi-tenant admin; 404 once the tenants table exists
    Route::get('/migration', [UpgradeController::class, 'show'])->name('upgrade.show');
    Route::post('/migration', [UpgradeController::class, 'run'])->name('upgrade.run');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:3,1')->name('verification.send');
});

Route::middleware(['auth', 'account_usable'])->group(function () {
    Route::get('/password/change', [AccountController::class, 'forcePasswordForm'])->name('password.force');
    Route::patch('/password/change', [AccountController::class, 'forcePasswordUpdate'])->name('password.force.update');

    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::patch('/account', [AccountController::class, 'update'])->name('account.update');
    Route::patch('/account/password', [AccountController::class, 'password'])->name('account.password');
    Route::post('/account/two-factor', [AccountController::class, 'startTwoFactor'])->name('account.two-factor.start');
    Route::post('/account/two-factor/confirm', [AccountController::class, 'confirmTwoFactor'])->middleware('throttle:10,1')->name('account.two-factor.confirm');
    Route::post('/account/two-factor/recovery-codes', [AccountController::class, 'recoveryCodes'])->middleware('throttle:10,1')->name('account.two-factor.recovery');
    Route::delete('/account/two-factor', [AccountController::class, 'disableTwoFactor'])->middleware('throttle:10,1')->name('account.two-factor.disable');

    // platform owner: firms, mail, Google keys, sign-up rules, maintenance
    Route::middleware('platform_admin')->prefix('platform')->name('platform.')->group(function () {
        Route::get('/', fn () => redirect()->route('platform.tenants.index'))->name('home');
        Route::resource('tenants', TenantController::class)->except('edit');
        Route::post('/tenants/{tenant}/admins', [TenantController::class, 'storeAdmin'])->name('tenants.admins.store');
        Route::patch('/tenants/{tenant}/admins/{user}', [TenantController::class, 'updateAdmin'])->name('tenants.admins.update');
        Route::delete('/tenants/{tenant}/admins/{user}', [TenantController::class, 'destroyAdmin'])->name('tenants.admins.destroy');

        Route::get('/settings', [PlatformSettingsController::class, 'index'])->name('settings');
        Route::post('/settings/mail', [PlatformSettingsController::class, 'updateMail'])->name('settings.mail');
        Route::post('/settings/mail/test', [PlatformSettingsController::class, 'testMail'])->middleware('throttle:5,1')->name('settings.mail.test');
        Route::post('/settings/google', [PlatformSettingsController::class, 'updateGoogle'])->name('settings.google');
        Route::post('/settings/auth', [PlatformSettingsController::class, 'updateAuth'])->name('settings.auth');

        Route::get('/maintenance', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/maintenance/migrate', [SettingsController::class, 'migrate'])->name('settings.migrate');
        Route::post('/maintenance/cache', [SettingsController::class, 'clearCache'])->name('settings.cache');
        // old GET endpoint ran migrations just by opening the URL; now it only redirects
        Route::get('/migration', fn () => redirect()->route('platform.settings.index'))->name('maintenance.migrate');
    });

    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/my-projects', [DashboardController::class, 'clientProjects'])->name('client.projects');
    Route::get('/my-more', [DashboardController::class, 'clientMore'])->name('client.more');
    Route::patch('/payments/{payment}/paid', [PaymentController::class, 'markPaid'])
        ->whereNumber('payment')
        ->name('payments.mark-paid');

    Route::middleware('super_admin')->group(function () {
        Route::get('/firm', [BrandController::class, 'edit'])->name('firm.edit');
        Route::patch('/firm', [BrandController::class, 'update'])->name('firm.update');

        Route::get('/exports', [ExportController::class, 'index'])->name('exports.index');
        Route::get('/exports/{type}', [ExportController::class, 'download'])->name('exports.download');

        Route::get('/leave', [LeaveController::class, 'index'])->name('leave.index');
        Route::post('/leave', [LeaveController::class, 'store'])->name('leave.store');
        Route::delete('/leave', [LeaveController::class, 'bulkDestroy'])->name('leave.bulk-destroy');
        Route::delete('/leave/{leaveDay}',[LeaveController::class, 'destroy'])->whereNumber('leaveDay')->name('leave.destroy');

        Route::resource('clients', ClientController::class);
        Route::post('/clients/{client}/users', [ClientController::class, 'storeUser'])
            ->whereNumber('client')
            ->name('clients.users.store');
        Route::patch('/clients/{client}/users/{user}', [ClientController::class, 'updateUser'])
            ->whereNumber(['client', 'user'])
            ->name('clients.users.update');
        Route::delete('/clients/{client}/users/{user}', [ClientController::class, 'destroyUser'])
            ->whereNumber(['client', 'user'])
            ->name('clients.users.destroy');
        Route::patch('/clients/{client}/users/{user}/password', [ClientController::class, 'resetUserPassword'])
            ->whereNumber(['client', 'user'])
            ->name('clients.users.password');
        Route::post('/clients/{client}/work-entries/invoice', [ClientController::class, 'invoiceWorkEntries'])
            ->whereNumber('client')
            ->name('clients.work-entries.invoice');
        Route::post('/clients/{client}/share', [ClientController::class, 'share'])
            ->whereNumber('client')
            ->name('clients.share');
        Route::resource('projects', ProjectController::class)->except('show');
        Route::patch('/work-entries/reorder', [WorkEntryController::class, 'reorder'])
            ->name('work-entries.reorder');
        Route::patch('/work-entries/bulk', [WorkEntryController::class, 'bulkUpdate'])
            ->name('work-entries.bulk');
        Route::delete('/work-entries/bulk', [WorkEntryController::class, 'bulkDestroy'])
            ->name('work-entries.bulk-destroy');
        Route::get('/clients/{client}/more', [ClientController::class, 'more'])
            ->whereNumber('client')
            ->name('clients.more');
        Route::resource('work-entries', WorkEntryController::class)->except('show');
        Route::resource('payments', PaymentController::class)->except('show');
    });

    Route::get('/projects/{project}', [ProjectController::class, 'show'])
        ->whereNumber('project')
        ->middleware('client_access')
        ->name('projects.show');
});
