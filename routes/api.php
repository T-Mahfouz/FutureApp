<?php

use App\Http\Controllers\AdMigrationController;
use App\Http\Controllers\AdminMigrationController;
use App\Http\Controllers\API\AdController;
use App\Http\Controllers\API\AuthController;
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

// Auth Routes
Route::group(['prefix' => 'auth'], function () {
    // Login and Register
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('verify', [AuthController::class, 'verify'])->name('verify');
    
    // Uncomment and update other routes as needed
    // Route::post('forget-password-request', [AuthController::class, 'forgetPasswordRequest'])->name('forget.password.request');
    // Route::post('reset-password-login', [AuthController::class, 'resetWithLogin'])->name('reset.with.login');
    
    Route::middleware('auth:api')->group(function() {
        Route::post('send-verification-code', [AuthController::class, 'sendVerificationCode'])->name('send.verification-code');
        Route::post('change-password', [AuthController::class, 'changePassword'])->name('change.password');
        Route::get('logout', [AuthController::class, 'logout'])->name('logout');
    });
});

Route::middleware('auth:api')->group(function() {
    Route::prefix('ads')->group(function () {
        Route::get('/city', [AdController::class, 'getCityAds']);
        Route::post('/city', [AdController::class, 'getByCityId']);
    });
});

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







Route::prefix('migration')->group(function () {
    Route::post('/institutes/parents', [InstituteMigrationController::class, 'parents']);
    Route::post('/institutes/children', [InstituteMigrationController::class, 'children']);
    Route::post('/institutes/remains', [InstituteMigrationController::class, 'remains']);
    Route::get('/institutes/status', [InstituteMigrationController::class, 'getMigrationStatus']);
    Route::post('/institutes/reset', [InstituteMigrationController::class, 'resetMigration']);
    
    

    Route::post('/cities', [CityMigrationController::class, 'migrateAllCities']);
    Route::get('/users/status', [CityMigrationController::class, 'getMigrationStatus']);
    Route::post('/cities/{cityId}', [CityMigrationController::class, 'migrateSingleCity']);


    Route::post('/users', [UserMigrationController::class, 'migrateAllUsers']);
    Route::get('/users/status', [UserMigrationController::class, 'getUserMigrationStatus']);
    Route::post('/users/{userId}', [UserMigrationController::class, 'migrateSingleUser']);

    Route::post('/admins', [AdminMigrationController::class, 'migrateAllAdmins']);
    Route::get('/admins/status', [AdminMigrationController::class, 'getAdminMigrationStatus']);
    Route::post('/admins/{adminId}', [AdminMigrationController::class, 'migrateSingleAdmin']);

    Route::post('/notifications', [NotificationMigrationController::class, 'migrateNotifications']);
    Route::get('/notifications/status', [NotificationMigrationController::class, 'getNotificationMigrationStatus']);
    Route::post('/notifications/{notificationId}', [NotificationMigrationController::class, 'migrateSingleNotification']);

    Route::post('/ads', [AdMigrationController::class, 'migrateAds']);
    Route::get('/ads/status', [AdMigrationController::class, 'getAdMigrationStatus']);
    Route::post('/ads/reset', [AdMigrationController::class, 'resetAdMigration']);

    Route::post('/ads', [AdMigrationController::class, 'migrateAds']);
    Route::get('/ads/status', [AdMigrationController::class, 'getAdMigrationStatus']);
    Route::post('/ads/reset', [AdMigrationController::class, 'resetAdMigration']);






    Route::post('/images', [ServiceImagesMigrationController::class, 'migrateServiceImages']);
    Route::post('/categories/prents', [CategoryMigrationController::class, 'migrateParents']);
    Route::post('/categories/children', [CategoryMigrationController::class, 'migrateChildren']);
    Route::post('/settings/aboutus', [SettingsMigrationController::class, 'migrateAboutUs']);
});