<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Service;
use App\Models\News;
use App\Models\Media;
use App\Models\City;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
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
     * Apply city restriction to query based on directly assigned cities
     */
    private function applyCityRestriction($query)
    {
        $accessibleCityIds = $this->getAccessibleCityIds();
        
        // Filter notifications that are assigned to accessible cities
        return $query->whereHas('cities', function($cityQuery) use ($accessibleCityIds) {
            $cityQuery->whereIn('cities.id', $accessibleCityIds);
        });
    }

    // Show all notifications
    public function index(Request $request)
    {
        $query = Notification::with(['service.city', 'news.city', 'image', 'cities']);
        
        // Apply city restriction based on admin's assigned cities
        $query = $this->applyCityRestriction($query);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('body', 'LIKE', "%{$search}%")
                  ->orWhereHas('service', function($serviceQuery) use ($search) {
                      $serviceQuery->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('news', function($newsQuery) use ($search) {
                      $newsQuery->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('cities', function($cityQuery) use ($search) {
                      $cityQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        // Filter by notification type
        if ($request->filled('type')) {
            switch ($request->get('type')) {
                case 'service':
                    $query->whereNotNull('service_id');
                    break;
                case 'news':
                    $query->whereNotNull('news_id');
                    break;
            }
        }
        
        // Filter by city
        if ($request->filled('city_id')) {
            $accessibleCityIds = $this->getAccessibleCityIds();
            if (in_array($request->get('city_id'), $accessibleCityIds)) {
                $cityId = $request->get('city_id');
                $query->whereHas('cities', function($cityQuery) use ($cityId) {
                    $cityQuery->where('cities.id', $cityId);
                });
            }
        }
        
        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['title', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }
        
        $notifications = $query->paginate(25)->withQueryString();
        
        // Get filter options - only cities admin has access to
        $accessibleCityIds = $this->getAccessibleCityIds();
        $cities = City::whereIn('id', $accessibleCityIds)->orderBy('name')->get();
        
        return view('notification.index', compact('notifications', 'cities'));
    }

    // Show the form to create new notification
    public function create()
    {
        $notification = new Notification();
        
        // Only show services and news from accessible cities
        $accessibleCityIds = $this->getAccessibleCityIds();
        
        $services = Service::whereIn('city_id', $accessibleCityIds)
                          ->orderBy('name')
                          ->get();
        
        $news = News::whereIn('city_id', $accessibleCityIds)
                   ->orderBy('name')
                   ->get();

        $cities = City::whereIn('id', $accessibleCityIds)->orderBy('name')->get();

        return view('notification.edit', compact('notification', 'services', 'news', 'cities'));
    }

    // Show the form for editing the specified notification
    public function edit(Notification $notification)
    {
        // Check if admin has access to this notification
        $accessibleCityIds = $this->getAccessibleCityIds();
        $notificationCityIds = $notification->cities->pluck('id')->toArray();
        
        // Admin must have access to at least one of the notification's cities
        $hasAccess = !empty(array_intersect($accessibleCityIds, $notificationCityIds));
        
        if (!$hasAccess) {
            abort(403, 'You do not have permission to edit this notification.');
        }

        // Only show services and news from accessible cities
        $services = Service::whereIn('city_id', $accessibleCityIds)
                          ->orderBy('name')
                          ->get();
        
        $news = News::whereIn('city_id', $accessibleCityIds)
                   ->orderBy('name')
                   ->get();
        
        $cities = City::whereIn('id', $accessibleCityIds)->orderBy('name')->get();

        return view('notification.edit', compact('notification', 'cities', 'services', 'news'));
    }

    // Save a newly created notification
    public function store(Request $request)
    {
        $notification = new Notification();
        return $this->update($request, $notification);
    }

    // Update the specified notification
    public function update(Request $request, Notification $notification)
    {
        // For existing notifications, check access
        if ($notification->exists) {
            $accessibleCityIds = $this->getAccessibleCityIds();
            $notificationCityIds = $notification->cities->pluck('id')->toArray();
            
            $hasAccess = !empty(array_intersect($accessibleCityIds, $notificationCityIds));
            
            if (!$hasAccess) {
                abort(403, 'You do not have permission to edit this notification.');
            }
        }

        $accessibleCityIds = $this->getAccessibleCityIds();

        // Validation rules
        $rules = [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'city_ids' => 'required|array|min:1',
            'city_ids.*' => ['required', 'exists:cities,id', function ($attribute, $value, $fail) use ($accessibleCityIds) {
                if (!in_array($value, $accessibleCityIds)) {
                    $fail('You do not have permission to assign notifications to this city.');
                }
            }],
            'service_id' => ['nullable', 'exists:services,id', function ($attribute, $value, $fail) use ($accessibleCityIds) {
                if ($value) {
                    $service = Service::find($value);
                    if ($service && !in_array($service->city_id, $accessibleCityIds)) {
                        $fail('You do not have permission to create notifications for this service.');
                    }
                }
            }],
            'news_id' => ['nullable', 'exists:news,id', function ($attribute, $value, $fail) use ($accessibleCityIds) {
                if ($value) {
                    $news = News::find($value);
                    if ($news && !in_array($news->city_id, $accessibleCityIds)) {
                        $fail('You do not have permission to create notifications for this news.');
                    }
                }
            }],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        // Either service or news is required
        $rules['service_or_news'] = ['required', function ($attribute, $value, $fail) use ($request) {
            if (!$request->service_id && !$request->news_id) {
                $fail('You must select either a service or news item for this notification.');
            }
            if ($request->service_id && $request->news_id) {
                $fail('You can only select either a service OR news item, not both.');
            }
        }];

        $request->validate($rules);

        // Handle image upload
        $imageId = $notification->image_id;
        if($request->hasFile('image')){
            $image = $request->file('image');
            
            $media = resizeImage($image, $this->storagePath);
            
            $imageId = $media->id ?? null;
            
            // Delete old image if exists
            if($imageId && $notification->image_id && $notification->image){
                Storage::disk('public')->delete($notification->image->path);
                $notification->image->delete();
            }
        }

        // Update notification fields
        $notification->title = $request->input('title');
        $notification->body = $request->input('body');
        $notification->service_id = $request->input('service_id');
        $notification->news_id = $request->input('news_id');
        $notification->image_id = $imageId;

        $notification->save();

        // Sync cities
        $notification->cities()->sync($request->input('city_ids'));

        $message = $notification->wasRecentlyCreated ? 'Notification has been created successfully' : 'Notification has been updated successfully';

        return redirect()
            ->route('notification.index')
            ->with('status', $message);
    }

    // Show the specified notification details
    public function show(Notification $notification)
    {
        // Check if admin has access to this notification
        $accessibleCityIds = $this->getAccessibleCityIds();
        $notificationCityIds = $notification->cities->pluck('id')->toArray();
        
        $hasAccess = !empty(array_intersect($accessibleCityIds, $notificationCityIds));
        
        if (!$hasAccess) {
            abort(403, 'You do not have permission to view this notification.');
        }

        $notification->load(['service.city', 'news.city', 'image', 'cities']);
        return view('notification.show', compact('notification'));
    }

    // Delete the specified notification
    public function destroy(Notification $notification)
    {
        // Check if admin has access to this notification
        $accessibleCityIds = $this->getAccessibleCityIds();
        $notificationCityIds = $notification->cities->pluck('id')->toArray();
        
        $hasAccess = !empty(array_intersect($accessibleCityIds, $notificationCityIds));
        
        if (!$hasAccess) {
            abort(403, 'You do not have permission to delete this notification.');
        }

        // Delete image if exists
        if($notification->image){
            Storage::disk('public')->delete($notification->image->path);
            $notification->image->delete();
        }

        $notification->delete();
        
        return redirect()
            ->route('notification.index')
            ->with('status', 'Notification has been deleted successfully');
    }

    // Send Firebase notification
    public function sendFirebase()
    {
        // Only show services and news from accessible cities
        $accessibleCityIds = $this->getAccessibleCityIds();
        
        $services = Service::whereIn('city_id', $accessibleCityIds)
                          ->orderBy('name')
                          ->get();
        
        $news = News::whereIn('city_id', $accessibleCityIds)
                   ->orderBy('name')
                   ->get();
        
        $cities = City::whereIn('id', $accessibleCityIds)->orderBy('name')->get();
        
        return view('notification.firebase', compact('services', 'news', 'cities'));
    }

    // Process Firebase notification sending
    public function processFirebase(Request $request)
    {
        $accessibleCityIds = $this->getAccessibleCityIds();

        $rules = [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'target_type' => 'required|in:all,city,service,news',
            'target_id' => 'nullable|integer',
        ];

        if ($request->target_type === 'city') {
            $rules['target_id'] = ['required', 'exists:cities,id', function ($attribute, $value, $fail) use ($accessibleCityIds) {
                if (!in_array($value, $accessibleCityIds)) {
                    $fail('You do not have permission to send notifications to this city.');
                }
            }];
        } elseif ($request->target_type === 'service') {
            $rules['target_id'] = ['required', 'exists:services,id', function ($attribute, $value, $fail) use ($accessibleCityIds) {
                $service = Service::find($value);
                if ($service && !in_array($service->city_id, $accessibleCityIds)) {
                    $fail('You do not have permission to send notifications for this service.');
                }
            }];
        } elseif ($request->target_type === 'news') {
            $rules['target_id'] = ['required', 'exists:news,id', function ($attribute, $value, $fail) use ($accessibleCityIds) {
                $news = News::find($value);
                if ($news && !in_array($news->city_id, $accessibleCityIds)) {
                    $fail('You do not have permission to send notifications for this news.');
                }
            }];
        }

        $request->validate($rules);

        // Here you would implement the actual Firebase sending logic
        // For now, we'll just simulate success
        
        return redirect()
            ->route('notification.send-firebase')
            ->with('status', 'Firebase notification has been sent successfully');
    }

    // Bulk actions
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete',
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'exists:notifications,id'
        ]);

        $accessibleCityIds = $this->getAccessibleCityIds();
        $deletedItems = [];
        $errors = [];

        if ($request->action === 'delete') {
            foreach ($request->notification_ids as $notificationId) {
                try {
                    $notification = Notification::with('cities')->find($notificationId);
                    if (!$notification) continue;

                    // Check if admin has access to this notification
                    $notificationCityIds = $notification->cities->pluck('id')->toArray();
                    $hasAccess = !empty(array_intersect($accessibleCityIds, $notificationCityIds));

                    if (!$hasAccess) {
                        $errors[] = "No permission to delete notification '{$notification->title}'";
                        continue;
                    }

                    $notificationTitle = $notification->title;

                    // Delete image if exists
                    if($notification->image){
                        Storage::disk('public')->delete($notification->image->path);
                        $notification->image->delete();
                    }

                    $notification->delete();
                    $deletedItems[] = "notification '{$notificationTitle}'";

                } catch (\Exception $e) {
                    $errors[] = "Error deleting notification '{$notificationTitle}': " . $e->getMessage();
                }
            }
        }

        $message = "";
        if (!empty($deletedItems)) {
            $message = "Successfully deleted: " . implode(', ', $deletedItems);
        }
        if (!empty($errors)) {
            $message .= (!empty($message) ? " | " : "") . "Errors: " . implode(", ", $errors);
        }

        return redirect()
            ->route('notification.index')
            ->with(!empty($deletedItems) ? 'status' : 'error', $message ?: 'No items were deleted.');
    }
}