<?php

namespace App\Http\Controllers\Migrations;

use App\Http\Controllers\Controller;
use App\Models\OldInstitute;
use App\Models\Service;
use App\Models\Category;
use App\Models\Media;
use App\Models\ServiceCategory;
use App\Models\ServicePhone;
use App\Models\Rate;
use App\Models\OldInstituteImage;
use App\Models\OldRate;
use App\Models\OldInstituteCategory;
use App\Models\City;
use App\Models\OldCity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class InstituteMigrationController extends Controller
{
    /**
     * Transfer data from old institutes to new services
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function parents(Request $request)
    {
         ini_set('max_execution_time', 1500);

        // Optional parameters
        $limit = $request->get('limit', 100);
        $offset = $request->get('offset', 0);
        $cityId = $request->get('city_id');
        
        try {
            DB::beginTransaction();
            
            $institutes = OldInstitute::whereNull('institute_id')->get();
            
            $errors = [];
            $migratedCount = 0;
            foreach ($institutes as $institute) {
                try {
                    $this->migrateInstitute($institute);
                    $migratedCount++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'institute_id' => $institute->id,
                        'error' => $e->getMessage()
                    ];
                    Log::error("Error migrating institute ID {$institute->id}: " . $e->getMessage());
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'total_processed' => $institutes->count(),
                'successfully_migrated' => $migratedCount,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Migration process failed: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => "Migration failed: " . $e->getMessage()
            ], 500);
        }
    }
    
    public function children(Request $request)
    {
         ini_set('max_execution_time', 1500);

        // Optional parameters
        $limit = $request->get('limit', 100);
        $offset = $request->get('offset', 0);
        $cityId = $request->get('city_id');
        
        try {
            DB::beginTransaction();
            
            $institutes = OldInstitute::whereNotNull('institute_id')->get();
            
            $errors = [];
            $migratedCount = 0;
            foreach ($institutes as $institute) {
                try {
                    $this->migrateInstitute($institute);
                    $migratedCount++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'institute_id' => $institute->id,
                        'error' => $e->getMessage()
                    ];
                    Log::error("Error migrating institute ID {$institute->id}: " . $e->getMessage());
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'total_processed' => $institutes->count(),
                'successfully_migrated' => $migratedCount,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Migration process failed: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => "Migration failed: " . $e->getMessage()
            ], 500);
        }
    }

    public function remains(Request $request)
    {
         ini_set('max_execution_time', 1500);

        // Optional parameters
        $limit = $request->get('limit', 100);
        $offset = $request->get('offset', 0);
        $cityId = $request->get('city_id');
        
        try {
            DB::beginTransaction();
            
            $institutes = OldInstitute::whereNull('institute_id')->get();
            
            
            $errors = [];
            $migratedCount = 0;
            foreach ($institutes as $institute) {
                try {
                    $this->migrateInstitute($institute);
                    $migratedCount++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'institute_id' => $institute->id,
                        'error' => $e->getMessage()
                    ];
                    Log::error("Error migrating institute ID {$institute->id}: " . $e->getMessage());
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'total_processed' => $institutes->count(),
                'successfully_migrated' => $migratedCount,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Migration process failed: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => "Migration failed: " . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Migrate a single institute to a service
     * 
     * @param OldInstitute $institute
     * @return Service
     */
    protected function migrateInstitute(OldInstitute $institute)
    {
        // Check if this institute was already migrated
        $existingService = Service::where('id', $institute->id)->first();
            
        if ($existingService) {
            return $existingService;
        }
        
        if ($institute->basic_image) {
            $media = $this->createMedia($institute->basic_image);
        }

        // Create the service
        $service = new Service();
        $service->id = $institute->id;
        $service->parent_id = $institute->institute_id;
        $service->name = $institute->name;
        $service->city_id = $institute->city_id;
        $service->brief_description = $institute->brief_description ?? '';
        $service->description = $institute->description ?? '';
        $service->lat = $institute->lat;
        $service->lon = $institute->lon;
        $service->website = $institute->website ?? '';
        $service->youtube = $institute->youtube ?? '';
        $service->facebook = $institute->facebook ?? '';
        $service->instagram = $institute->instagram ?? '';
        $service->telegram = $institute->telegram ?? '';
        $service->valid = $institute->valid ;
        $service->image_id = $media->id ?? null;
        $service->arrangement_order = $institute->arrange_order;
        $service->created_at = $institute->created_at;
        $service->updated_at = $institute->updated_at;
        $service->save();
        
        // Migrate categories
        $this->migrateCategories($institute, $service);
        
        // Migrate additional phone numbers
        $this->migratePhones($institute, $service);
        
        // Migrate ratings
        $this->migrateRatings($institute, $service);
        
        return $service;
    }
    
    /**
     * Migrate categories from old institute to new service
     * 
     * @param OldInstitute $institute
     * @param Service $service
     */
    protected function migrateCategories(OldInstitute $institute, Service $service)
    {
        if ($institute->category_id) {
            $category = Category::where('id', $institute->category_id)->first();
                            
            if ($category) {
                ServiceCategory::create([
                    'service_id' => $service->id,	
                    'category_id' => $category->id,
                ]);
            }
        }
        
        $instituteCategories = OldInstituteCategory::where('institute_id', $institute->id)->get();
        
        foreach ($instituteCategories as $instituteCategory) {
            $oldCategory = $instituteCategory->category;
            
            if (!$oldCategory) {
                continue;
            }
            
            $category = Category::where('id', $oldCategory->id)->first();
                
            if ($category && !$service->categories->contains($category->id)) {
                ServiceCategory::create([
                    'service_id' => $service->id,	
                    'category_id' => $category->id,
                ]);
            }
        }
    }
    
    /**
     * Migrate phone numbers
     * 
     * @param OldInstitute $institute
     * @param Service $service
     */
    protected function migratePhones(OldInstitute $institute, Service $service)
    {
        // The main phone is already in the service
        
        // Additional phones might be in different format or table
        // This is just a placeholder - adjust based on your actual data structure
        if ($institute->phones) {
            $phones = explode(',', $institute->phones);
            
            foreach ($phones as $phone) {
                $phone = trim($phone);
                
                if ($phone) {
                    ServicePhone::create([
                        'service_id' => $service->id,
                        'phone' => $phone
                    ]);
                }
            }
        }
    }
    
    /**
     * Migrate ratings
     * 
     * @param OldInstitute $institute
     * @param Service $service
     */
    protected function migrateRatings(OldInstitute $institute, Service $service)
    {
        $oldRatings = OldRate::where('institute_id', $institute->id)->get();
        
        foreach ($oldRatings as $oldRating) {
            if (!$oldRating->user_id) {
                continue;
            }
            
            // Check if rating already exists
            $existingRating = Rate::where('user_id', $oldRating->user_id)
                ->where('service_id', $service->id)
                ->first();
                
            if ($existingRating) {
                continue;
            }
            
            Rate::create([
                'user_id' => $oldRating->user_id,
                'service_id' => $service->id,
                'rate' => $oldRating->rate
            ]);
        }
    }
    
    /**
     * Get migration status
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMigrationStatus()
    {
        $totalInstitutes = OldInstitute::count();
        $totalServices = Service::count();
        
        return response()->json([
            'total_institutes' => $totalInstitutes,
            'total_services' => $totalServices,
            'estimated_progress' => $totalInstitutes > 0 ? round(($totalServices / $totalInstitutes) * 100, 2) : 0
        ]);
    }
    
    /**
     * Reset migration (for testing purposes)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetMigration()
    {
        // Only allow this in development environment
        if (app()->environment() !== 'local') {
            return response()->json([
                'message' => 'This action is only available in development environment'
            ], 403);
        }
        
        try {
            DB::beginTransaction();
            
            // Delete all services
            Service::truncate();
            
            // Delete related pivot tables
            DB::table('service_categories')->truncate();
            DB::table('service_images')->truncate();
            DB::table('service_phones')->truncate();
            DB::table('rates')->truncate();
            
            // Clear city mapping cache
            Cache::forget('city_id_mapping');
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Migration reset successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Reset failed: ' . $e->getMessage()
            ], 500);
        }
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
            $cities = City::all(['id']);
            $oldCities = OldCity::all(['id']);
            
            $existingIds = $cities->pluck('id')->toArray();
            $oldIds = $oldCities->pluck('id')->toArray();
            
            // Find which IDs exist in both tables
            $commonIds = array_intersect($oldIds, $existingIds);
            
            foreach ($commonIds as $id) {
                $mapping[$id] = $id;
            }
            
            // Method 2: For remaining IDs, try to match by name
            $unmappedOldCities = OldCity::whereNotIn('id', array_keys($mapping))->get(['id', 'name']);
            
            foreach ($unmappedOldCities as $oldCity) {
                $newCity = City::where('name', $oldCity->name)->first();
                
                if ($newCity) {
                    $mapping[$oldCity->id] = $newCity->id;
                }
            }
            
            return $mapping;
        });
        
        return $cityMapping[$oldCityId] ?? null;
    }
    
    /**
     * Map all cities and create missing ones
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function mapCities()
    {
        try {
            DB::beginTransaction();
            
            $oldCities = OldCity::all();
            $created = 0;
            $mapped = 0;
            $failed = 0;
            
            foreach ($oldCities as $oldCity) {
                // Check if city already exists with same name
                $existingCity = City::where('name', $oldCity->name)->first();
                
                if ($existingCity) {
                    $mapped++;
                    continue;
                }
                
                // Create new city
                try {
                    $city = new City();
                    $city->id = $oldCity->id; // Try to use same ID
                    $city->name = $oldCity->name;
                    // Map other fields as needed
                    $city->save();
                    
                    // Create city config if needed
                    if ($oldCity->config) {
                        $city->config()->create([
                            'firebase_topic' => $oldCity->config->firebase_topic ?? '',
                            'firebase_token' => $oldCity->config->firebase_token ?? ''
                        ]);
                    }
                    
                    $created++;
                } catch (\Exception $e) {
                    // If we couldn't use the same ID, create with auto-increment
                    try {
                        $city = new City();
                        $city->name = $oldCity->name;
                        // Map other fields as needed
                        $city->save();
                        
                        // Create city config if needed
                        if ($oldCity->config) {
                            $city->config()->create([
                                'firebase_topic' => $oldCity->config->firebase_topic ?? '',
                                'firebase_token' => $oldCity->config->firebase_token ?? ''
                            ]);
                        }
                        
                        $created++;
                    } catch (\Exception $e) {
                        $failed++;
                        Log::error("Failed to create city {$oldCity->name}: " . $e->getMessage());
                    }
                }
            }
            
            // Clear the city mapping cache
            Cache::forget('city_id_mapping');
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'created' => $created,
                'mapped' => $mapped,
                'failed' => $failed
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'City mapping failed: ' . $e->getMessage()
            ], 500);
        }
    }
}