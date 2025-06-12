<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactUs;
use App\Models\City;
use Illuminate\Support\Facades\Auth;

class ContactUsController extends Controller
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
     * Apply city restriction to query
     */
    private function applyCityRestriction($query)
    {
        $accessibleCityIds = $this->getAccessibleCityIds();
        return $query->whereIn('city_id', $accessibleCityIds);
    }

    // Show all contact messages
    public function index(Request $request)
    {
        $query = ContactUs::with(['city', 'user']);
        
        // Apply city restriction based on admin's assigned cities
        $query = $this->applyCityRestriction($query);
        
        // Filter by city if provided (only show cities admin has access to)
        if ($request->has('city_id') && $request->city_id) {
            $accessibleCityIds = $this->getAccessibleCityIds();
            if (in_array($request->city_id, $accessibleCityIds)) {
                $query->where('city_id', $request->city_id);
            }
        }
        
        // Filter by read status if provided
        if ($request->has('status') && $request->status !== '') {
            if ($request->status == 'read') {
                $query->where('is_read', true);
            } elseif ($request->status == 'unread') {
                $query->where('is_read', false);
            }
            // If status is empty or anything else, don't add where clause (show all)
        }
        
        // Search by name, phone, or message
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }
        
        $contacts = $query->latest()->paginate(25);
        
        // Only show cities admin has access to in filter dropdown
        $accessibleCityIds = $this->getAccessibleCityIds();
        $cities = City::whereIn('id', $accessibleCityIds)->get();
        
        return view('contact.index', compact('contacts', 'cities'));
    }

    // Show the specified contact message
    public function show(ContactUs $contact)
    {
        // Check if admin has access to this contact's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($contact->city_id, $accessibleCityIds)) {
            abort(403, 'You do not have permission to view this contact message.');
        }

        $contact->load(['city', 'user']);
        
        // Mark as read when viewed
        if (!$contact->is_read) {
            $contact->update(['is_read' => true]);
        }
        
        return view('contact.show', compact('contact'));
    }

    // Toggle read status
    public function toggleRead(ContactUs $contact)
    {
        // Check if admin has access to this contact's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($contact->city_id, $accessibleCityIds)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to modify this contact message.'
            ], 403);
        }

        $contact->update(['is_read' => !$contact->is_read]);
        
        $status = $contact->is_read ? 'marked as read' : 'marked as unread';
        
        return redirect()->back()->with('status', "Message has been {$status}");
    }

    // Delete the specified contact message
    public function destroy(ContactUs $contact)
    {
        // Check if admin has access to this contact's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($contact->city_id, $accessibleCityIds)) {
            abort(403, 'You do not have permission to delete this contact message.');
        }

        $contact->delete();
        
        return redirect()
            ->route('contact.index')
            ->with('status', 'Contact message has been deleted successfully');
    }

    // Bulk actions
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:mark_read,mark_unread,delete',
            'contact_ids' => 'required|array',
            'contact_ids.*' => 'exists:contact_us,id'
        ]);

        $accessibleCityIds = $this->getAccessibleCityIds();
        
        // Only get contacts that are in accessible cities
        $contacts = ContactUs::whereIn('id', $request->contact_ids)
                            ->whereIn('city_id', $accessibleCityIds);

        $contactsToProcess = $contacts->get();
        $processedCount = $contactsToProcess->count();
        $totalRequested = count($request->contact_ids);

        if ($processedCount == 0) {
            return redirect()->route('contact.index')
                          ->with('error', 'No messages were processed. You may not have permission to access the selected messages.');
        }

        switch ($request->action) {
            case 'mark_read':
                $contacts->update(['is_read' => true]);
                $message = "Marked {$processedCount} messages as read";
                break;
            case 'mark_unread':
                $contacts->update(['is_read' => false]);
                $message = "Marked {$processedCount} messages as unread";
                break;
            case 'delete':
                $contacts->delete();
                $message = "Deleted {$processedCount} messages successfully";
                break;
        }

        // Add notice if some messages were skipped due to permissions
        if ($processedCount < $totalRequested) {
            $skipped = $totalRequested - $processedCount;
            $message .= ". {$skipped} messages were skipped (no permission).";
        }

        return redirect()->route('contact.index')->with('status', $message);
    }

    // Get statistics for dashboard or reports
    public function getStats()
    {
        $accessibleCityIds = $this->getAccessibleCityIds();
        
        $stats = [
            'total' => ContactUs::whereIn('city_id', $accessibleCityIds)->count(),
            'unread' => ContactUs::whereIn('city_id', $accessibleCityIds)->where('is_read', false)->count(),
            'read' => ContactUs::whereIn('city_id', $accessibleCityIds)->where('is_read', true)->count(),
            'today' => ContactUs::whereIn('city_id', $accessibleCityIds)->whereDate('created_at', today())->count(),
        ];
        
        return $stats;
    }

    // Mark multiple messages as read (for notifications)
    public function markAsRead(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:contact_us,id'
        ]);

        $accessibleCityIds = $this->getAccessibleCityIds();
        
        $updatedCount = ContactUs::whereIn('id', $request->ids)
                                ->whereIn('city_id', $accessibleCityIds)
                                ->where('is_read', false)
                                ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => "Marked {$updatedCount} messages as read",
            'updated_count' => $updatedCount
        ]);
    }

    // Get unread count for notifications
    public function getUnreadCount()
    {
        $accessibleCityIds = $this->getAccessibleCityIds();
        
        $count = ContactUs::whereIn('city_id', $accessibleCityIds)
                         ->where('is_read', false)
                         ->count();

        return response()->json(['unread_count' => $count]);
    }
}