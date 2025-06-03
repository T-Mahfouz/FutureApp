<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Auth;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{   
    use AuthenticatesUsers;
    protected $redirectTo = '/dashboard';

    public function showLoginForm()
	{
        return view('auth.login');
    }

    public function login(Request $request)
    {
        try {
            // Validate the incoming request
            $this->validateAdminLogin($request);

            // Extract credentials
            $credentials = $request->only('email', 'password');
            
            // Find admin by email
            $admin = Admin::where('email', $credentials['email'])->first();

            // Check if admin exists
            if (!$admin) {
                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'No admin account found with this email address.']);
            }

            // Check if password is correct
            if (!Hash::check($credentials['password'], $admin->password)) {
                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors(['password' => 'The provided password is incorrect.']);
            }

            // Check if admin account is active (if you have an 'active' field)
            if (isset($admin->active) && !$admin->active) {
                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'Your admin account has been deactivated. Please contact the system administrator.']);
            }

            // Login the admin using the 'admin' guard
            Auth::guard('admin')->login($admin, $request->filled('remember'));

            // Regenerate session to prevent fixation attacks
            $request->session()->regenerate();

            // Clear any login attempts
            $this->clearLoginAttempts($request);

            // Log successful login
            logger('Admin logged in successfully: ' . $admin->email);

            // Redirect to dashboard with success message
            return redirect()->route('dashboard')
                ->with('success', 'Welcome back, ' . $admin->name . '! You have been logged in successfully.');

        } catch (\Exception $e) {
            // Log the error
            logger('Admin login error: ' . $e->getMessage());
            
            // Redirect back with generic error
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'An error occurred during login. Please try again.'.$e->getMessage()]);
        }
    }

    public function logout(Request $request)
    {
        $adminName = Auth::guard('admin')->user()->name ?? 'Admin';
        
        Auth::guard('admin')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')
            ->with('success', 'Goodbye, ' . $adminName . '! You have been logged out successfully.');
    }




    protected function validateAdminLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
        ]);
    }
}
