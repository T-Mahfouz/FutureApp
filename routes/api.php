<?php

use App\Http\Controllers\API\AdController;
use App\Http\Controllers\API\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

# Auth Routes
Route::group(['prefix' => 'auth'], function () {
    # Login and Register
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('verify', [AuthController::class, 'verify'])->name('verify');
    
    # Uncomment and update other routes as needed
    # Route::post('forget-password-request', [AuthController::class, 'forgetPasswordRequest'])->name('forget.password.request');
    # Route::post('reset-password-login', [AuthController::class, 'resetWithLogin'])->name('reset.with.login');
    
    # Route::middleware('auth:api')->group(function() {
    #     Route::post('send-verification-code', [AuthController::class, 'sendVerificationCode'])->name('send.verification-code');
    #     Route::post('change-password', [AuthController::class, 'changePassword'])->name('change.password');
    #     Route::get('logout', [AuthController::class, 'logout'])->name('logout');
    # });
});

Route::middleware('auth:api')->group(function() {
    Route::prefix('ads')->group(function () {
        Route::get('/city', [AdController::class, 'getCityAds']);
        Route::post('/city', [AdController::class, 'getByCityId']);
    });
});
