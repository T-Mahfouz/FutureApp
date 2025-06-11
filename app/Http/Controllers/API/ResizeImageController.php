<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\File;
use Illuminate\Http\JsonResponse;

class ResizeImageController extends Controller
{

    private $sourceFolder;
    private $destinationFolder;
    private $resizedImagesFolder;
    private $pathPrefix = 'all_images/';
    
    // Mobile-optimized dimensions
    
    private $maxWidth = 360;
    private $maxHeight = 640;
    private $quality = 85;     // Good balance between quality and size

    public function __construct()
    {
        // Use direct paths since storage disks are not configured correctly
        $this->sourceFolder = storage_path('app/public/all_images');
        $this->destinationFolder = storage_path('app/public/extra_images');
        $this->resizedImagesFolder = storage_path('app/public/resized_images');
    }

    /**
     * Resize all images in the all_images folder for mobile optimization
     */
    /**
    * Resize a single image file
    */
    public function resizeSingleImage(Request $request): JsonResponse
    {
        $request->validate([
            'filename' => 'required|string',
            'max_width' => 'nullable|integer|min:100|max:4000',
            'max_height' => 'nullable|integer|min:100|max:4000',
            'quality' => 'nullable|integer|min:10|max:100',
        ]);

        try {
            $filename = $request->input('filename');
            $filePath = $this->sourceFolder . DIRECTORY_SEPARATOR . $filename;

            if (!File::exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => "File not found: {$filename}",
                ], 404);
            }

            if (!$this->isImageFile($filename)) {
                return response()->json([
                    'success' => false,
                    'message' => "File is not a valid image: {$filename}",
                ], 400);
            }

            $maxWidth = $request->input('max_width', $this->maxWidth);
            $maxHeight = $request->input('max_height', $this->maxHeight);
            $quality = $request->input('quality', $this->quality);

            $originalSize = File::size($filePath);

            // Create backup first
            if (!File::exists($this->resizedImagesFolder)) {
                File::makeDirectory($this->resizedImagesFolder, 0755, true);
            }
            $backupPath = $this->resizedImagesFolder . DIRECTORY_SEPARATOR . 'backup_' . $filename;
            File::copy($filePath, $backupPath);

            // Use the helper function to resize the image
            $result = resizeExistingImage(
                $filename,                  // filename
                $this->sourceFolder,        // source path
                null,                       // destination (null = overwrite original)
                $maxWidth,                  // max width
                $maxHeight,                 // max height
                100,                        // min dimension
                true,                       // keep original name
                $quality                    // quality
            );

