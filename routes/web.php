<?php

use App\Http\Controllers\Admin\AdController;
use App\Http\Controllers\Admin\AdminsController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\ContactUsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Artisan;

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
    Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');

    // Users
    Route::get('/users', [UserController::class, 'index'])->name('user.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('user.create');
    Route::post('/users/create', [UserController::class, 'store'])->name('user.store');
    Route::get('/users/{user}', [UserController::class, 'edit'])->name('user.edit');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('user.destroy');

    // Admins
    Route::get('/admins', [AdminsController::class, 'index'])->name('admin.index');
    Route::get('/admins/create', [AdminsController::class, 'create'])->name('admin.create');
    Route::post('/admins/create', [AdminsController::class, 'store'])->name('admin.store');
    Route::get('/admins/{admin}', [AdminsController::class, 'edit'])->name('admin.edit');
    Route::patch('/admins/{admin}', [AdminsController::class, 'update'])->name('admin.update');
    Route::delete('/admins/{admin}', [AdminsController::class, 'destroy'])->name('admin.destroy');

    // Cities
    Route::get('/cities', [CityController::class, 'index'])->name('city.index');
    Route::get('/cities/create', [CityController::class, 'create'])->name('city.create');
    Route::post('/cities/create', [CityController::class, 'store'])->name('city.store');
    Route::get('/cities/{city}', [CityController::class, 'show'])->name('city.show');
    Route::get('/cities/{city}/edit', [CityController::class, 'edit'])->name('city.edit');
    Route::patch('/cities/{city}', [CityController::class, 'update'])->name('city.update');
    Route::delete('/cities/{city}', [CityController::class, 'destroy'])->name('city.destroy');

    // Services
    Route::get('/services', [ServiceController::class, 'index'])->name('service.index');
    Route::get('/services/create', [ServiceController::class, 'create'])->name('service.create');
    Route::post('/services/create', [ServiceController::class, 'store'])->name('service.store');
    Route::get('/services/{service}', [ServiceController::class, 'show'])->name('service.show');
    Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('service.edit');
    Route::patch('/services/{service}', [ServiceController::class, 'update'])->name('service.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('service.destroy');
    Route::post('/services/{service}/toggle-status', [ServiceController::class, 'toggleStatus'])->name('service.toggle-status');

    Route::post('/services/bulk-destroy', [ServiceController::class, 'bulkDestroy'])->name('service.bulk-destroy');
    Route::post('/services/bulk-toggle-status', [ServiceController::class, 'bulkToggleStatus'])->name('service.bulk-toggle-status');
    
    // News
    Route::get('/news', [NewsController::class, 'index'])->name('news.index');
    Route::get('/news/create', [NewsController::class, 'create'])->name('news.create');
    Route::post('/news/create', [NewsController::class, 'store'])->name('news.store');
    Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show');
    Route::get('/news/{news}/edit', [NewsController::class, 'edit'])->name('news.edit');
    Route::patch('/news/{news}', [NewsController::class, 'update'])->name('news.update');
    Route::delete('/news/{news}', [NewsController::class, 'destroy'])->name('news.destroy');
    Route::delete('/news/images/{id}', [NewsController::class, 'destroyImage'])->name('news.image.destroy');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('category.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('category.create');
    Route::post('/categories/create', [CategoryController::class, 'store'])->name('category.store');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('category.show');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('category.edit');
    Route::patch('/categories/{category}', [CategoryController::class, 'update'])->name('category.update');
    Route::post('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('category.toggle-status');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('category.destroy');
    Route::delete('/categories/bulk-delete/{category}', [CategoryController::class, 'bulkDestroy'])->name('category.bulk-destroy');

    // Ads
    /* 
    Route::get('/ads', [AdController::class, 'index'])->name('ad.index');
    Route::post('/ads/bulk-action', [AdController::class, 'bulkAction'])->name('ad.bulk-action');
    Route::get('/ads/create', [AdController::class, 'create'])->name('ad.create');
    Route::post('/ads/create', [AdController::class, 'store'])->name('ad.store');
    Route::get('/ads/{ad}', [AdController::class, 'show'])->name('ad.show');
    Route::get('/ads/{ad}/edit', [AdController::class, 'edit'])->name('ad.edit');
    Route::patch('/ads/{ad}', [AdController::class, 'update'])->name('ad.update');
    Route::delete('/ads/{id}', [AdController::class, 'destroy'])->name('ad.destroy'); 
    */
    
    Route::get('/ads', [AdController::class, 'index'])->name('ad.index');
    Route::post('/ads/bulk-action', [AdController::class, 'bulkAction'])->name('ad.bulk-action');
    Route::get('/ads/create', [AdController::class, 'create'])->name('ad.create');
    Route::post('/ads', [AdController::class, 'store'])->name('ad.store');
    Route::get('/ads/{ad}', [AdController::class, 'show'])->name('ad.show');
    Route::get('/ads/{ad}/edit', [AdController::class, 'edit'])->name('ad.edit');
    Route::patch('/ads/{ad}', [AdController::class, 'update'])->name('ad.update');
    Route::delete('/ads/{ad}', [AdController::class, 'destroy'])->name('ad.destroy');
    
    // AJAX routes for dynamic dropdowns
    Route::get('/ads/ajax/categories-by-city', [AdController::class, 'getCategoriesByCity'])->name('ad.categories-by-city');
    Route::get('/ads/ajax/services-by-city-category', [AdController::class, 'getServicesByCityAndCategory'])->name('ad.services-by-city-category');


    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notification.index');
    Route::get('/notifications/create', [NotificationController::class, 'create'])->name('notification.create');
    Route::post('/notifications/create', [NotificationController::class, 'store'])->name('notification.store');
    Route::get('/notifications/send-firebase', [NotificationController::class, 'sendFirebase'])->name('notification.send-firebase');
    Route::post('/notifications/send-firebase', [NotificationController::class, 'processFirebase'])->name('notification.send-firebase-post');
    Route::post('/notifications/bulk-action', [NotificationController::class, 'bulkAction'])->name('notification.bulk-action');
    Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notification.show');
    Route::get('/notifications/{notification}/edit', [NotificationController::class, 'edit'])->name('notification.edit');
    Route::patch('/notifications/{notification}', [NotificationController::class, 'update'])->name('notification.update');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notification.destroy');


    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('setting.index');
    Route::get('/settings/create', [SettingController::class, 'create'])->name('setting.create');
    Route::post('/settings/create', [SettingController::class, 'store'])->name('setting.store');
    Route::get('/settings/{setting}', [SettingController::class, 'show'])->name('setting.show');
    Route::get('/settings/{setting}/edit', [SettingController::class, 'edit'])->name('setting.edit');
    Route::patch('/settings/{setting}', [SettingController::class, 'update'])->name('setting.update');
    Route::delete('/settings/{setting}', [SettingController::class, 'destroy'])->name('setting.destroy');

    // Contact Messages
    Route::get('/contacts', [ContactUsController::class, 'index'])->name('contact.index');
    Route::post('/contacts/bulk-action', [ContactUsController::class, 'bulkAction'])->name('contact.bulk-action');
    Route::get('/contacts/{contact}', [ContactUsController::class, 'show'])->name('contact.show');
    Route::post('/contacts/{contact}/toggle-read', [ContactUsController::class, 'toggleRead'])->name('contact.toggle-read');
    Route::delete('/contacts/{contact}', [ContactUsController::class, 'destroy'])->name('contact.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

});


Route::get('cache/clear-all', function (Request $request) {
    try {
        // Clear application cache
        Cache::flush();
        
        // Clear config cache
        Artisan::call('config:clear');
        
        // Clear route cache
        Artisan::call('route:clear');
        
        // Clear view cache
        Artisan::call('view:clear');
        
        // Clear compiled classes
        Artisan::call('clear-compiled');
        
        // If using Redis, clear Redis cache as well
        if (config('cache.default') === 'redis') {
            Redis::flushall();
        }
        
        return jsonResponse(200, 'All cache cleared successfully.');
        
    } catch (\Exception $e) {
        return jsonResponse(500, 'Failed to clear cache: ' . $e->getMessage());
    }
});