<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\DeleteProfileRequest;
use App\Http\Requests\API\ProfileRequest;
use App\Http\Resources\API\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfileController extends InitController
{
    public function __construct()
    {
        parent::__construct();
        $this->pipeline->setModel('User');
    }

    /**
     * Get current user profile information
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getProfile(Request $request): JsonResponse
    {
        $user = $this->pipeline
            ->with(['image', 'city', 'favorites.service', 'rates.service'])
            ->find($this->user->id);

        if (!$user) {
            return jsonResponse(404, 'User not found.');
        }

        $data = new UserResource($user);

        return jsonResponse(200, 'Profile retrieved successfully.', $data);
    }

    /**
     * Update user profile information
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(ProfileRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = User::find($this->user->id);
            
            // Handle image upload if provided
            if ($request->hasFile('image')) {
                
                $media = resizeImage($request->file('image'), $this->storagePath, 'all_images'.DIRECTORY_SEPARATOR.'users');
            
                $imageId = $media->id ?? null;

                if ($imageId) {
                    deleteImage($user->image_id);
                    $user->image_id = $imageId;
                }
            }

            // Update user fields
            if ($request->has('name')) {
                $user->name = $request->name;
            }
            
            if ($request->has('email')) {
                $user->email = $request->email;
                $user->email_verified_at = null; // Reset email verification if email changed
            }
            
            if ($request->has('phone')) {
                $user->phone = $request->phone;
            }
            
            if ($request->has('city_id')) {
                $user->city_id = $request->city_id;
            }
            
            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            DB::commit();

            // Reload user with relationships
            $updatedUser = $this->pipeline
                ->with(['image', 'city'])
                ->find($user->id);

            $data = new UserResource($updatedUser);

            return jsonResponse(200, 'Profile updated successfully.', $data);

        } catch (\Exception $e) {
            DB::rollBack();
            return jsonResponse(500, 'Failed to update profile. Please try again.');
        }
    }

    /**
     * Delete user account and all related data
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAccount(DeleteProfileRequest $request): JsonResponse
    {
        // Verify password
        if (!Hash::check($request->password, $this->user->password)) {
            return jsonResponse(422, 'Invalid password.');
        }

        try {
            DB::beginTransaction();

            $user = User::find($this->user->id);
            
            // Delete user's image if exists
            if ($user->image_id) {
                deleteImage($user->image_id);
            }

            // Delete related data (cascading deletes)
            // Note: You might want to keep some data for business purposes
            // and just mark the user as "deleted" instead of hard delete
            
            // Delete user's favorites
            $user->favorites()->delete();
            
            // Delete user's rates
            $user->rates()->delete();
            
            // Update contact us records to remove user association
            // (keeping the messages for business records but anonymizing them)
            DB::table('contact_us')
                ->where('user_id', $user->id)
                ->update(['user_id' => null]);

            // Finally delete the user
            $user->delete();

            DB::commit();

            return jsonResponse(200, 'Account deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return jsonResponse(500, 'Failed to delete account. Please try again.');
        }
    }
}