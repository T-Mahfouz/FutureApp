<?php

use App\Http\Controllers\AdMigrationController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminMigrationController;
use App\Http\Controllers\CityMigrationController;
use App\Http\Controllers\InstituteMigrationController;
use App\Http\Controllers\NotificationMigrationController;
use App\Http\Controllers\UserMigrationController;

Route::get('/', function () {
    return view('welcome');
});


// Institute to Service Migration Routes
Route::prefix('migration')->group(function () {
    Route::post('/institutes', [InstituteMigrationController::class, 'migrateInstitutes']);
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
});
