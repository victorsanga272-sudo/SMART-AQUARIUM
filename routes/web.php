<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VivoUsersController;
use App\Http\Controllers\AquariumDashboardController;
use App\Mail\DebugTestEmail;
use Illuminate\Support\Facades\Mail;

Route::get('/', function () {
    return redirect()->route('vivo_users.create');
});

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

Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/telemetry/latest', [AquariumDashboardController::class, 'latestTelemetry']);
    Route::post('/actuator/{actuator}', [AquariumDashboardController::class, 'queueActuator']);
    Route::post('/aquarium/pair-device', [AquariumDashboardController::class, 'pairDevice'])
        ->middleware('throttle:5,1');
});

Route::view('/dashboard', 'dashboard')->middleware('auth')->name('dashboard');

if (app()->environment(['local', 'testing'])) {
    Route::get('/debug/mail', function () {
        $user = request()->user();

        Mail::to($user->email)->send(new DebugTestEmail($user));

        return response()->json(['message' => 'Test email sent.']);
    })->middleware('auth')->name('debug.mail');
}
