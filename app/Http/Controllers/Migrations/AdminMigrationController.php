<?php

namespace App\Http\Controllers\Migrations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\OldAdmin;
use App\Models\Admin;
use App\Models\Media;
use App\Models\City;

class AdminMigrationController extends Controller
{
    /**
     * Migrate all admins from old_admins to admins table
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function migrateAllAdmins()
    {
        try {
            // Start a database transaction
            DB::beginTransaction();
            
            $oldAdmins = OldAdmin::with(['cities'])->get();
            
            $migrationResults = [
                'total_admins' => count($oldAdmins),
                'migrated_admins' => 0,
                'failed_admins' => 0,
                'admin_details' => []
            ];
            
            foreach ($oldAdmins as $oldAdmin) {
                $result = $this->migrateAdmin($oldAdmin);
                
                if ($result['success']) {
                    $migrationResults['migrated_admins']++;
                } else {
                    $migrationResults['failed_admins']++;
                }
                
                $migrationResults['admin_details'][] = $result;
            }
            
            // Commit the transaction if everything went well
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Admin migration completed',
                'results' => $migrationResults
            ]);
            
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();
            
            Log::error('Admin migration failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Admin migration failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Migrate a single admin by ID
     *
     * @param int $adminId
     * @return \Illuminate\Http\JsonResponse
     */
    public function migrateSingleAdmin($adminId)
    {
        try {
            // Start a database transaction
            DB::beginTransaction();
            
            $oldAdmin = OldAdmin::with(['cities'])->findOrFail($adminId);
            
            $result = $this->migrateAdmin($oldAdmin);
            
            // Commit the transaction if everything went well
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $result['success'] ? 'Admin migrated successfully' : 'Admin migration failed',
                'result' => $result
            ]);
            
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();
            
            Log::error('Admin migration failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Admin migration failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check migration status for admins
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdminMigrationStatus()
    {
        $oldAdminsCount = OldAdmin::count();
        $newAdminsCount = Admin::count();
        
        return response()->json([
            'success' => true,
            'status' => [
                'old_admins_count' => $oldAdminsCount,
                'new_admins_count' => $newAdminsCount,
                'progress_percentage' => $oldAdminsCount > 0 ? round(($newAdminsCount / $oldAdminsCount) * 100, 2) : 0,
                'remaining_admins' => $oldAdminsCount - $newAdminsCount > 0 ? $oldAdminsCount - $newAdminsCount : 0
            ]
        ]);
    }
    
    /**
     * Migrate a specific admin
     *
     * @param OldAdmin $oldAdmin
     * @return array
     */
    private function migrateAdmin(OldAdmin $oldAdmin)
    {
        try {
            // Check if the admin already exists
            $existingAdmin = Admin::where('email', $oldAdmin->email)->first();
            
            if ($existingAdmin) {
                return [
                    'success' => false,
                    'admin_id' => $oldAdmin->id,
                    'message' => "Admin with email '{$oldAdmin->email}' already exists"
                ];
            }
            
            // Handle admin profile image if exists
            $imageId = null;
            if ($oldAdmin->image) {
                $media = $this->createMedia($oldAdmin->image);
                $imageId = $media->id;
            }
            
            // Create the new admin
            $newAdmin = new Admin();
            $newAdmin->id = $oldAdmin->id;
            $newAdmin->name = $oldAdmin->name ?? '';
            $newAdmin->email = $oldAdmin->email ?? '';
            
            $newAdmin->password = $oldAdmin->password;
            $newAdmin->image_id = $imageId;
            $newAdmin->phone = $oldAdmin->phone;
            $newAdmin->status = $oldAdmin->status;
            $newAdmin->last_seen = $oldAdmin->last_seen;
            $newAdmin->email_verified_at = $oldAdmin->email_verified_at;
            $newAdmin->remember_token = $oldAdmin->remember_token;
            $newAdmin->created_at = $oldAdmin->created_at;
            $newAdmin->updated_at = $oldAdmin->updated_at;
            $newAdmin->save();
            
            // Migrate admin's city associations
            $this->migrateAdminCities($oldAdmin, $newAdmin);
            
            return [
                'success' => true,
                'admin_id' => [
                    'old' => $oldAdmin->id,
                    'new' => $newAdmin->id
                ],
                'email' => $newAdmin->email,
                'name' => $newAdmin->name
            ];
            
        } catch (\Exception $e) {
            Log::error('Admin migration failed: ' . $e->getMessage(), [
                'admin_id' => $oldAdmin->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'admin_id' => $oldAdmin->id,
                'message' => 'Migration failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Migrate admin's city associations
     *
     * @param OldAdmin $oldAdmin
     * @param Admin $newAdmin
     * @return void
     */
    private function migrateAdminCities(OldAdmin $oldAdmin, Admin $newAdmin)
    {
        try {
            // For each old city association, find the corresponding new city
            foreach ($oldAdmin->cities as $oldCity) {
                // Find the corresponding new city
                // This assumes cities have been migrated and have the same name
                $newCity = City::where('name', $oldCity->name)->first();
                
                if ($newCity) {
                    // Attach the admin to the new city
                    $newAdmin->cities()->attach($newCity->id);
                } else {
                    Log::warning("Could not find new city matching '{$oldCity->name}' for admin ID {$oldAdmin->id}");
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to migrate admin cities: ' . $e->getMessage(), [
                'admin_id' => $oldAdmin->id
            ]);
        }
    }
}
