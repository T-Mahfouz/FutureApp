<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Redirect from /home to /dashboard
Route::get('/home', function () {
    return redirect()->route('dashboard');
});

Auth::routes();

// App Routes

Route::group(['middleware' => 'auth:admin'], function () {
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');
    
    Route::get('/users', [UserController::class, 'index'])->name('user.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('user.create');
    Route::post('/users/create', [UserController::class, 'store'])->name('user.store');
    Route::get('/users/{user}', [UserController::class, 'edit'])->name('user.edit');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('user.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

});

// Users


// // Institute to Service Migration Routes
// Route::prefix('migration')->group(function () {
//     Route::post('/institutes', [InstituteMigrationController::class, 'migrateInstitutes']);
//     Route::get('/institutes/status', [InstituteMigrationController::class, 'getMigrationStatus']);
//     Route::post('/institutes/reset', [InstituteMigrationController::class, 'resetMigration']);


//     Route::post('/cities', [CityMigrationController::class, 'migrateAllCities']);
//     Route::get('/users/status', [CityMigrationController::class, 'getMigrationStatus']);
//     Route::post('/cities/{cityId}', [CityMigrationController::class, 'migrateSingleCity']);


//     Route::post('/users', [UserMigrationController::class, 'migrateAllUsers']);
//     Route::get('/users/status', [UserMigrationController::class, 'getUserMigrationStatus']);
//     Route::post('/users/{userId}', [UserMigrationController::class, 'migrateSingleUser']);

//     Route::post('/admins', [AdminMigrationController::class, 'migrateAllAdmins']);
//     Route::get('/admins/status', [AdminMigrationController::class, 'getAdminMigrationStatus']);
//     Route::post('/admins/{adminId}', [AdminMigrationController::class, 'migrateSingleAdmin']);

//     Route::post('/notifications', [NotificationMigrationController::class, 'migrateNotifications']);
//     Route::get('/notifications/status', [NotificationMigrationController::class, 'getNotificationMigrationStatus']);
//     Route::post('/notifications/{notificationId}', [NotificationMigrationController::class, 'migrateSingleNotification']);

//     Route::post('/ads', [AdMigrationController::class, 'migrateAds']);
//     Route::get('/ads/status', [AdMigrationController::class, 'getAdMigrationStatus']);
//     Route::post('/ads/reset', [AdMigrationController::class, 'resetAdMigration']);

//     Route::post('/ads', [AdMigrationController::class, 'migrateAds']);
//     Route::get('/ads/status', [AdMigrationController::class, 'getAdMigrationStatus']);
//     Route::post('/ads/reset', [AdMigrationController::class, 'resetAdMigration']);
// });
