<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\VivoUsersController;
use App\Http\Controllers\AquariumDashboardController;
use App\Http\Middleware\EnsureUserIsAdmin;

Route::get('/', function () {
    return redirect()->route('index');
});
Route::get('/index',[HomeController::class, 'index'])->name('index');
Route::get('/login/create', [VivoUsersController::class, 'create'])->name('vivo_users.create');
Route::view('/terms', 'terms')->name('terms');

Route::post('/login/store', [VivoUsersController::class, 'store'])
    ->middleware('throttle:registration')
    ->name('vivo_users.store');
    
Route::post('/login', [VivoUsersController::class, 'login'])
    ->middleware('throttle:login')
    ->name('vivo_users.login');

Route::post('/logout-all-devices', [VivoUsersController::class, 'logoutAllDevices'])
    ->middleware('auth')
    ->name('vivo_users.logout_all');

Route::post('/logout', [VivoUsersController::class, 'logout'])
    ->middleware('auth')
    ->name('vivo_users.logout');

Route::delete('/account', [VivoUsersController::class, 'destroy'])
    ->middleware('auth')
    ->name('vivo_users.destroy');

Route::delete('/admin/users/{user}', [VivoUsersController::class, 'destroyByAdmin'])
    ->middleware(['auth', EnsureUserIsAdmin::class])
    ->name('admin.users.destroy');

Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/telemetry/latest', [AquariumDashboardController::class, 'latestTelemetry']);
    Route::post('/actuator/{actuator}', [AquariumDashboardController::class, 'queueActuator']);
    Route::post('/aquarium/pair-device', [AquariumDashboardController::class, 'pairDevice'])
        ->middleware('throttle:5,1');
});

Route::view('/dashboard', 'dashboard')->middleware('auth')->name('dashboard');

Route::get('/admin', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', EnsureUserIsAdmin::class])
    ->name('admin.dashboard');
