<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\InitController;
use App\Http\Requests\API\User\AuthRequest;
use App\Http\Requests\API\Users\Auth\ChangePasswordRequest;
use App\Http\Resources\API\AuthResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends InitController
{
    public function __construct()
    {
        parent::__construct();

        $this->pipeline->setModel('User');
    }

    public function login(Request $request)
    {
        $credentials = $request->only(['phone', 'password']);

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return jsonResponse(401, 'Wrong phone or password!');
        }

        $user = Auth::guard('api')->user();

        // Block unverified users from logging in
        if (!$user->is_verified) {
            Auth::guard('api')->logout();

            return jsonResponse(403, 'Your account is not verified. Please verify your phone number first.', [
                'is_verified' => false,
                'phone' => $user->phone,
            ]);
        }

        $user->access_token = $token;

        $data = new AuthResource($user);

        return jsonResponse(200, 'done.', $data);
    }

    public function register(AuthRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->only(['email', 'name', 'city_id', 'phone']);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $media = resizeImage($image, $this->storagePath, 'all_images' . DIRECTORY_SEPARATOR . 'users');
                $imageId = $media->id ?? null;
                $data['image_id'] = $imageId;
            }

            $data['password'] = Hash::make($request->password);
            $data['is_verified'] = false;

            $user = $this->pipeline->setModel('User')->create($data);
            $user->access_token = auth()->guard('api')->tokenById($user->id);

            DB::commit();

            // Send OTP after successful registration
            $lang = $request->header('Accept-Language', 'en');
            sendOtp($user->phone, $user->name ?? '', $lang);

            $data = new AuthResource($user);

            return jsonResponse(201, 'Registration successful. Please verify your phone number.', $data);
        } catch (\Exception $e) {
            DB::rollBack();

            return jsonResponse($e->getCode(), $e->getMessage());
        }
    }

    /**
     * Verify phone number using OTP code.
     * Used after registration to activate the account.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string|size:4',
        ]);

        $phone = $request->phone;
        $otp = $request->otp;

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return jsonResponse(404, 'User not found.');
        }

        if ($user->is_verified) {
            return jsonResponse(400, 'Account is already verified.');
        }

        // Verify OTP against database value
        if (!$user->otp_code || !$user->otp_expires_at || now()->greaterThan($user->otp_expires_at)) {
            return jsonResponse(400, 'Invalid or expired verification code.');
        }

        if ($user->otp_code !== $otp) {
            return jsonResponse(400, 'Invalid or expired verification code.');
        }

        // Mark user as verified and clear OTP
        $user->update([
            'is_verified' => true,
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        // Generate token for auto-login after verification
        $user->access_token = auth()->guard('api')->tokenById($user->id);

        $data = new AuthResource($user);

        return jsonResponse(200, 'Phone number verified successfully.', $data);
    }

    /**
     * Resend OTP for phone verification.
     * Can be used after registration or before login.
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $request->phone;

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return jsonResponse(404, 'User not found.');
        }

        if ($user->is_verified) {
            return jsonResponse(400, 'Account is already verified.');
        }

        $lang = $request->header('Accept-Language', 'en');
        $otpResult = sendOtp($phone, $user->name ?? '', $lang);

        if (!$otpResult['success']) {
            return jsonResponse(429, $otpResult['message']);
        }

        return jsonResponse(200, 'Verification code sent successfully.');
    }

    /**
     * Forgot password - send OTP to phone number.
     * Public endpoint, no auth required.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $request->phone;

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return jsonResponse(404, 'User not found.');
        }

        if (!$user->is_verified) {
            return jsonResponse(403, 'Account is not verified. Please verify your phone number first.');
        }

        $lang = $request->header('Accept-Language', 'en');
        $otpResult = sendOtp($phone, $user->name ?? '', $lang);

        if (!$otpResult['success']) {
            return jsonResponse(429, $otpResult['message']);
        }

        return jsonResponse(200, 'Password reset code sent successfully.');
    }

    /**
     * Reset password using OTP code.
     * Verifies the OTP, resets password, and returns a login token.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string|size:4',
            'password' => 'required|min:6|confirmed',
        ]);

        $phone = $request->phone;
        $otp = $request->otp;

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return jsonResponse(404, 'User not found.');
        }

        // Verify OTP against database value
        if (!$user->otp_code || !$user->otp_expires_at || now()->greaterThan($user->otp_expires_at)) {
            return jsonResponse(400, 'Invalid or expired verification code.');
        }

        if ($user->otp_code !== $otp) {
            return jsonResponse(400, 'Invalid or expired verification code.');
        }

        // Reset password and clear OTP
        $user->update([
            'password' => Hash::make($request->password),
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        // Auto-login after password reset
        $user->access_token = auth()->guard('api')->tokenById($user->id);

        $data = new AuthResource($user);

        return jsonResponse(200, 'Password reset successfully.', $data);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $password = $request->password;
        $oldPassword = $request->old_password;

        $user = Auth::guard('api')->user();

        if (!$user || !Hash::check($oldPassword, $user->password)) {
            return jsonResponse(400, 'Invalid password!');
        }

        $user->password = Hash::make($password);
        $user->save();

        $data = new AuthResource($user);

        return jsonResponse(201, 'done.', $data);
    }

    public function logout(Request $request)
    {
        auth()->logout();

        return jsonResponse(200, 'Successfully logged out');
    }
}
