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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function show()
    {
        // Basic statistics
        $stats = [
            'total_users' => User::count(),
            'total_services' => Service::count(),
            'total_cities' => City::count(),
            'total_categories' => Category::count(),
            'total_admins' => Admin::count(),
            'total_contact_messages' => ContactUs::count(),
            'total_news' => News::count(),
            'active_services' => Service::where('valid', 1)->count(),
            'inactive_services' => Service::where('valid', 0)->count(),
            'total_favorites' => Favorite::count(),
            'total_ratings' => Rate::count(),
            'average_rating' => Rate::avg('rate') ?: 0,
        ];

        // Recent activity
        $recentUsers = User::latest()->take(5)->get();
        $recentServices = Service::with(['city', 'categories'])->latest()->take(5)->get();
        $recentContactMessages = ContactUs::with(['city', 'user'])->latest()->take(5)->get();

        // Chart data - Services by City
        $servicesByCity = City::withCount('services')
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

        // Chart data - Services by Category
        $servicesByCategory = Category::withCount('services')
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

        // Chart data - Monthly registrations (last 12 months)
        $monthlyUsers = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = User::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->count();
            $monthlyUsers->push([
                'month' => $date->format('M Y'),
                'count' => $count
            ]);
        }

        // Chart data - Services created monthly (last 12 months)
        $monthlyServices = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Service::whereYear('created_at', $date->year)
                           ->whereMonth('created_at', $date->month)
                           ->count();
            $monthlyServices->push([
                'month' => $date->format('M Y'),
                'count' => $count
            ]);
        }

        // Rating distribution
        $ratingDistribution = Rate::select('rate', DB::raw('count(*) as count'))
            ->groupBy('rate')
            ->orderBy('rate')
            ->get()
            ->map(function ($item) {
                return [
                    'rating' => $item->rate . ' Star' . ($item->rate > 1 ? 's' : ''),
                    'count' => $item->count
                ];
            });

        // Top rated services
        $topRatedServices = Service::with(['rates', 'city'])
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

        // Most favorited services
        $mostFavoritedServices = Service::withCount('favorites')
            ->with('city')
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

        return view('dashboard.show', compact(
            'stats',
            'recentUsers',
            'recentServices',
            'recentContactMessages',
            'servicesByCity',
            'servicesByCategory',
            'monthlyUsers',
            'monthlyServices',
            'ratingDistribution',
            'topRatedServices',
            'mostFavoritedServices'
        ));
    }


    public function analytics()
    {
        // More detailed analytics data
        $data = [
            'dailyStats' => $this->getDailyStats(),
            'topCities' => $this->getTopCities(),
            'categoryPerformance' => $this->getCategoryPerformance(),
            'userGrowth' => $this->getUserGrowthData(),
        ];
        
        return view('dashboard.analytics', $data);
    }

    private function getDailyStats()
    {
        $stats = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $stats->push([
                'date' => $date->format('M d'),
                'users' => User::whereDate('created_at', $date)->count(),
                'services' => Service::whereDate('created_at', $date)->count(),
            ]);
        }
        return $stats;
    }

    private function getTopCities()
    {
        return City::withCount(['services', 'categories'])
            ->orderByDesc('services_count')
            ->take(10)
            ->get();
    }

    private function getCategoryPerformance()
    {
        return Category::select([
                'categories.id',
                'categories.name',
                DB::raw('COUNT(DISTINCT services.id) as services_count'),
                DB::raw('ROUND(AVG(rates.rate), 2) as average_rating'),
                DB::raw('COUNT(rates.id) as total_ratings')
            ])
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

    // private function getCategoryPerformance()
    // {
    //     return Category::select([
    //             'categories.id',
    //             'categories.name',
    //             DB::raw('COUNT(DISTINCT services.id) as services_count'),
    //             DB::raw('ROUND(AVG(rates.rate), 2) as average_rating'),
    //             DB::raw('COUNT(rates.id) as total_ratings')
    //         ])
    //         ->leftJoin('service_categories', 'categories.id', '=', 'service_categories.category_id')
    //         ->leftJoin('services', 'service_categories.service_id', '=', 'services.id')
    //         ->leftJoin('rates', 'services.id', '=', 'rates.service_id')
    //         ->groupBy('categories.id', 'categories.name')
    //         ->having('services_count', '>', 0)
    //         ->orderByDesc('services_count')
    //         ->take(15)
    //         ->get();
    // }

    private function getUserGrowthData()
    {
        return User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get();
    }
}