<?php

use App\Http\Controllers\AdMigrationController;
use App\Http\Controllers\AdminMigrationController;
use App\Http\Controllers\API\AdController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\MediaCleanupController;
use App\Http\Controllers\API\ResizeImageController;
use App\Http\Controllers\CategoryMigrationController;
use App\Http\Controllers\CityMigrationController;
use App\Http\Controllers\InstituteMigrationController;
use App\Http\Controllers\NotificationMigrationController;
use App\Http\Controllers\ServiceImagesMigrationController;
use App\Http\Controllers\SettingsMigrationController;
use App\Http\Controllers\UserMigrationController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// // Auth Routes
// Route::group(['prefix' => 'auth'], function () {
//     // Login and Register
//     Route::post('login', [AuthController::class, 'login'])->name('login');
//     Route::post('register', [AuthController::class, 'register'])->name('register');
//     Route::post('verify', [AuthController::class, 'verify'])->name('verify');
    
//     // Uncomment and update other routes as needed
//     // Route::post('forget-password-request', [AuthController::class, 'forgetPasswordRequest'])->name('forget.password.request');
//     // Route::post('reset-password-login', [AuthController::class, 'resetWithLogin'])->name('reset.with.login');
    
//     Route::middleware('auth:api')->group(function() {
//         Route::post('send-verification-code', [AuthController::class, 'sendVerificationCode'])->name('send.verification-code');
//         Route::post('change-password', [AuthController::class, 'changePassword'])->name('change.password');
//         Route::get('logout', [AuthController::class, 'logout'])->name('logout');
//     });
// });

// Route::middleware('auth:api')->group(function() {
//     Route::prefix('ads')->group(function () {
//         Route::get('/city', [AdController::class, 'getCityAds']);
//         Route::post('/city', [AdController::class, 'getByCityId']);
//     });
// });

// Example of how to restore other routes with correct syntax:
/*
Route::group(['prefix' => 'guest'], function () {
    Route::get('categories', [App\Http\Controllers\API\CategoryController::class, 'index'])->name('categories.index');
    Route::get('ads', [App\Http\Controllers\API\AdsController::class, 'index'])->name('ads.index');
    
    Route::get('merchants', [App\Http\Controllers\API\MerchantController::class, 'index'])->name('merchants.index');
    Route::get('merchants/{id}', [App\Http\Controllers\API\MerchantController::class, 'view'])->name('merchants.view');
    
    Route::get('products', [App\Http\Controllers\API\ProductController::class, 'index'])->name('products.index');
    Route::get('products/{id}', [App\Http\Controllers\API\ProductController::class, 'view'])->name('products.view');
});

Route::middleware(['auth:api', 'active:api'])->group(function () {
    Route::group(['prefix' => 'profile'], function () {
        Route::get('/', [App\Http\Controllers\API\ProfileController::class, '__invoke'])->name('profile.info');
        Route::put('update', [App\Http\Controllers\API\ProfileController::class, 'update'])->name('profile.update');
        Route::post('change-phone-request', [App\Http\Controllers\API\ProfileController::class, 'changePhoneRequest'])->name('profile.change.phone');
        Route::put('update-phone', [App\Http\Controllers\API\ProfileController::class, 'updatePhone'])->name('profile.phone.update');
    });
    
    
});
*/



Route::prefix('media-cleanup')->group(function () {
    Route::get('/paths', [MediaCleanupController::class, 'getAllPaths']);
    Route::get('/storage-info', [MediaCleanupController::class, 'getStorageInfo']);
    Route::get('/analyze', [MediaCleanupController::class, 'analyzeMedia']);
    Route::post('/move-unreferenced', [MediaCleanupController::class, 'moveUnreferencedImages']);
    Route::post('/restore', [MediaCleanupController::class, 'restoreImages']);
    Route::delete('/delete-unreferenced', [MediaCleanupController::class, 'deleteUnreferencedImages']);
    Route::get('/debug-storage', [MediaCleanupController::class, 'debugStoragePaths']);


    Route::get('/analyze-images', [ResizeImageController::class, 'analyzeImages']);
    Route::get('/resize-all', [ResizeImageController::class, 'resizeAllImages']);
    Route::post('/resize-single', [ResizeImageController::class, 'resizeSingleImage']);
    Route::post('/restore-backup', [ResizeImageController::class, 'restoreFromBackup']);
    Route::delete('/delete-backup', [ResizeImageController::class, 'deleteBackup']);
});