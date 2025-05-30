<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\OldCity;
use App\Models\City;
use App\Models\Media;

class CityMigrationController extends Controller
{
    /**
     * Migrate all data from old_cities to cities
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function migrateAllCities()
    {
        ini_set('max_execution_time', 600);

        try {
            // Start a database transaction
            DB::beginTransaction();
            
            $oldCities = OldCity::with([
                'config',
                'categories',
                'institutes',
                'news',
                'news.images',
                'contacts',
                'contactus',
                'admins'
            ])->get();
            
            $migrationResults = [
                'total_cities' => count($oldCities),
                'migrated_cities' => 0,
                'failed_cities' => 0,
                'city_details' => []
            ];
            
            foreach ($oldCities as $oldCity) {
                try {
                    $result = $this->migrateCity($oldCity);
                    
                    if ($result['success']) {
                        $migrationResults['migrated_cities']++;
                    } else {
                        $migrationResults['failed_cities']++;
                    }
                    
                    $migrationResults['city_details'][] = $result;
                } catch (\Exception $e) {
                    $migrationResults['failed_cities']++;
                    $migrationResults['city_details'][] = [
                        'success' => false,
                        'city_id' => $oldCity->id ?? 'unknown',
                        'city_name' => $oldCity->name ?? 'unknown',
                        'message' => 'Migration failed: ' . $e->getMessage()
                    ];
                    
                    Log::error('City migration failed in loop', [
                        'city_id' => $oldCity->id ?? 'unknown',
                        'city_name' => $oldCity->name ?? 'unknown',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            
            // Commit the transaction if everything went well
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Migration completed',
                'results' => $migrationResults
            ]);
            
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();
            
            Log::error('Migration failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Migration failed: ' . $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'code' => $e->getCode()
                ]
            ], 500);
        }
    }
    
    /**
     * Migrate a single city by ID
     *
     * @param int $cityId
     * @return \Illuminate\Http\JsonResponse
     */
    public function migrateSingleCity($cityId)
    {
        try {
            // Validate city ID
            if (!is_numeric($cityId) || $cityId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid city ID provided'
                ], 400);
            }

            // Start a database transaction
            DB::beginTransaction();
            
            $oldCity = OldCity::with([
                'config',
                'categories',
                'institutes',
                'news',
                'news.images',
                'contacts',
                'contactus',
                'admins'
            ])->findOrFail($cityId);
            
            $result = $this->migrateCity($oldCity);
            
            // Commit the transaction if everything went well
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $result['success'] ? 'City migrated successfully' : 'City migration failed',
                'result' => $result
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            
            Log::error('City not found for migration', [
                'city_id' => $cityId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => "City with ID {$cityId} not found"
            ], 404);
            
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();
            
