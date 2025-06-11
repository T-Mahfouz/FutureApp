<?php

namespace App\Http\Controllers\Migrations;

use App\Http\Controllers\Controller;
use App\Models\OldInstitute;
use App\Models\Ad;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AdMigrationController extends Controller
{
    /**
     * Transfer institutes with is_add=1 to ads
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function migrateAds(Request $request)
    {
        // Optional parameters
        $limit = $request->get('limit', 100);
        $offset = $request->get('offset', 0);
        $cityId = $request->get('city_id');
        
        try {
            DB::beginTransaction();
            
            // Build query for institutes that are ads (is_add = 1)
            $query = OldInstitute::where('is_add', 1);
            
            if ($cityId) {
                $query->where('city_id', $cityId);
            }
            
            $adInstitutes = $query->skip($offset)->take($limit)->get();
            $migratedCount = 0;
            $errors = [];
            
            foreach ($adInstitutes as $institute) {
                try {
                    $this->migrateAdInstitute($institute);
                    $migratedCount++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'institute_id' => $institute->id,
                        'error' => $e->getMessage()
                    ];
                    Log::error("Error migrating ad institute ID {$institute->id}: " . $e->getMessage());
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'total_processed' => $adInstitutes->count(),
                'successfully_migrated' => $migratedCount,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Ad migration process failed: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => "Ad migration failed: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Migrate a single institute with is_add=1 to an ad
     * 
     * @param OldInstitute $institute
     * @return Ad
     */
    protected function migrateAdInstitute(OldInstitute $institute)
    {
        // Check if this institute was already migrated as an ad
        $existingAd = Ad::where('name', $institute->name)
            ->where('city_id', $this->mapCityId($institute->city_id))
            ->first();
            
        if ($existingAd) {
            return $existingAd;
        }
        
        // Map the city ID from old system to new system
        $newCityId = $this->mapCityId($institute->city_id);
        
        // If we couldn't find a valid city ID, throw an exception
        if (!$newCityId) {
            throw new \Exception("No matching city found for old city ID: {$institute->city_id}");
        }
        
        // Create the ad
        $ad = new Ad([
            'name' => $institute->name,
            'city_id' => $newCityId,
            'created_at' => $institute->created_at,
            'updated_at' => $institute->updated_at,
            'location' => $this->determineAdLocation($institute)
        ]);
        
        // Migrate main image if exists
        if ($institute->image) {
            $media = $this->migrateImage($institute->image);
            if ($media) {
                $ad->image_id = $media->id;
            }
        }
        
        $ad->save();
        
        return $ad;
    }
    
    /**
     * Determine ad location based on institute data
     * 
     * @param OldInstitute $institute
     * @return string
     */
    protected function determineAdLocation(OldInstitute $institute)
    {
        // Placeholder for location determination logic
        // This could be based on different factors in your system
        // Examples: 'home_top', 'category_list', 'detail_page', etc.
        
        if ($institute->ad_location) {
            return $institute->ad_location;
        }
        
        if ($institute->premium && $institute->premium == 1) {
            return 'premium';
        }
        
        if ($institute->featured && $institute->featured == 1) {
            return 'featured';
        }
        
        return 'standard'; // Default location
    }
    
    /**
     * Migrate image and create Media record
     * 
     * @param string $imagePath
     * @return Media|null
     */
    protected function migrateImage($imagePath)
    {
        if (!$imagePath) {
            return null;
        }
        
        // Check if media already exists
        $existingMedia = Media::where('path', $imagePath)->first();
        
        if ($existingMedia) {
            return $existingMedia;
        }
        
        // Create new media record
        $media = new Media([
            'path' => $imagePath,
            'type' => 'image'
        ]);
        
        $media->save();
        
        return $media;
    }
    
    /**
     * Map old city ID to new city ID
     * 
     * @param int $oldCityId
     * @return int|null
     */
    protected function mapCityId($oldCityId)
    {
        // Use cache to avoid repeated queries
        $cityMapping = Cache::remember('city_id_mapping', 3600, function () {
            $mapping = [];
            
            // Method 1: Check if IDs match directly
            $cities = \App\Models\City::all(['id']);
            $oldCities = \App\Models\OldCity::all(['id']);
            
            $existingIds = $cities->pluck('id')->toArray();
            $oldIds = $oldCities->pluck('id')->toArray();
            
            // Find which IDs exist in both tables
            $commonIds = array_intersect($oldIds, $existingIds);
            
            foreach ($commonIds as $id) {
                $mapping[$id] = $id;
            }
            
            // Method 2: For remaining IDs, try to match by name
            $unmappedOldCities = \App\Models\OldCity::whereNotIn('id', array_keys($mapping))->get(['id', 'name']);
            
            foreach ($unmappedOldCities as $oldCity) {
                $newCity = \App\Models\City::where('name', $oldCity->name)->first();
                
                if ($newCity) {
                    $mapping[$oldCity->id] = $newCity->id;
                }
            }
            
            return $mapping;
        });
        
        return $cityMapping[$oldCityId] ?? null;
    }
    
    /**
     * Get migration status
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdMigrationStatus()
    {
        $totalAdInstitutes = OldInstitute::where('is_add', 1)->count();
        $totalAds = Ad::count();
        
        return response()->json([
            'total_ad_institutes' => $totalAdInstitutes,
            'total_ads' => $totalAds,
            'estimated_progress' => $totalAdInstitutes > 0 ? round(($totalAds / $totalAdInstitutes) * 100, 2) : 0
        ]);
    }
    
    /**
     * Reset ad migration (for testing purposes)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetAdMigration()
    {
        // Only allow this in development environment
        if (app()->environment() !== 'local') {
            return response()->json([
                'message' => 'This action is only available in development environment'
            ], 403);
        }
        
        try {
            DB::beginTransaction();
            
            // Delete all ads
            Ad::truncate();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Ad migration reset successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Reset failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
