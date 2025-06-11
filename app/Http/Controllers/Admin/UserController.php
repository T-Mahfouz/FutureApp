<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\City;

class UserController extends Controller
{
	
	// Show all users
    public function index()
    {
		$users = User::with('city')->paginate(25);
		return view('user.index', compact('users'));
    }
	
	// Show the form to create new user
    public function create()
    {
        $user = new User();
        $cities = City::orderBy('name')->get();
        return view('user.edit', compact('user', 'cities'));
    }
	
    // Show the form for editing the specified user
    public function edit(User $user){
        $cities = City::orderBy('name')->get();
        return view('user.edit', compact('user', 'cities'));
    }
	
    // Save a newly created user
    public function store(Request $request){
        $user = new User();
        return $this->update($request, $user);
    }
    
    // Update the specified user
    public function update(Request $request, User $user){
        // Base validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'city_id' => 'nullable|exists:cities,id',
        ];

        // Email validation - unique except current user
        if($user->id){
            $rules['email'] = 'required|email|unique:users,email,' . $user->id;
        } else {
            $rules['email'] = 'required|email|unique:users,email';
        }

        // Phone validation - unique except current user, can be empty
        if($user->id){
            $rules['phone'] = 'nullable|string|unique:users,phone,' . $user->id;
        } else {
            $rules['phone'] = 'nullable|string|unique:users,phone';
        }

        // Password validation
        if(!$user->id){
            // Required for new users
            $rules['password'] = 'required|string|min:6|confirmed';
        } else {
            // Optional for existing users, but must be confirmed if provided
            $rules['password'] = 'nullable|string|min:6|confirmed';
        }

        $request->validate($rules);

        // Update user fields
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');
        $user->city_id = $request->input('city_id');

        // Hash password if provided
        if($request->input('password')){
            $user->password = bcrypt($request->input('password'));
        }
		
        $message = $user->id ? 'User has been updated successfully' : 'User has been created successfully';
        $user->save();
		
        return redirect()
            ->route('user.index')
            ->with('status', $message);
    }
	
    // Delete the specified user
    public function destroy(User $user)
    {
        if($user->id == auth()->user()->id){
            return abort(401);
        }
		
        $user->delete();
        return redirect()
            ->route('user.index')->with('status', 'User has been deleted successfully');
    }
	
}