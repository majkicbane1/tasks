<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SharedClientController;
use App\Http\Controllers\WorkEntryController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::get('/share/{token}', [SharedClientController::class, 'dashboard'])->name('shared.dashboard');
Route::get('/share/{token}/projects', [SharedClientController::class, 'projects'])->name('shared.projects');
Route::get('/share/{token}/projects/{project}', [SharedClientController::class, 'project'])
    ->whereNumber('project')
    ->name('shared.projects.show');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/my-projects', [DashboardController::class, 'clientProjects'])->name('client.projects');
    Route::patch('/payments/{payment}/paid', [PaymentController::class, 'markPaid'])
        ->whereNumber('payment')
        ->name('payments.mark-paid');

    Route::middleware('super_admin')->group(function () {
        Route::get('/migration', function () {
            Artisan::call('migrate', ['--force' => true]);

            return response(nl2br(e(Artisan::output())));
        })->name('maintenance.migrate');

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
        Route::resource('work-entries', WorkEntryController::class)->except('show');
        Route::resource('payments', PaymentController::class)->except('show');
    });

    Route::get('/projects/{project}', [ProjectController::class, 'show'])
        ->whereNumber('project')
        ->middleware('client_access')
        ->name('projects.show');
});
