<?php

use App\Http\Controllers\API\AdController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ContactUsController;
use App\Http\Controllers\API\FavoriteController;
use App\Http\Controllers\API\NewsController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\SettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

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

// Route::middleware('auth:api')->group(function() {
//     Route::prefix('ads')->group(function () {
//         Route::get('/city', [AdController::class, 'getCityAds']);
//         Route::post('/city', [AdController::class, 'getByCityId']);
//     });
// });


// Configure rate limiting in RouteServiceProvider boot method
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

// Specific rate limiting for different endpoints
RateLimiter::for('favorites', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()->id);
});

RateLimiter::for('search', function (Request $request) {
    return Limit::perMinute(30)->by($request->user()->id);
});

Route::middleware(['auth:api', 'throttle:api'])->group(function() {
    
    Route::get('auth/logout', [AuthController::class, 'logout'])->name('logout');

    // Ads Routes
    Route::prefix('ads')->group(function () {
        Route::get('/city', [AdController::class, 'getCityAds']);
        Route::get('/user-city', [AdController::class, 'getByCityId']);
        Route::get('/filter', [AdController::class, 'getAdsWithFilter']); // Get ads by location type
    });

    // Categories Routes
    Route::prefix('categories')->group(function () {
        Route::get('/active', [CategoryController::class, 'getActiveCategories']); // Get active categories
        Route::get('/active/with-children', [CategoryController::class, 'getActiveCategoriesWithChildren']); // Bonus: With children
    });

    // News Routes
    Route::prefix('news')->group(function () {
        Route::get('/city', [NewsController::class, 'getCityNews']); // Get city news
        Route::get('/city/paginated', [NewsController::class, 'getCityNewsPaginated']); // Bonus: Paginated
        Route::get('/{id}', [NewsController::class, 'getNewsById']); // Bonus: Single news
    });

    // Services Routes
    Route::prefix('services')->group(function () {
        Route::get('/latest', [ServiceController::class, 'getLatestServices']); // Latest services
        Route::get('/city', [ServiceController::class, 'getCityServices']); // Bonus: All city services
        Route::get('/category/{categoryId}', [ServiceController::class, 'getServicesByCategory']); // Bonus: By category
        
        Route::post('/request', [ServiceController::class, 'requestService'])->name('service.request');
        Route::get('/my-requests', [ServiceController::class, 'getMyServiceRequests'])->name('service.request.show');
        
        Route::get('/{id}', [ServiceController::class, 'getServiceById'])->name('service.show'); // Bonus: Single service
    });

    // Favorites Routes
    Route::prefix('favorites')->middleware('throttle:favorites')->group(function () {
        Route::post('/add', [FavoriteController::class, 'addToFavorites']); // Add to favorites
        Route::post('/remove', [FavoriteController::class, 'removeFromFavorites']); // Remove from favorites
        Route::post('/toggle', [FavoriteController::class, 'toggleFavorite']); // Bonus: Toggle favorite
        Route::get('/my-favorites', [FavoriteController::class, 'getUserFavorites']); // Bonus: Get user favorites
        Route::get('/check/{serviceId}', [FavoriteController::class, 'checkFavoriteStatus']); // Bonus: Check status
    });

    // Notifications Routes
    Route::prefix('notifications')->group(function () {
        Route::get('/city', [NotificationController::class, 'getCityNotifications']);
        Route::get('/latest', [NotificationController::class, 'getLatestNotifications']);
        Route::get('/{id}', [NotificationController::class, 'getNotificationById']);
    });

    // Profile routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'getProfile']);
        Route::post('/update', [ProfileController::class, 'updateProfile']);
        Route::delete('/delete', [ProfileController::class, 'deleteAccount']);
    });


    // Settings routes
    Route::prefix('settings')->group(function () {
        Route::get('/city', [SettingController::class, 'getCitySetting']); // ?key=contact_us or ?key=about_us
        Route::get('/keys', [SettingController::class, 'getAvailableKeys']);
        Route::post('/multiple', [SettingController::class, 'getMultipleSettings']);
    });

    // Contact Us routes
    Route::prefix('contact-us')->group(function () {
        Route::post('/send', [ContactUsController::class, 'sendMessage']);
        Route::post('/send-anonymous', [ContactUsController::class, 'sendAnonymousMessage']);
        Route::get('/my-messages', [ContactUsController::class, 'getMyMessages']);
        Route::get('/{id}', [ContactUsController::class, 'getMessageById']);
        Route::put('/{id}', [ContactUsController::class, 'updateMessage']);
        Route::delete('/{id}', [ContactUsController::class, 'deleteMessage']);
        Route::delete('/{id}', [ContactUsController::class, 'deleteMessage']);
    });
});