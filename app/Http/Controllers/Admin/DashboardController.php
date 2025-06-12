<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\City;
use App\Models\Service;
use App\Models\User;
use App\Models\Category;
use App\Models\ContactUs;
use App\Models\News;
use App\Models\Rate;
use App\Models\Favorite;
use App\Models\Ad;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Get cities that the current admin can access
     */
    private function getAccessibleCityIds()
    {
        $admin = Auth::guard('admin')->user();
        $adminCities = $admin->cities();
        
        // If admin has no city assignments, they can access all cities (super admin)
        if ($adminCities->count() == 0) {
            return City::pluck('id')->toArray();
        }
        
        // Otherwise, return only assigned cities
        return $adminCities->pluck('cities.id')->toArray();
    }

    /**
     * Check if current admin is super admin
     */
    private function isSuperAdmin()
    {
        $admin = Auth::guard('admin')->user();
        return $admin && $admin->cities()->count() === 0;
    }

    public function show()
    {
        $accessibleCityIds = $this->getAccessibleCityIds();
        $isSuperAdmin = $this->isSuperAdmin();

        // Basic statistics filtered by accessible cities
        if ($isSuperAdmin) {
            // Super admin sees all statistics
            $stats = [
                'total_users' => User::count(),
                'total_services' => Service::count(),
                'total_cities' => City::count(),
                'total_categories' => Category::count(),
                'total_admins' => Admin::count(),
                'total_contact_messages' => ContactUs::count(),
                'total_news' => News::count(),
                'total_ads' => Ad::count(),
                'total_notifications' => Notification::count(),
                'active_services' => Service::where('valid', 1)->count(),
                'inactive_services' => Service::where('valid', 0)->count(),
                'unread_messages' => ContactUs::where('is_read', false)->count(),
                'total_favorites' => Favorite::count(),
                'total_ratings' => Rate::count(),
                'average_rating' => Rate::avg('rate') ?: 0,
                'my_cities' => City::count(), // For super admin, show total cities
            ];
        } else {
            // City admin sees only statistics from their assigned cities
            $stats = [
                'total_users' => User::whereIn('city_id', $accessibleCityIds)->count(),
                'total_services' => Service::whereIn('city_id', $accessibleCityIds)->count(),
                'total_cities' => count($accessibleCityIds), // Same as my_cities for consistency
                'total_categories' => Category::whereIn('city_id', $accessibleCityIds)->count(),
                'total_admins' => 0, // City admins don't manage other admins
                'total_contact_messages' => ContactUs::whereIn('city_id', $accessibleCityIds)->count(),
                'total_news' => News::whereIn('city_id', $accessibleCityIds)->count(),
                'total_ads' => Ad::whereIn('city_id', $accessibleCityIds)->count(),
                'total_notifications' => Notification::whereHas('service', function($q) use ($accessibleCityIds) {
                    $q->whereIn('city_id', $accessibleCityIds);
                })->orWhereHas('news', function($q) use ($accessibleCityIds) {
                    $q->whereIn('city_id', $accessibleCityIds);
                })->count(),
                'active_services' => Service::whereIn('city_id', $accessibleCityIds)->where('valid', 1)->count(),
                'inactive_services' => Service::whereIn('city_id', $accessibleCityIds)->where('valid', 0)->count(),
                'unread_messages' => ContactUs::whereIn('city_id', $accessibleCityIds)->where('is_read', false)->count(),
                'total_favorites' => Favorite::whereHas('service', function($q) use ($accessibleCityIds) {
                    $q->whereIn('city_id', $accessibleCityIds);
                })->count(),
                'total_ratings' => Rate::whereHas('service', function($q) use ($accessibleCityIds) {
                    $q->whereIn('city_id', $accessibleCityIds);
                })->count(),
                'average_rating' => Rate::whereHas('service', function($q) use ($accessibleCityIds) {
                    $q->whereIn('city_id', $accessibleCityIds);
                })->avg('rate') ?: 0,
                'my_cities' => count($accessibleCityIds),
            ];
        }

        // Recent activity filtered by accessible cities
        if ($isSuperAdmin) {
            $recentUsers = User::latest()->take(5)->get();
            $recentServices = Service::with(['city', 'categories'])->latest()->take(5)->get();
            $recentContactMessages = ContactUs::with(['city', 'user'])->latest()->take(5)->get();
        } else {
            $recentUsers = User::whereIn('city_id', $accessibleCityIds)->latest()->take(5)->get();
            $recentServices = Service::with(['city', 'categories'])
                                   ->whereIn('city_id', $accessibleCityIds)
                                   ->latest()
                                   ->take(5)
                                   ->get();
            $recentContactMessages = ContactUs::with(['city', 'user'])
                                             ->whereIn('city_id', $accessibleCityIds)
                                             ->latest()
                                             ->take(5)
                                             ->get();
        }

        // Chart data - Services by City (filtered by accessible cities)
        $servicesByCity = City::whereIn('id', $accessibleCityIds)
            ->withCount('services')
            ->having('services_count', '>', 0)
            ->orderByDesc('services_count')
            ->take(10)
            ->get()
            ->map(function ($city) {
                return [
                    'name' => $city->name ?? 'City #' . $city->id,
                    'count' => $city->services_count
                ];
            });

        // Chart data - Services by Category (filtered by accessible cities)
        $servicesByCategory = Category::whereIn('city_id', $accessibleCityIds)
            ->withCount('services')
            ->having('services_count', '>', 0)
            ->orderByDesc('services_count')
            ->take(10)
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'count' => $category->services_count
                ];
            });

        // Chart data - Monthly registrations (last 12 months) filtered by cities
        $monthlyUsers = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            if ($isSuperAdmin) {
                $count = User::whereYear('created_at', $date->year)
                           ->whereMonth('created_at', $date->month)
                           ->count();
            } else {
                $count = User::whereIn('city_id', $accessibleCityIds)
                           ->whereYear('created_at', $date->year)
                           ->whereMonth('created_at', $date->month)
                           ->count();
            }
            $monthlyUsers->push([
                'month' => $date->format('M Y'),
                'count' => $count
            ]);
        }

        // Chart data - Services created monthly (last 12 months) filtered by cities
        $monthlyServices = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Service::whereIn('city_id', $accessibleCityIds)
                           ->whereYear('created_at', $date->year)
                           ->whereMonth('created_at', $date->month)
                           ->count();
            $monthlyServices->push([
                'month' => $date->format('M Y'),
                'count' => $count
            ]);
        }

        // Rating distribution (filtered by accessible cities)
        $ratingDistribution = Rate::whereHas('service', function($q) use ($accessibleCityIds) {
                $q->whereIn('city_id', $accessibleCityIds);
            })
            ->select('rate', DB::raw('count(*) as count'))
            ->groupBy('rate')
            ->orderBy('rate')
            ->get()
            ->map(function ($item) {
                return [
                    'rating' => $item->rate . ' Star' . ($item->rate > 1 ? 's' : ''),
                    'count' => $item->count
                ];
            });

        // Top rated services (filtered by accessible cities)
        $topRatedServices = Service::with(['rates', 'city'])
            ->whereIn('city_id', $accessibleCityIds)
            ->whereHas('rates')
            ->get()
            ->map(function ($service) {
                return [
                    'name' => $service->name,
                    'city' => $service->city->name ?? 'Unknown',
                    'average_rating' => round($service->averageRating(), 2),
                    'total_ratings' => $service->rates->count()
                ];
            })
            ->sortByDesc('average_rating')
            ->take(10)
            ->values();

        // Most favorited services (filtered by accessible cities)
        $mostFavoritedServices = Service::withCount('favorites')
            ->with('city')
            ->whereIn('city_id', $accessibleCityIds)
            ->having('favorites_count', '>', 0)
            ->orderByDesc('favorites_count')
            ->take(10)
            ->get()
            ->map(function ($service) {
                return [
                    'name' => $service->name,
                    'city' => $service->city->name ?? 'Unknown',
                    'favorites_count' => $service->favorites_count
                ];
            });

        // Recent news (for city admins)
        $recentNews = News::with('city')
                         ->whereIn('city_id', $accessibleCityIds)
                         ->latest()
                         ->take(5)
                         ->get();

        // Recent ads (for city admins)
        $recentAds = Ad::with('city')
                      ->whereIn('city_id', $accessibleCityIds)
                      ->latest()
                      ->take(5)
                      ->get();

        // Get accessible cities info for city admins
        $accessibleCities = City::whereIn('id', $accessibleCityIds)->get();

        return view('dashboard.show', compact(
            'stats',
            'recentUsers',
            'recentServices',
            'recentContactMessages',
            'recentNews',
            'recentAds',
            'servicesByCity',
            'servicesByCategory',
            'monthlyUsers',
            'monthlyServices',
            'ratingDistribution',
            'topRatedServices',
            'mostFavoritedServices',
            'accessibleCities',
            'isSuperAdmin'
        ));
    }

    public function analytics()
    {
        $accessibleCityIds = $this->getAccessibleCityIds();
        $isSuperAdmin = $this->isSuperAdmin();

        // More detailed analytics data filtered by accessible cities
        $data = [
            'dailyStats' => $this->getDailyStats($accessibleCityIds),
            'topCities' => $this->getTopCities($accessibleCityIds),
            'categoryPerformance' => $this->getCategoryPerformance($accessibleCityIds),
            'userGrowth' => $this->getUserGrowthData($accessibleCityIds),
            'serviceStats' => $this->getServiceStats($accessibleCityIds),
            'contactMessageStats' => $this->getContactMessageStats($accessibleCityIds),
            'isSuperAdmin' => $isSuperAdmin,
        ];
        
        return view('dashboard.analytics', $data);
    }

    private function getDailyStats($accessibleCityIds)
    {
        $stats = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $stats->push([
                'date' => $date->format('M d'),
                'users' => User::whereIn('city_id', $accessibleCityIds)
                             ->whereDate('created_at', $date)
                             ->count(),
                'services' => Service::whereIn('city_id', $accessibleCityIds)
                                   ->whereDate('created_at', $date)
                                   ->count(),
                'contact_messages' => ContactUs::whereIn('city_id', $accessibleCityIds)
                                              ->whereDate('created_at', $date)
                                              ->count(),
            ]);
        }
        return $stats;
    }

    private function getTopCities($accessibleCityIds)
    {
        return City::whereIn('id', $accessibleCityIds)
            ->withCount(['services', 'categories', 'users', 'news', 'ads'])
            ->orderByDesc('services_count')
            ->take(10)
            ->get();
    }

    private function getCategoryPerformance($accessibleCityIds)
    {
        return Category::select([
                'categories.id',
                'categories.name',
                DB::raw('COUNT(DISTINCT services.id) as services_count'),
                DB::raw('ROUND(AVG(rates.rate), 2) as average_rating'),
                DB::raw('COUNT(rates.id) as total_ratings')
            ])
            ->whereIn('categories.city_id', $accessibleCityIds)
            ->leftJoin('service_categories', 'categories.id', '=', 'service_categories.category_id')
            ->leftJoin('services', 'service_categories.service_id', '=', 'services.id')
            ->leftJoin('rates', 'services.id', '=', 'rates.service_id')
            ->groupBy('categories.id', 'categories.name')
            ->having('services_count', '>', 0)
            ->orderByDesc('services_count')
            ->take(15)
            ->get()
            ->map(function($category) {
                return [
                    'name' => $category->name,
                    'services_count' => $category->services_count,
                    'average_rating' => $category->average_rating ?? 0,
                    'total_ratings' => $category->total_ratings,
                ];
            });
    }

    private function getUserGrowthData($accessibleCityIds)
    {
        return User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->whereIn('city_id', $accessibleCityIds)
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get();
    }

    private function getServiceStats($accessibleCityIds)
    {
        return [
            'total' => Service::whereIn('city_id', $accessibleCityIds)->count(),
            'active' => Service::whereIn('city_id', $accessibleCityIds)->where('valid', 1)->count(),
            'inactive' => Service::whereIn('city_id', $accessibleCityIds)->where('valid', 0)->count(),
            'with_images' => Service::whereIn('city_id', $accessibleCityIds)->whereNotNull('image_id')->count(),
            'without_images' => Service::whereIn('city_id', $accessibleCityIds)->whereNull('image_id')->count(),
            'recent_week' => Service::whereIn('city_id', $accessibleCityIds)
                                  ->where('created_at', '>=', now()->subWeek())
                                  ->count(),
            'recent_month' => Service::whereIn('city_id', $accessibleCityIds)
                                   ->where('created_at', '>=', now()->subMonth())
                                   ->count(),
        ];
    }

    private function getContactMessageStats($accessibleCityIds)
    {
        return [
            'total' => ContactUs::whereIn('city_id', $accessibleCityIds)->count(),
            'unread' => ContactUs::whereIn('city_id', $accessibleCityIds)->where('is_read', false)->count(),
            'read' => ContactUs::whereIn('city_id', $accessibleCityIds)->where('is_read', true)->count(),
            'recent_week' => ContactUs::whereIn('city_id', $accessibleCityIds)
                                    ->where('created_at', '>=', now()->subWeek())
                                    ->count(),
            'recent_month' => ContactUs::whereIn('city_id', $accessibleCityIds)
                                     ->where('created_at', '>=', now()->subMonth())
                                     ->count(),
        ];
    }

    // Quick stats API endpoint for widgets
    public function quickStats()
    {
        $accessibleCityIds = $this->getAccessibleCityIds();
        $isSuperAdmin = $this->isSuperAdmin();

        if ($isSuperAdmin) {
            $stats = [
                'services' => Service::count(),
                'users' => User::count(),
                'cities' => City::count(),
                'unread_messages' => ContactUs::where('is_read', false)->count(),
            ];
        } else {
            $stats = [
                'services' => Service::whereIn('city_id', $accessibleCityIds)->count(),
                'categories' => Category::whereIn('city_id', $accessibleCityIds)->count(),
                'news' => News::whereIn('city_id', $accessibleCityIds)->count(),
                'unread_messages' => ContactUs::whereIn('city_id', $accessibleCityIds)->where('is_read', false)->count(),
            ];
        }

        return response()->json($stats);
    }

    // City-specific dashboard for city admins
    public function cityDashboard($cityId)
    {
        $accessibleCityIds = $this->getAccessibleCityIds();
        
        // Check if admin has access to this city
        if (!in_array($cityId, $accessibleCityIds)) {
            abort(403, 'You do not have permission to view this city dashboard.');
        }

        $city = City::findOrFail($cityId);
        
        $cityStats = [
            'services' => Service::where('city_id', $cityId)->count(),
            'active_services' => Service::where('city_id', $cityId)->where('valid', 1)->count(),
            'categories' => Category::where('city_id', $cityId)->count(),
            'users' => User::where('city_id', $cityId)->count(),
            'news' => News::where('city_id', $cityId)->count(),
            'ads' => Ad::where('city_id', $cityId)->count(),
            'contact_messages' => ContactUs::where('city_id', $cityId)->count(),
            'unread_messages' => ContactUs::where('city_id', $cityId)->where('is_read', false)->count(),
        ];

        $recentActivity = [
            'services' => Service::where('city_id', $cityId)->latest()->take(5)->get(),
            'users' => User::where('city_id', $cityId)->latest()->take(5)->get(),
            'messages' => ContactUs::with('user')->where('city_id', $cityId)->latest()->take(5)->get(),
        ];

        return view('dashboard.city', compact('city', 'cityStats', 'recentActivity'));
    }
}