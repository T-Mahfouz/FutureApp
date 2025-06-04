<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactUs;
use App\Models\City;

class ContactUsController extends Controller
{
    // Show all contact messages
    public function index(Request $request)
    {
        $query = ContactUs::with(['city', 'user']);
        
        // Filter by city if provided
        if ($request->has('city_id') && $request->city_id) {
            $query->where('city_id', $request->city_id);
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
        $cities = City::all();
        
        return view('contact.index', compact('contacts', 'cities'));
    }

    // Show the specified contact message
    public function show(ContactUs $contact)
    {
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
        $contact->update(['is_read' => !$contact->is_read]);
        
        $status = $contact->is_read ? 'marked as read' : 'marked as unread';
        
        return redirect()->back()->with('status', "Message has been {$status}");
    }

    // Delete the specified contact message
    public function destroy(ContactUs $contact)
    {
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

        $contacts = ContactUs::whereIn('id', $request->contact_ids);

        switch ($request->action) {
            case 'mark_read':
                $contacts->update(['is_read' => true]);
                $message = 'Selected messages marked as read';
                break;
            case 'mark_unread':
                $contacts->update(['is_read' => false]);
                $message = 'Selected messages marked as unread';
                break;
            case 'delete':
                $contacts->delete();
                $message = 'Selected messages deleted successfully';
                break;
        }

        return redirect()->route('contact.index')->with('status', $message);
    }
}