<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\WorkEntryController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/my-projects', [DashboardController::class, 'clientProjects'])->name('client.projects');
    Route::patch('/payments/{payment}/paid', [PaymentController::class, 'markPaid'])
        ->whereNumber('payment')
        ->name('payments.mark-paid');

    Route::middleware('super_admin')->group(function () {
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
        Route::resource('projects', ProjectController::class)->except('show');
        Route::resource('work-entries', WorkEntryController::class)->except('show');
        Route::resource('payments', PaymentController::class)->except('show');
    });

    Route::get('/projects/{project}', [ProjectController::class, 'show'])
        ->whereNumber('project')
        ->middleware('client_access')
        ->name('projects.show');
});
