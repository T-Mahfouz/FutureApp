<?php

namespace App\Http\Controllers\Migrations;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\OldCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryMigrationController extends Controller
{
    public function migrateParents()
    {
        ini_set('max_execution_time', 600);

        $errors = [];
        $successCount = 0;
        $errorsCount = 0;

        try {
            DB::beginTransaction();
            
            $oldItems = OldCategory::whereNull('parent_id')->get();
            
            foreach ($oldItems as $item) {
                $category = Category::find($item->id);
                if ($category) {
                    $errors[] = "item: {$item->id}, is found!";
                    continue;
                }
                
                try {
                    
                    if ($item->image) {
                        $media = $this->createMedia($item->image);
                    }
                    Category::create([
                        'id' => $item->id,
                        'city_id' => $item->city_id,
                        'parent_id' => $item->parent_id,
                        'name' => $item->name,
                        'description' => $item->description,
                        'active' => $item->active,
                        'image_id' => $media->id ?? null,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $errorsCount++;
                    $errors[] = "Failed to create Category: {$item->id} \n error: {$e->getMessage()}";

                    continue;
                }
            }
            
            DB::commit();

        } catch (\Exception $e) {
            
            DB::rollBack();
            
            Log::error("Migration process failed: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => "Migration failed: " . $e->getMessage(),
                'data' => [
                    'successCount' => $successCount,
                    'errorsCount' => $errorsCount,
                    'errors' => $errors,
                ]
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Migration succeed",
            'data' => [
                'successCount' => $successCount,
                'errorsCount' => $errorsCount,
                'errors' => $errors,
            ]
        ], 200);
    }
    

    public function migrateChildren()
    {
        ini_set('max_execution_time', 600);

        $errors = [];
        $successCount = 0;
        $errorsCount = 0;

        try {
            DB::beginTransaction();
            
            $oldItems = OldCategory::whereNOTNull('parent_id')->get();
            
            foreach ($oldItems as $item) {
                $category = Category::find($item->id);
                if ($category) {
                    $errors[] = "item: {$item->id}, is found!";
                    continue;
                }
                
                try {
                    
                    if ($item->image) {
                        $media = $this->createMedia($item->image);
                    }
                    Category::create([
                        'id' => $item->id,
                        'city_id' => $item->city_id,
                        'parent_id' => $item->parent_id,
                        'name' => $item->name,
                        'description' => $item->description,
                        'active' => $item->active,
                        'image_id' => $media->id ?? null,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $errorsCount++;
                    $errors[] = "Failed to create Category: {$item->id} \n error: {$e->getMessage()}";

                    continue;
                }
            }
            
            DB::commit();

        } catch (\Exception $e) {
            
            DB::rollBack();
            
            Log::error("Migration process failed: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => "Migration failed: " . $e->getMessage(),
                'data' => [
                    'successCount' => $successCount,
                    'errorsCount' => $errorsCount,
                    'errors' => $errors,
                ]
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Migration succeed",
            'data' => [
                'successCount' => $successCount,
                'errorsCount' => $errorsCount,
                'errors' => $errors,
            ]
        ], 200);
    }
}
