<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\OldUser;
use App\Models\User;
use App\Models\OldRate;
use App\Models\Rate;

class UserMigrationController extends Controller
{
    /**
     * Migrate all users from old_users to users table
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function migrateAllUsers()
    {
        try {
            // Start a database transaction
            DB::beginTransaction();
            
            $oldUsers = OldUser::with(['phones', 'rates'])->get();
            
            $migrationResults = [
                'total_users' => count($oldUsers),
                'migrated_users' => 0,
                'failed_users' => 0,
                'user_details' => []
            ];
            
            foreach ($oldUsers as $oldUser) {
                
                $result = $this->migrateUser($oldUser);
                
                if ($result['success']) {
                    $migrationResults['migrated_users']++;
                } else {
                    $migrationResults['failed_users']++;
                }
                
                $migrationResults['user_details'][] = $result;
            }
            
            // Commit the transaction if everything went well
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'User migration completed',
                'results' => $migrationResults
            ]);
            
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();
            
            Log::error('User migration failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'User migration failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Migrate a single user by ID
     *
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function migrateSingleUser($userId)
    {
        try {
            // Start a database transaction
            DB::beginTransaction();
            
            $oldUser = OldUser::with(['phones', 'rates'])->findOrFail($userId);
            
            $result = $this->migrateUser($oldUser);
            
            // Commit the transaction if everything went well
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $result['success'] ? 'User migrated successfully' : 'User migration failed',
                'result' => $result
            ]);
            
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();
            
            Log::error('User migration failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'User migration failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check migration status for users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserMigrationStatus()
    {
        $oldUsersCount = OldUser::count();
        $newUsersCount = User::count();
        
        return response()->json([
            'success' => true,
            'status' => [
                'old_users_count' => $oldUsersCount,
                'new_users_count' => $newUsersCount,
                'progress_percentage' => $oldUsersCount > 0 ? round(($newUsersCount / $oldUsersCount) * 100, 2) : 0,
                'remaining_users' => $oldUsersCount - $newUsersCount > 0 ? $oldUsersCount - $newUsersCount : 0
            ]
        ]);
    }
    
    /**
     * Migrate a specific user
     *
     * @param OldUser $oldUser
     * @return array
     */
    private function migrateUser(OldUser $oldUser)
    {
        try {
            // Check if the user already exists
            $existingUser = User::where('email', $oldUser->email)->first();
            
            if ($existingUser) {
                return [
                    'success' => false,
                    'user_id' => $oldUser->id,
                    'message' => "User with email '{$oldUser->email}' already exists"
                ];
            }
            
            // Create the new user
            $newUser = new User();
            $newUser->id = $oldUser->id;
            $newUser->name = $oldUser->name;
            $newUser->email = $oldUser->email;
            
            // Handle password - check if it needs rehashing
            // If the old system used the same hashing algorithm as the new one
            // and the passwords are compatible, we can use the existing hash
            if (Hash::needsRehash($oldUser->password)) {
                // If we need to rehash, we'd need the original password
                // Since we likely don't have it, we might set a temporary one
                $tempPassword = Str::random(12);
                $newUser->password = Hash::make($tempPassword);
                
                // Here we would potentially send an email to the user
                // to inform them of their new password
                // Mail::to($newUser->email)->send(new TemporaryPasswordMail($tempPassword));
            } else {
                // Use the existing password hash
                $newUser->password = $oldUser->password;
            }
            
            $newUser->email_verified_at = $oldUser->email_verified_at;
            $newUser->remember_token = $oldUser->remember_token;
            $newUser->created_at = $oldUser->created_at;
            $newUser->updated_at = $oldUser->updated_at;
            $newUser->save();
            
            // Store old_id in a temporary attribute if needed
            // Alternatively, you could use a simple table to track this information
            // $newUser->meta = json_encode(['old_id' => $oldUser->id]);
            // $newUser->save();
            
            
            // Migrate user's ratings
            $this->migrateUserRatings($oldUser, $newUser);
            
            // Migrate user's phones
            $this->migrateUserPhones($oldUser, $newUser);
            
            return [
                'success' => true,
                'user_id' => [
                    'old' => $oldUser->id,
                    'new' => $newUser->id
                ],
                'email' => $newUser->email,
                'name' => $newUser->name
            ];
            
        } catch (\Exception $e) {
            Log::error('User migration failed: ' . $e->getMessage(), [
                'user_id' => $oldUser->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'user_id' => $oldUser->id,
                'message' => 'Migration failed: ' . $e->getMessage()
            ];
        }
    }
    
    
    /**
     * Migrate user's service ratings
     *
     * @param OldUser $oldUser
     * @param User $newUser
     * @return void
     */
    private function migrateUserRatings(OldUser $oldUser, User $newUser)
    {
        try {
            foreach ($oldUser->rates as $oldRate) {
                $newUser->rates()->create([
                    'service_id' => $oldRate->institute_id,
                    'rate' => $oldRate->rate
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to migrate user ratings: ' . $e->getMessage(), [
                'user_id' => $oldUser->id
            ]);
        }
    }
    private function migrateUserPhones(OldUser $oldUser, User $newUser)
    {
        
        try {
            foreach ($oldUser->phones as $oldPhone) {
                $newUser->phones()->create([
                    'user_id' => $newUser->id,
                    'phone' => $oldPhone->phone
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to migrate user ratings: ' . $e->getMessage(), [
                'user_id' => $oldUser->id
            ]);
        }
    }
}