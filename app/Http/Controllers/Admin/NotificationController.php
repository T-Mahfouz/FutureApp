<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\City;
use App\Models\News;
use App\Models\Service;
use App\Models\Media;
use App\Helpers\FirebaseHelper;
use Illuminate\Support\Facades\Storage;

class NotificationController extends Controller
{
    // Show all notifications
    public function index()
    {
        $notifications = Notification::with(['service', 'image', 'news'])
                     ->latest()
                     ->paginate(25);
        return view('notification.index', compact('notifications'));
    }

    // Show the form to create new notification
    public function create()
    {
        $cities = City::all();
        $services = Service::with('city')->get();
        $news = News::all();
        $notification = new Notification();
        
        return view('notification.edit', compact('notification', 'cities', 'services', 'news'));
    }

    // Show the form for editing the specified notification
    public function edit(Notification $notification)
    {
        $cities = City::all();
        $services = Service::with('city')->get();
        $news = News::all();
        
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
        // Validation rules
        $rules = [
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'service_id' => 'nullable|exists:services,id',
            'news_id' => 'nullable|exists:news,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'target_type' => 'required|in:broadcast,cities,service,news',
            'city_ids' => 'required_if:target_type,cities|array',
            'city_ids.*' => 'exists:cities,id',
            'send_firebase' => 'boolean',
        ];

        $request->validate($rules);

        // Handle image upload
        $imageId = $notification->image_id;
        if($request->hasFile('image')){
            $image = $request->file('image');
            $path = $image->store('all_images', 'public');
            
            // Create media record
            $media = Media::create([
                'path' => $path,
                'type' => 'image'
            ]);
            
            $imageId = $media->id;
            
            // Delete old image if exists
            if($notification->image_id && $notification->image){
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

        // Send Firebase notification if requested
        if ($request->boolean('send_firebase')) {
            $this->sendFirebaseNotification($request, $notification);
        }

        $message = $notification->wasRecentlyCreated ? 'Notification has been created successfully' : 'Notification has been updated successfully';

        return redirect()
            ->route('notification.index')
            ->with('status', $message);
    }

    // Show the specified notification details
    public function show(Notification $notification)
    {
        $notification->load(['service', 'image', 'news']);
        return view('notification.show', compact('notification'));
    }

    // Delete the specified notification
    public function destroy(Notification $notification)
    {
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

    // Send Firebase notification only
    public function sendFirebase(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'target_type' => 'required|in:broadcast,cities,service,news',
            'city_ids' => 'required_if:target_type,cities|array',
            'city_ids.*' => 'exists:cities,id',
            'service_id' => 'nullable|exists:services,id',
            'news_id' => 'nullable|exists:news,id',
        ];

        $request->validate($rules);

        $result = $this->sendFirebaseNotification($request);

        if ($result['success']) {
            return redirect()->back()->with('status', 'Firebase notification sent successfully');
        } else {
            return redirect()->back()->with('error', 'Failed to send Firebase notification: ' . $result['message']);
        }
    }

    private function sendFirebaseNotification($request, $notification = null)
    {
        $title = $request->input('title');
        $body = $request->input('body');
        $targetType = $request->input('target_type');
        
        // Get image URL if exists
        $imageUrl = null;
        if ($notification && $notification->image) {
            $imageUrl = asset('storage/' . $notification->image->path);
        }

        $result = ['success' => false, 'message' => 'Unknown error'];

        switch ($targetType) {
            case 'broadcast':
                $result = FirebaseHelper::sendBroadcast($title, $body, $imageUrl);
                break;
                
            case 'cities':
                $cityIds = $request->input('city_ids', []);
                $result = FirebaseHelper::sendToMultipleCities($cityIds, $title, $body, $imageUrl);
                break;
                
            case 'service':
                $serviceId = $request->input('service_id');
                if ($serviceId) {
                    $service = Service::find($serviceId);
                    if ($service && $service->city_id) {
                        $result = FirebaseHelper::sendToCityTopic($service->city_id, $title, $body, $imageUrl, [
                            'type' => 'service',
                            'service_id' => $serviceId
                        ]);
                    }
                }
                break;
                
            case 'news':
                $newsId = $request->input('news_id');
                if ($newsId) {
                    $result = FirebaseHelper::sendBroadcast($title, $body, $imageUrl, [
                        'type' => 'news',
                        'news_id' => $newsId
                    ]);
                }
                break;
        }

        return $result;
    }
}