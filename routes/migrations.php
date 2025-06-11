<?php


use App\Http\Controllers\Migrations\AdMigrationController;
use App\Http\Controllers\Migrations\AdminMigrationController;
use App\Http\Controllers\Migrations\CategoryMigrationController;
use App\Http\Controllers\Migrations\CityMigrationController;
use App\Http\Controllers\Migrations\InstituteMigrationController;
use App\Http\Controllers\Migrations\NotificationMigrationController;
use App\Http\Controllers\Migrations\ServiceImagesMigrationController;
use App\Http\Controllers\Migrations\SettingsMigrationController;
use App\Http\Controllers\Migrations\UserMigrationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Migration Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// Route::post('/cities', [CityMigrationController::class, 'migrateAllCities']);
// Route::get('/users/status', [CityMigrationController::class, 'getMigrationStatus']);
// Route::post('/cities/{cityId}', [CityMigrationController::class, 'migrateSingleCity']);


// Route::post('/categories/parents', [CategoryMigrationController::class, 'migrateParents']);
// Route::post('/categories/children', [CategoryMigrationController::class, 'migrateChildren']);

// Route::post('/users', [UserMigrationController::class, 'migrateAllUsers']);
// Route::get('/users/status', [UserMigrationController::class, 'getUserMigrationStatus']);
// Route::post('/users/{userId}', [UserMigrationController::class, 'migrateSingleUser']);

// // Institute to Service Migration Routes
// Route::post('/institutes/parents', [InstituteMigrationController::class, 'parents']);
// Route::post('/institutes/children', [InstituteMigrationController::class, 'children']);
// Route::get('/institutes/status', [InstituteMigrationController::class, 'getMigrationStatus']);
// Route::post('/institutes/reset', [InstituteMigrationController::class, 'resetMigration']);


// Route::post('/institute-images', [ServiceImagesMigrationController::class, 'migrateServiceImages']);

// Route::post('/settings/aboutus', [SettingsMigrationController::class, 'migrateAboutUs']);


// Route::post('/admins', [AdminMigrationController::class, 'migrateAllAdmins']);
// Route::get('/admins/status', [AdminMigrationController::class, 'getAdminMigrationStatus']);
// Route::post('/admins/{adminId}', [AdminMigrationController::class, 'migrateSingleAdmin']);

// Route::post('/notifications', [NotificationMigrationController::class, 'migrateNotifications']);
// Route::get('/notifications/status', [NotificationMigrationController::class, 'getNotificationMigrationStatus']);
// Route::post('/notifications/{notificationId}', [NotificationMigrationController::class, 'migrateSingleNotification']);

// Route::post('/ads', [AdMigrationController::class, 'migrateAds']);
// Route::get('/ads/status', [AdMigrationController::class, 'getAdMigrationStatus']);
// Route::post('/ads/reset', [AdMigrationController::class, 'resetAdMigration']);

// Route::post('/ads', [AdMigrationController::class, 'migrateAds']);
// Route::get('/ads/status', [AdMigrationController::class, 'getAdMigrationStatus']);
// Route::post('/ads/reset', [AdMigrationController::class, 'resetAdMigration']);




############################# OLD ROUTES #########################
// Route::post('/institutes/parents', [InstituteMigrationController::class, 'parents']);
// Route::post('/institutes/children', [InstituteMigrationController::class, 'children']);
// Route::post('/institutes/remains', [InstituteMigrationController::class, 'remains']);
// Route::get('/institutes/status', [InstituteMigrationController::class, 'getMigrationStatus']);
// Route::post('/institutes/reset', [InstituteMigrationController::class, 'resetMigration']);



// Route::post('/cities', [CityMigrationController::class, 'migrateAllCities']);
// Route::get('/users/status', [CityMigrationController::class, 'getMigrationStatus']);
// Route::post('/cities/{cityId}', [CityMigrationController::class, 'migrateSingleCity']);


// Route::post('/users', [UserMigrationController::class, 'migrateAllUsers']);
// Route::get('/users/status', [UserMigrationController::class, 'getUserMigrationStatus']);
// Route::post('/users/{userId}', [UserMigrationController::class, 'migrateSingleUser']);

// Route::post('/admins', [AdminMigrationController::class, 'migrateAllAdmins']);
// Route::get('/admins/status', [AdminMigrationController::class, 'getAdminMigrationStatus']);
// Route::post('/admins/{adminId}', [AdminMigrationController::class, 'migrateSingleAdmin']);

// Route::post('/notifications', [NotificationMigrationController::class, 'migrateNotifications']);
// Route::get('/notifications/status', [NotificationMigrationController::class, 'getNotificationMigrationStatus']);
// Route::post('/notifications/{notificationId}', [NotificationMigrationController::class, 'migrateSingleNotification']);

// Route::post('/ads', [AdMigrationController::class, 'migrateAds']);
// Route::get('/ads/status', [AdMigrationController::class, 'getAdMigrationStatus']);
// Route::post('/ads/reset', [AdMigrationController::class, 'resetAdMigration']);

// Route::post('/ads', [AdMigrationController::class, 'migrateAds']);
// Route::get('/ads/status', [AdMigrationController::class, 'getAdMigrationStatus']);
// Route::post('/ads/reset', [AdMigrationController::class, 'resetAdMigration']);


// Route::post('/images', [ServiceImagesMigrationController::class, 'migrateServiceImages']);
// Route::post('/categories/prents', [CategoryMigrationController::class, 'migrateParents']);
// Route::post('/categories/children', [CategoryMigrationController::class, 'migrateChildren']);
// Route::post('/settings/aboutus', [SettingsMigrationController::class, 'migrateAboutUs']);