<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UbuntuServer;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\VivoUsersController;
use App\Http\Controllers\AquariumDashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login/create', [VivoUsersController::class, 'create'])->name('vivo_users.create');
Route::view('/terms', 'terms')->name('terms');
//vivo routes

Route::post('/login/store', [VivoUsersController::class, 'store'])
    ->middleware('throttle:registration')
    ->name('vivo_users.store');
    
Route::post('/login', [VivoUsersController::class, 'login'])
    ->middleware('throttle:login')
    ->name('vivo_users.login');

Route::post('/logout-all-devices', [VivoUsersController::class, 'logoutAllDevices'])
    ->middleware('auth')
    ->name('vivo_users.logout_all');

Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/telemetry/latest', [AquariumDashboardController::class, 'latestTelemetry']);
    Route::post('/actuator/{actuator}', [AquariumDashboardController::class, 'queueActuator']);
    Route::post('/aquarium/pair-device', [AquariumDashboardController::class, 'pairDevice'])
        ->middleware('throttle:5,1');
});

Route::view('/dashboard', 'dashboard')->middleware('auth')->name('dashboard');

Route::get('/vivo/home', [VivoUsersController::class, 'home'])->name('vivo_users.home');
