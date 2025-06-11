<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Support\Facades\Log;

abstract class Controller
{

    public $storagePath;

    public function __construct()
    {
        $this->storagePath = storage_path('app/public');
    }
    /**
     * Create media record (placeholder method - implement based on your Media model)
     *
     * @param string $imagePath
     * @return Media|null
     */
    public function createMedia($imagePath)
    {
        try {
            if (empty($imagePath)) {
                return null;
            }
            
            $imagePath = 'all_images/'.$imagePath;
            
            // Check if media already exists
            $existingMedia = Media::where('path', $imagePath)->first();
            if ($existingMedia) {
                return $existingMedia;
            }
            
            // Create new media record
            return Media::create([
                'path' => $imagePath,
                'type' => 'image', // Assuming it's always an image, adjust as needed
                'size' => null, // Add file size if available
                'mime_type' => null // Add mime type if available
            ]);
            
        } catch (\Exception $e) {
            log::error('Failed to create media', [
                'image_path' => $imagePath,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
