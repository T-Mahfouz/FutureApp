<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\City;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class AdminsController extends Controller
{
    // Show all admins
    public function index()
    {
        $admins = Admin::with(['cities', 'image'])->paginate(25);
        return view('admin.index', compact('admins'));
    }

    // Show the form to create new admin
    public function create()
    {
        $admin = new Admin();
        $cities = City::orderBy('name')->get();
        return view('admin.edit', compact('admin', 'cities'));
    }

    // Show the form for editing the specified admin
    public function edit(Admin $admin)
    {
        $cities = City::orderBy('name')->get();
        return view('admin.edit', compact('admin', 'cities'));
    }

    // Save a newly created admin
    public function store(Request $request)
    {
        $admin = new Admin();
        return $this->update($request, $admin);
    }

    // Update the specified admin
    public function update(Request $request, Admin $admin)
    {
        // Base validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'cities' => 'nullable|array',
            'cities.*' => 'exists:cities,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        // Email validation - unique except current admin
        if($admin->id){
            $rules['email'] = 'required|email|unique:admins,email,' . $admin->id;
        } else {
            $rules['email'] = 'required|email|unique:admins,email';
        }

        // Password validation
        if(!$admin->id){
            // Required for new admins
            $rules['password'] = 'required|string|min:6|confirmed';
        } else {
            // Optional for existing admins, but must be confirmed if provided
            $rules['password'] = 'nullable|string|min:6|confirmed';
        }

        $request->validate($rules);

        // Handle image upload
        $imageId = $admin->image_id;
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
            if($admin->image_id && $admin->image){
                Storage::disk('public')->delete($admin->image->path);
                $admin->image->delete();
            }
        }

        // Update admin fields
        $admin->name = $request->input('name');
        $admin->email = $request->input('email');
        $admin->image_id = $imageId;

        // Hash password if provided
        if($request->input('password')){
            $admin->password = bcrypt($request->input('password'));
        }

        $admin->save();

        // Sync cities
        if($request->has('cities')){
            $admin->cities()->sync($request->input('cities'));
        } else {
            $admin->cities()->sync([]);
        }

        $message = $admin->wasRecentlyCreated ? 'Admin has been created successfully' : 'Admin has been updated successfully';

        return redirect()
            ->route('admin.index')
            ->with('status', $message);
    }

    // Delete the specified admin
    public function destroy(Admin $admin)
    {
        // Prevent self-deletion (assuming current user is admin)
        if($admin->id == auth()->guard('admin')->id()){
            return abort(401, 'You cannot delete yourself');
        }

        // Delete image if exists
        if($admin->image){
            Storage::disk('public')->delete($admin->image->path);
            $admin->image->delete();
        }

        $admin->delete();
        
        return redirect()
            ->route('admin.index')
            ->with('status', 'Admin has been deleted successfully');
    }
}