            Log::error('Single city migration failed', [
                'city_id' => $cityId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Migration failed: ' . $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'code' => $e->getCode()
                ]
            ], 500);
        }
    }
    
    /**
     * Migrate data for a specific city
     *
     * @param OldCity $oldCity
     * @return array
     */
    private function migrateCity(OldCity $oldCity)
    {
        try {
            // Validate old city data
            if (!$oldCity || !$oldCity->name) {
                throw new \InvalidArgumentException('Invalid city data provided');
            }

            // Check if the city already exists
            $existingCity = City::where('name', $oldCity->name)->first();
            
            if ($existingCity) {
                return [
                    'success' => false,
                    'city_id' => $oldCity->id,
                    'city_name' => $oldCity->name,
                    'message' => "City '{$oldCity->name}' already exists"
                ];
            }
            
            // Create the new city
            $newCity = new City();
            $newCity->id = $oldCity->id;
            $newCity->name = $oldCity->name;
            
            // Handle city image if exists
            if ($oldCity->image_path) {
                try {
                    $media = $this->createMedia($oldCity->image_path);
                    if ($media) {
                        $newCity->image_id = $media->id;
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to create media for city image', [
                        'city_id' => $oldCity->id,
                        'image_path' => $oldCity->image_path,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $newCity->save();
            
            // Initialize stats
            $stats = [
                'categories' => 0,
                'services' => 0,
                'news' => 0,
                'contacts' => 0,
                'admins' => 0
            ];
            
            // Migrate city config
            try {
                $this->migrateCityConfig($oldCity, $newCity);
            } catch (\Exception $e) {
                Log::warning('Failed to migrate city config', [
                    'city_id' => $oldCity->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Migrate categories
            try {
                $categoryMap = $this->migrateCategories($oldCity, $newCity);
                $stats['categories'] = count($categoryMap);
            } catch (\Exception $e) {
                Log::error('Failed to migrate categories', [
                    'city_id' => $oldCity->id,
                    'error' => $e->getMessage()
                ]);
                $categoryMap = [];
            }
            
            // Migrate institutes to services
            try {
                $serviceMap = $this->migrateInstitutesToServices($oldCity, $newCity, $categoryMap);
                $stats['services'] = count($serviceMap);
            } catch (\Exception $e) {
                Log::error('Failed to migrate institutes to services', [
                    'city_id' => $oldCity->id,
                    'error' => $e->getMessage()
                ]);
                $serviceMap = [];
            }
            
            // Migrate news
            try {
                $newsMap = $this->migrateNews($oldCity, $newCity);
                $stats['news'] = count($newsMap);
            } catch (\Exception $e) {
                Log::error('Failed to migrate news', [
                    'city_id' => $oldCity->id,
                    'error' => $e->getMessage()
                ]);
                $newsMap = [];
            }
            
            // Migrate contact us
            try {
                $contactCount = $this->migrateContactUs($oldCity, $newCity);
                $stats['contacts'] = $contactCount;
            } catch (\Exception $e) {
                Log::error('Failed to migrate contact us', [
                    'city_id' => $oldCity->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Migrate admins
            try {
                $adminCount = $this->migrateAdmins($oldCity, $newCity);
                $stats['admins'] = $adminCount;
            } catch (\Exception $e) {
                Log::error('Failed to migrate admins', [
                    'city_id' => $oldCity->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            return [
                'success' => true,
                'city_id' => [
                    'old' => $oldCity->id,
                    'new' => $newCity->id
                ],
                'name' => $newCity->name,
                'stats' => $stats
            ];
            
        } catch (\Exception $e) {
            Log::error('City migration failed: ' . $e->getMessage(), [
                'city_id' => $oldCity->id ?? 'unknown',
                'city_name' => $oldCity->name ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'city_id' => $oldCity->id ?? 'unknown',
                'city_name' => $oldCity->name ?? 'unknown',
                'message' => 'Migration failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Migrate city configuration
     *
     * @param OldCity $oldCity
     * @param City $newCity
     * @return void
     */
    private function migrateCityConfig(OldCity $oldCity, City $newCity)
    {
        try {
            if ($oldCity->config) {
                $newCity->config()->create([
                    'firebase_topic' => $oldCity->config->firebase_topic ?? null,
                    'firebase_token' => $oldCity->config->firebase_token ?? null
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to migrate city config', [
                'city_id' => $oldCity->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Migrate categories and return mapping of old to new IDs
     *
     * @param OldCity $oldCity
     * @param City $newCity
     * @return array
     */
    private function migrateCategories(OldCity $oldCity, City $newCity)
    {
        $categoryMap = [];
        
        try {
            // First, migrate categories without parent (root categories)
            foreach ($oldCity->categories as $oldCategory) {
                if (!$oldCategory->parent_id) {
                    try {
                        $newCategory = $this->createCategory($oldCategory, $newCity);
                        $categoryMap[$oldCategory->id] = $newCategory->id;
                    } catch (\Exception $e) {
                        Log::warning('Failed to migrate root category', [
                            'category_id' => $oldCategory->id,
                            'category_name' => $oldCategory->name ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            
            // Then, migrate categories with parent
            foreach ($oldCity->categories as $oldCategory) {
                if ($oldCategory->parent_id && isset($categoryMap[$oldCategory->parent_id])) {
                    try {
                        $newCategory = $this->createCategory($oldCategory, $newCity, $categoryMap[$oldCategory->parent_id]);
                        $categoryMap[$oldCategory->id] = $newCategory->id;
                    } catch (\Exception $e) {
                        Log::warning('Failed to migrate child category', [
                            'category_id' => $oldCategory->id,
                            'category_name' => $oldCategory->name ?? 'unknown',
                            'parent_id' => $oldCategory->parent_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to migrate categories', [
                'city_id' => $oldCity->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
        
        return $categoryMap;
    }
    
    /**
     * Create a new category
     *
     * @param \App\Models\OldCategory $oldCategory
     * @param City $newCity
     * @param int|null $parentId
     * @return \App\Models\Category
     */
    private function createCategory($oldCategory, $newCity, $parentId = null)
    {
        try {
            // Handle category image if exists
            $imageId = null;
            if ($oldCategory->image_path) {
                try {
                    $media = $this->createMedia($oldCategory->image_path);
                    if ($media) {
                        $imageId = $media->id;
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to create media for category image', [
                        'category_id' => $oldCategory->id,
                        'image_path' => $oldCategory->image_path,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            return $newCity->categories()->create([
                'name' => $oldCategory->name,
                'parent_id' => $parentId,
                'image_id' => $imageId
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to create category', [
                'category_id' => $oldCategory->id,
                'category_name' => $oldCategory->name ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Migrate institutes to services and return mapping of old to new IDs
     *
     * @param OldCity $oldCity
     * @param City $newCity
     * @param array $categoryMap
     * @return array
     */
    private function migrateInstitutesToServices(OldCity $oldCity, City $newCity, array $categoryMap)
    {
        $serviceMap = [];
        
        try {
            foreach ($oldCity->institutes as $oldInstitute) {
                try {
                    // Create new service
                    $imageId = null;
                    if ($oldInstitute->image_path) {
                        try {
                            $media = $this->createMedia($oldInstitute->image_path);
                            if ($media) {
                                $imageId = $media->id;
                            }
                        } catch (\Exception $e) {
                            Log::warning('Failed to create media for institute image', [
                                'institute_id' => $oldInstitute->id,
                                'image_path' => $oldInstitute->image_path,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    
                    $newService = $newCity->services()->create([
                        'name' => $oldInstitute->name,
                        'brief_description' => $oldInstitute->brief ?? null,
                        'description' => $oldInstitute->description ?? null,
                        'lat' => $oldInstitute->lat,
                        'lon' => $oldInstitute->lon,
                        'website' => $oldInstitute->website ?? null,
                        'youtube' => $oldInstitute->youtube ?? null,
                        'facebook' => $oldInstitute->facebook ?? null,
                        'instagram' => $oldInstitute->instagram ?? null,
                        'telegram' => $oldInstitute->telegram ?? null,
                        'video_link' => $oldInstitute->video_link ?? null,
                        'valid' => $oldInstitute->valid ?? 1,
                        'arrangement_order' => $oldInstitute->order ?? 1,
                        'image_id' => $imageId
                    ]);
                    
                    // Migrate service phones
                    try {
                        if ($oldInstitute->phone) {
                            $newService->phones()->create(['phone' => $oldInstitute->phone]);
                        }

                        if ($oldInstitute->phone2) {
                            $newService->phones()->create(['phone' => $oldInstitute->phone2]);
                        }
                        
                        if ($oldInstitute->phone3) {
                            $newService->phones()->create(['phone' => $oldInstitute->phone3]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to migrate service phones', [
                            'institute_id' => $oldInstitute->id,
                            'service_id' => $newService->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                    
                    // Migrate service categories
                    try {
                        if ($oldInstitute->category_id && isset($categoryMap[$oldInstitute->category_id])) {
                            $newService->categories()->attach($categoryMap[$oldInstitute->category_id]);
                        }
                        
                        // Migrate additional categories
                        foreach ($oldInstitute->categories as $oldCategory) {
                            if (isset($categoryMap[$oldCategory->id])) {
                                $newService->categories()->syncWithoutDetaching([$categoryMap[$oldCategory->id]]);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to migrate service categories', [
                            'institute_id' => $oldInstitute->id,
                            'service_id' => $newService->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                    
                    // Migrate service images
                    try {
                        foreach ($oldInstitute->images as $oldImage) {
                            if ($oldImage->path) {
                                try {
                                    $media = $this->createMedia($oldImage->path);
                                    if ($media) {
                                        $newService->images()->attach($media->id);
                                    }
                                } catch (\Exception $e) {
                                    Log::warning('Failed to create media for service image', [
                                        'institute_id' => $oldInstitute->id,
                                        'image_path' => $oldImage->path,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to migrate service images', [
                            'institute_id' => $oldInstitute->id,
                            'service_id' => $newService->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                    
                    // Migrate service rates
                    try {
                        foreach ($oldInstitute->rates as $oldRate) {
                            if ($oldRate->user_id) {
                                $newService->rates()->create([
                                    'user_id' => $oldRate->user_id, // Assuming user IDs remain the same
                                    'rate' => $oldRate->rate
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to migrate service rates', [
                            'institute_id' => $oldInstitute->id,
                            'service_id' => $newService->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                    
                    $serviceMap[$oldInstitute->id] = $newService->id;
                    
                } catch (\Exception $e) {
                    Log::error('Failed to migrate institute to service', [
                        'institute_id' => $oldInstitute->id,
                        'institute_name' => $oldInstitute->name ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to migrate institutes to services', [
                'city_id' => $oldCity->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
        
        return $serviceMap;
    }
    
    /**
     * Migrate news and return mapping of old to new IDs
     *
     * @param OldCity $oldCity
     * @param City $newCity
     * @return array
     */
    private function migrateNews(OldCity $oldCity, City $newCity)
    {
        $newsMap = [];
        
        try {
            foreach ($oldCity->news as $oldNews) {
                try {
                    // Handle main news image
                    $imageId = null;
                    if ($oldNews->image_path) {
                        try {
                            $media = $this->createMedia($oldNews->image_path);
                            if ($media) {
                                $imageId = $media->id;
                            }
                        } catch (\Exception $e) {
                            Log::warning('Failed to create media for news image', [
                                'news_id' => $oldNews->id,
                                'image_path' => $oldNews->image_path,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    
                    $newNews = new News();
                    $newNews->id = $oldNews->id;
                    $newNews->name = $oldNews->name;
                    $newNews->description = $oldNews->description;
                    $newNews->city_id = $newCity->id; // Use new city ID instead of old city_id
                    $newNews->image_id = $imageId;
                    
                    $newNews->save();
                    
                    // Migrate news images
                    try {
                        foreach ($oldNews->images as $oldImage) {
                            if ($oldImage->path) {
                                try {
                                    $media = $this->createMedia($oldImage->path);
                                    if ($media) {
                                        $newNews->images()->attach($media->id);
                                    }
                                } catch (\Exception $e) {
                                    Log::warning('Failed to create media for news additional image', [
                                        'news_id' => $oldNews->id,
                                        'image_path' => $oldImage->path,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to migrate news images', [
                            'news_id' => $oldNews->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                    
                    $newsMap[$oldNews->id] = $newNews->id;
                    
                } catch (\Exception $e) {
                    Log::error('Failed to migrate news item', [
                        'news_id' => $oldNews->id,
                        'news_name' => $oldNews->name ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to migrate news', [
                'city_id' => $oldCity->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
        
        return $newsMap;
    }
    
    /**
     * Migrate contact us data
     *
     * @param OldCity $oldCity
     * @param City $newCity
     * @return int
     */
    private function migrateContactUs(OldCity $oldCity, City $newCity)
    {
        $contactCount = 0;
        
        try {
            foreach ($oldCity->contactus as $oldContact) {
                try {
                    $newCity->contactUs()->create([
                        'name' => $oldContact->name,
                        'phone' => $oldContact->phone,
                        'message' => $oldContact->message
                    ]);
                    $contactCount++;
                } catch (\Exception $e) {
                    Log::warning('Failed to migrate contact us item', [
                        'contact_id' => $oldContact->id ?? 'unknown',
                        'contact_name' => $oldContact->name ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to migrate contact us data', [
                'city_id' => $oldCity->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
        
        return $contactCount;
    }
    
    /**
     * Migrate admin associations
     *
     * @param OldCity $oldCity
     * @param City $newCity
     * @return int
     */
    private function migrateAdmins(OldCity $oldCity, City $newCity)
    {
        try {
            $adminIds = $oldCity->admins->pluck('id')->toArray();
            if (!empty($adminIds)) {
                $newCity->admins()->attach($adminIds);
            }
            return count($adminIds);
        } catch (\Exception $e) {
            Log::error('Failed to migrate admins', [
                'city_id' => $oldCity->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    
    /**
     * Get migration status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMigrationStatus()
    {
        try {
            $oldCitiesCount = OldCity::count();
            $newCitiesCount = City::count();
            
            return response()->json([
                'success' => true,
                'status' => [
                    'old_cities_count' => $oldCitiesCount,
                    'new_cities_count' => $newCitiesCount,
                    'progress_percentage' => $oldCitiesCount > 0 ? round(($newCitiesCount / $oldCitiesCount) * 100, 2) : 0
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get migration status', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get migration status: ' . $e->getMessage()
            ], 500);
        }
    }
}