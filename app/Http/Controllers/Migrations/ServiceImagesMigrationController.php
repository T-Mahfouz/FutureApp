<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\OldInstitute;
use App\Models\Service;
use App\Models\Category;
use App\Models\Media;
use App\Models\ServiceImage;
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

class ServiceImagesMigrationController extends Controller
{
    /**
     * Transfer data from old institutes to new services
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function migrateServiceImages(Request $request)
    {
        $errors = [];
        $successCount = 0;
        $errorsCount = 0;

        $success = true;
        $code = 200;
        $message = "Migration succeed";

        try {
            DB::beginTransaction();
            
            $oldInstitutes = OldInstitute::with(['images'])->get();
            
            foreach ($oldInstitutes as $institute) {
                $newService = Service::find($institute->id);
                if (!$newService) {
                    $errors[] = "institute: {$institute->id}, not found!";
                    continue;
                }
                
                if (!empty($institute->images)) {
                    // return $institute->images;
                    foreach ($institute->images as $oldImage) {
                        if ($oldImage->image) {
                            try {
                                $media = $this->createMedia($oldImage->image);
                                if ($media) {
                                    ServiceImage::create([
                                        'service_id' => $newService->id, 
                                        'image_id' => $media->id
                                    ]);
                                    
                                    $successCount++;
                                } 
                            } catch (\Exception $e) {
                                $errorsCount++;
                                $errors[] = "Failed to create media for service image for service: {$institute->id} => {$oldImage->path} \n error: {$e->getMessage()}";
                            }
                        }

                    }
                }
            }
            
            DB::commit();

        } catch (\Exception $e) {
            // dd('not good');
            DB::rollBack();
            
            Log::error("Migration process failed: " . $e->getMessage());
            
            $success = false;
            $message = "Migration failed: " . $e->getMessage();
            $code = 500;
        }

        
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => [
                'successCount' => $successCount,
                'errorsCount' => $errorsCount,
                'errors' => $errors,
            ]
        ], $code);
    }
    
    
    
}