            if ($result['success']) {
                $newSize = File::size($filePath);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Image resized successfully',
                    'data' => [
                        'filename' => $filename,
                        'original_size' => $this->formatBytes($originalSize),
                        'new_size' => $this->formatBytes($newSize),
                        'reduction' => $this->formatBytes($originalSize - $newSize),
                        'reduction_percent' => $originalSize > 0 ? round((($originalSize - $newSize) / $originalSize) * 100, 2) : 0,
                        'original_dimensions' => $result['original_dimensions'],
                        'new_dimensions' => $result['new_dimensions'],
                        'backup_created' => $backupPath,
                    ]
                ]);
            } else {
                // Restore from backup if resize failed
                File::copy($backupPath, $filePath);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to resize image: ' . $result['message'],
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error resizing image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Updated resizeAllImages method to use the helper function
     */
    public function resizeAllImages(Request $request): JsonResponse
    {
        ini_set('max_execution_time', 1500);
        ini_set('memory_limit', '512M');
        
        try {
            if (!File::exists($this->sourceFolder)) {
                return response()->json([
                    'success' => false,
                    'message' => "Source folder does not exist: {$this->sourceFolder}",
                ], 404);
            }

            // Get custom dimensions if provided
            $maxWidth = $request->input('max_width', $this->maxWidth);
            $maxHeight = $request->input('max_height', $this->maxHeight);
            $quality = $request->input('quality', $this->quality);

            // Validate inputs
            if ($maxWidth < 100 || $maxWidth > 4000 || $maxHeight < 100 || $maxHeight > 4000) {
                return response()->json([
                    'success' => false,
                    'message' => 'Width and height must be between 100 and 4000 pixels',
                ], 400);
            }

            if ($quality < 10 || $quality > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quality must be between 10 and 100',
                ], 400);
            }

            $files = File::files($this->sourceFolder);
            $resizedFiles = [];
            $skippedFiles = [];
            $errors = [];
            $totalOriginalSize = 0;
            $totalNewSize = 0;

            // Create backup folder
            $backupFolder = $this->sourceFolder . '_backup';
            if (!File::exists($backupFolder)) {
                File::makeDirectory($backupFolder, 0755, true);
            }

            foreach ($files as $file) {
                $filename = $file->getFilename();
                $filePath = $file->getPathname();
                
                // Check if it's an image file
                if (!$this->isImageFile($filename)) {
                    $skippedFiles[] = $filename . ' (not an image)';
                    continue;
                }

                try {
                    $originalSize = $file->getSize();
                    $totalOriginalSize += $originalSize;

                    // // Backup original file
                    // $backupPath = $backupFolder . DIRECTORY_SEPARATOR . $filename;
                    // File::copy($filePath, $backupPath);

                    // Use helper function to resize image
                    $result = resizeExistingImage(
                        $filename,
                        $this->sourceFolder,
                        $this->resizedImagesFolder, // overwrite original
                        $maxWidth,
                        $maxHeight,
                        100,
                        true,
                        $quality
                    );
                    
                    if ($result['success']) {
                        $newSize = File::size($filePath);
                        $totalNewSize += $newSize;
                        
                        $resizedFiles[] = [
                            'filename' => $filename,
                            'original_size' => $this->formatBytes($originalSize),
                            'new_size' => $this->formatBytes($newSize),
                            'reduction' => $this->formatBytes($originalSize - $newSize),
                            'reduction_percent' => $originalSize > 0 ? round((($originalSize - $newSize) / $originalSize) * 100, 2) : 0,
                            'original_dimensions' => $result['original_dimensions'],
                            'new_dimensions' => $result['new_dimensions'],
                        ];
                    } else {
                        $errors[] = "Failed to resize {$filename}: " . $result['message'];
                        // Restore from backup if resize failed
                        // File::copy($backupPath, $filePath);
                    }

                } catch (\Exception $e) {
                    $errors[] = "Error processing {$filename}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Image resizing completed',
                'data' => [
                    'resized_files' => $resizedFiles,
                    'skipped_files' => $skippedFiles,
                    'errors' => $errors,
                    'summary' => [
                        'total_processed' => count($resizedFiles),
                        'total_skipped' => count($skippedFiles),
                        'total_errors' => count($errors),
                        'original_total_size' => $this->formatBytes($totalOriginalSize),
                        'new_total_size' => $this->formatBytes($totalNewSize),
                        'total_saved' => $this->formatBytes($totalOriginalSize - $totalNewSize),
                        'total_reduction_percent' => $totalOriginalSize > 0 ? 
                            round((($totalOriginalSize - $totalNewSize) / $totalOriginalSize) * 100, 2) : 0,
                    ],
                    'settings' => [
                        'max_width' => $maxWidth,
                        'max_height' => $maxHeight,
                        'quality' => $quality,
                    ],
                    'backup_folder' => $backupFolder,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error resizing images: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get image analysis (dimensions, sizes, etc.)
     */
    public function analyzeImages(): JsonResponse
    {
        try {
            if (!File::exists($this->sourceFolder)) {
                return response()->json([
                    'success' => false,
                    'message' => "Source folder does not exist: {$this->sourceFolder}",
                ], 404);
            }

            $files = File::files($this->sourceFolder);
            $imageFiles = [];
            $totalSize = 0;
            $totalImages = 0;

            foreach ($files as $file) {
                $filename = $file->getFilename();
                
                if (!$this->isImageFile($filename)) {
                    continue;
                }

                try {
                    $filePath = $file->getPathname();
                    $fileSize = $file->getSize();
                    $totalSize += $fileSize;
                    $totalImages++;

                    // Get image dimensions
                    $imageInfo = getimagesize($filePath);
                    $width = $imageInfo[0] ?? 0;
                    $height = $imageInfo[1] ?? 0;
                    
                    // Check if image needs resizing
                    $needsResize = $width > $this->maxWidth || $height > $this->maxHeight;
                    
                    // Calculate potential savings
                    $potentialSavings = 0;
                    if ($needsResize) {
                        // Estimate 30-50% reduction for oversized images
                        $potentialSavings = $fileSize * 0.4;
                    }

                    $imageFiles[] = [
                        'filename' => $filename,
                        'size' => $this->formatBytes($fileSize),
                        'size_bytes' => $fileSize,
                        'dimensions' => "{$width}x{$height}",
                        'width' => $width,
                        'height' => $height,
                        'needs_resize' => $needsResize,
                        'potential_savings' => $this->formatBytes($potentialSavings),
                        'potential_savings_bytes' => $potentialSavings,
                    ];

                } catch (\Exception $e) {
                    // Skip files that can't be processed
                    continue;
                }
            }

            // Sort by file size (largest first)
            usort($imageFiles, function($a, $b) {
                return $b['size_bytes'] - $a['size_bytes'];
            });

            $oversizedImages = array_filter($imageFiles, function($img) {
                return $img['needs_resize'];
            });

            $totalPotentialSavings = array_sum(array_column($oversizedImages, 'potential_savings_bytes'));

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total_images' => $totalImages,
                        'total_size' => $this->formatBytes($totalSize),
                        'average_size' => $totalImages > 0 ? $this->formatBytes($totalSize / $totalImages) : '0 B',
                        'oversized_images' => count($oversizedImages),
                        'potential_savings' => $this->formatBytes($totalPotentialSavings),
                        'potential_savings_percent' => $totalSize > 0 ? 
                            round(($totalPotentialSavings / $totalSize) * 100, 2) : 0,
                    ],
                    'recommended_settings' => [
                        'max_width' => $this->maxWidth,
                        'max_height' => $this->maxHeight,
                        'quality' => $this->quality,
                    ],
                    'images' => $imageFiles,
                    'oversized_images' => array_values($oversizedImages),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error analyzing images: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore images from backup
     */
    public function restoreFromBackup(Request $request): JsonResponse
    {
        try {
            $backupFolder = $this->sourceFolder . '_backup';
            
            if (!File::exists($backupFolder)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup folder does not exist',
                ], 404);
            }

            $filesToRestore = $request->input('files', []);
            $backupFiles = File::files($backupFolder);
            $restoredFiles = [];
            $errors = [];

            foreach ($backupFiles as $backupFile) {
                $filename = $backupFile->getFilename();
                
                // If specific files requested, only restore those
                if (!empty($filesToRestore) && !in_array($filename, $filesToRestore)) {
                    continue;
                }

                $backupPath = $backupFile->getPathname();
                $originalPath = $this->sourceFolder . DIRECTORY_SEPARATOR . $filename;

                try {
                    if (File::copy($backupPath, $originalPath)) {
                        $restoredFiles[] = $filename;
                    } else {
                        $errors[] = "Failed to restore: {$filename}";
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error restoring {$filename}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Images restored from backup',
                'data' => [
                    'restored_files' => $restoredFiles,
                    'total_restored' => count($restoredFiles),
                    'errors' => $errors,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error restoring from backup: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete backup folder
     */
    public function deleteBackup(): JsonResponse
    {
        try {
            $backupFolder = $this->sourceFolder . '_backup';
            
            if (!File::exists($backupFolder)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup folder does not exist',
                ], 404);
            }

            $backupFiles = File::files($backupFolder);
            $totalSize = 0;
            foreach ($backupFiles as $file) {
                $totalSize += $file->getSize();
            }

            if (File::deleteDirectory($backupFolder)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Backup folder deleted successfully',
                    'data' => [
                        'deleted_files' => count($backupFiles),
                        'space_freed' => $this->formatBytes($totalSize),
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete backup folder',
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting backup: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resize image using Intervention Image v3
     */
    private function resizeImage($filePath, $maxWidth, $maxHeight, $quality): array
    {
        try {
            // Get original dimensions
            $imageInfo = getimagesize($filePath);
            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];

            // Check if resize is needed
            if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
                return [
                    'success' => true,
                    'message' => 'No resize needed',
                    'original_dimensions' => "{$originalWidth}x{$originalHeight}",
                    'new_dimensions' => "{$originalWidth}x{$originalHeight}",
                ];
            }

            // Create ImageManager instance with GD driver
            $manager = new ImageManager(new Driver());
            
            // Read the image
            $image = $manager->read($filePath);
            
            // Resize image maintaining aspect ratio
            $image->scaleDown(width: $maxWidth, height: $maxHeight);

            // Save with quality (encode as JPEG for quality control, or maintain original format)
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            
            if (in_array($extension, ['jpg', 'jpeg'])) {
                $image->toJpeg($quality)->save($filePath);
            } elseif ($extension === 'png') {
                // PNG uses compression level 0-9, convert quality (0-100) to compression (0-9)
                $compression = 9 - intval(($quality / 100) * 9);
                $image->toPng()->save($filePath);
            } elseif ($extension === 'webp') {
                $image->toWebp($quality)->save($filePath);
            } else {
                // For other formats, save as original format
                $image->save($filePath);
            }

            // Get new dimensions
            $newImageInfo = getimagesize($filePath);
            $newWidth = $newImageInfo[0];
            $newHeight = $newImageInfo[1];

            return [
                'success' => true,
                'original_dimensions' => "{$originalWidth}x{$originalHeight}",
                'new_dimensions' => "{$newWidth}x{$newHeight}",
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if file is an image
     */
    private function isImageFile($filename): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, $imageExtensions);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
