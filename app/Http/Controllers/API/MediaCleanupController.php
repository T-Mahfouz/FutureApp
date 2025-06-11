<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Http\JsonResponse;


class MediaCleanupController extends Controller
{
    private $sourceFolder;
    private $destinationFolder;
    private $pathPrefix = 'all_images/';
       // Good balance between quality and size

    public function __construct()
    {
        // Use direct paths since storage disks are not configured correctly
        $this->sourceFolder = storage_path('app/public/all_images');
        $this->destinationFolder = storage_path('app/public/extra_images');
    }

    /**
     * Debug storage paths
     */
    public function debugStoragePaths(): JsonResponse
    {
        return response()->json([
            'source_folder' => $this->sourceFolder,
            'destination_folder' => $this->destinationFolder,
            'source_exists' => File::exists($this->sourceFolder),
            'destination_exists' => File::exists($this->destinationFolder),
            'source_is_directory' => File::isDirectory($this->sourceFolder),
            'source_files' => File::exists($this->sourceFolder) ? File::files($this->sourceFolder) : 'Folder not found',
            'storage_app_public' => storage_path('app/public'),
            'available_folders_in_public' => File::exists(storage_path('app/public')) ? 
                File::directories(storage_path('app/public')) : 'public folder not found',
        ]);
    }

    /**
     * Get all paths from database and filesystem
     */
    public function getAllPaths(): JsonResponse
    {
        try {
            // Get all paths from database
            $databasePaths = Media::pluck('path')->map(function ($path) {
                // Remove the prefix if it exists to get just the filename
                return str_replace($this->pathPrefix, '', $path);
            })->toArray();

            // Get all files from the filesystem
            $filesystemPaths = [];
            
            if (!File::exists($this->sourceFolder)) {
                return response()->json([
                    'success' => false,
                    'message' => "Folder does not exist: {$this->sourceFolder}",
                    'debug_info' => [
                        'source_folder' => $this->sourceFolder,
                        'storage_app_public_exists' => File::exists(storage_path('app/public')),
                        'available_in_public' => File::exists(storage_path('app/public')) ? 
                            File::directories(storage_path('app/public')) : 'public folder not found',
                    ]
                ], 404);
            }

            $files = File::files($this->sourceFolder);
            foreach ($files as $file) {
                $filename = $file->getFilename();
                $filesystemPaths[] = $filename;
            }

            // Find unreferenced files
            $unreferencedFiles = array_diff($filesystemPaths, $databasePaths);

            return response()->json([
                'success' => true,
                'data' => [
                    'database_paths' => $databasePaths,
                    'filesystem_paths' => $filesystemPaths,
                    'unreferenced_files' => array_values($unreferencedFiles),
                    'total_db_records' => count($databasePaths),
                    'total_filesystem_files' => count($filesystemPaths),
                    'total_unreferenced' => count($unreferencedFiles),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting paths: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Move unreferenced images to extra_images folder
     */
    public function moveUnreferencedImages(): JsonResponse
    {
        try {
            // Check if source folder exists
            if (!File::exists($this->sourceFolder)) {
                return response()->json([
                    'success' => false,
                    'message' => "Source folder does not exist: {$this->sourceFolder}",
                ], 404);
            }

            // Create destination folder if it doesn't exist
            if (!File::exists($this->destinationFolder)) {
                File::makeDirectory($this->destinationFolder, 0755, true);
            }

            // Get database paths (without prefix)
            $databasePaths = Media::pluck('path')->map(function ($path) {
                return str_replace($this->pathPrefix, '', $path);
            })->toArray();

            // Get all files from filesystem
            $files = File::files($this->sourceFolder);
            $movedFiles = [];
            $errors = [];

            foreach ($files as $file) {
                $filename = $file->getFilename();
                
                // Check if this file is NOT referenced in database
                if (!in_array($filename, $databasePaths)) {
                    $sourcePath = $file->getPathname();
                    $destinationPath = $this->destinationFolder . DIRECTORY_SEPARATOR . $filename;
                    
                    try {
                        // Move the file
                        if (File::move($sourcePath, $destinationPath)) {
                            $movedFiles[] = $filename;
                        } else {
                            $errors[] = "Failed to move: {$filename}";
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Error moving {$filename}: " . $e->getMessage();
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Unreferenced images moved successfully',
                'data' => [
                    'moved_files' => $movedFiles,
                    'total_moved' => count($movedFiles),
                    'errors' => $errors,
                    'destination_folder' => $this->destinationFolder,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error moving images: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get analysis of media files
     */
    public function analyzeMedia(): JsonResponse
    {
        try {
            if (!File::exists($this->sourceFolder)) {
                return response()->json([
                    'success' => false,
                    'message' => "Folder does not exist: {$this->sourceFolder}",
                ], 404);
            }

            // Get database info
            $databasePaths = Media::pluck('path')->map(function ($path) {
                return str_replace($this->pathPrefix, '', $path);
            })->toArray();

            // Get filesystem info
            $files = File::files($this->sourceFolder);
            $filesystemPaths = [];
            $totalSize = 0;

            foreach ($files as $file) {
                $filename = $file->getFilename();
                $filesystemPaths[] = $filename;
                $totalSize += $file->getSize();
            }

            // Find differences
            $unreferencedFiles = array_diff($filesystemPaths, $databasePaths);
            $missingFiles = array_diff($databasePaths, $filesystemPaths);

            // Calculate size of unreferenced files
            $unreferencedSize = 0;
            foreach ($unreferencedFiles as $filename) {
                $filePath = $this->sourceFolder . DIRECTORY_SEPARATOR . $filename;
                if (File::exists($filePath)) {
                    $unreferencedSize += File::size($filePath);
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'analysis' => [
                        'total_db_records' => count($databasePaths),
                        'total_filesystem_files' => count($filesystemPaths),
                        'total_filesystem_size' => $this->formatBytes($totalSize),
                        'unreferenced_files_count' => count($unreferencedFiles),
                        'unreferenced_files_size' => $this->formatBytes($unreferencedSize),
                        'missing_files_count' => count($missingFiles),
                        'space_can_be_freed' => $this->formatBytes($unreferencedSize),
                    ],
                    'unreferenced_files' => array_values($unreferencedFiles),
                    'missing_files' => array_values($missingFiles),
                    'storage_path' => $this->sourceFolder,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error analyzing media: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore images from extra_images back to all_images
     */
    public function restoreImages(Request $request): JsonResponse
    {
        try {
            if (!File::exists($this->destinationFolder)) {
                return response()->json([
                    'success' => false,
                    'message' => "Destination folder does not exist: {$this->destinationFolder}",
                ], 404);
            }

            // Get files to restore (or all if none specified)
            $filesToRestore = $request->input('files', []);
            $files = File::files($this->destinationFolder);
            $restoredFiles = [];
            $errors = [];

            foreach ($files as $file) {
                $filename = $file->getFilename();
                
                // If specific files requested, only restore those
                if (!empty($filesToRestore) && !in_array($filename, $filesToRestore)) {
                    continue;
                }

                $sourcePath = $file->getPathname();
                $destinationPath = $this->sourceFolder . DIRECTORY_SEPARATOR . $filename;
                
                try {
                    if (File::move($sourcePath, $destinationPath)) {
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
                'message' => 'Images restored successfully',
                'data' => [
                    'restored_files' => $restoredFiles,
                    'total_restored' => count($restoredFiles),
                    'errors' => $errors,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error restoring images: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete unreferenced images permanently (use with caution)
     */
    public function deleteUnreferencedImages(): JsonResponse
    {
        try {
            if (!File::exists($this->sourceFolder)) {
                return response()->json([
                    'success' => false,
                    'message' => "Source folder does not exist: {$this->sourceFolder}",
                ], 404);
            }

            // Get database paths (without prefix)
            $databasePaths = Media::pluck('path')->map(function ($path) {
                return str_replace($this->pathPrefix, '', $path);
            })->toArray();

            // Get all files from filesystem
            $files = File::files($this->sourceFolder);
            $deletedFiles = [];
            $errors = [];

            foreach ($files as $file) {
                $filename = $file->getFilename();
                
                // Check if this file is NOT referenced in database
                if (!in_array($filename, $databasePaths)) {
                    try {
                        if (File::delete($file->getPathname())) {
                            $deletedFiles[] = $filename;
                        } else {
                            $errors[] = "Failed to delete: {$filename}";
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Error deleting {$filename}: " . $e->getMessage();
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Unreferenced images deleted permanently',
                'data' => [
                    'deleted_files' => $deletedFiles,
                    'total_deleted' => count($deletedFiles),
                    'errors' => $errors,
                ],
                'warning' => 'Files have been permanently deleted and cannot be recovered'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting images: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get storage information
     */
    public function getStorageInfo(): JsonResponse
    {
        try {
            $info = [
                'source_folder' => $this->sourceFolder,
                'destination_folder' => $this->destinationFolder,
                'source_exists' => File::exists($this->sourceFolder),
                'destination_exists' => File::exists($this->destinationFolder),
                'source_is_directory' => File::isDirectory($this->sourceFolder),
                'destination_is_directory' => File::isDirectory($this->destinationFolder),
            ];

            if ($info['source_exists']) {
                $info['source_files_count'] = count(File::files($this->sourceFolder));
                $info['source_files'] = collect(File::files($this->sourceFolder))->map(function ($file) {
                    return $file->getFilename();
                })->take(10)->toArray(); // Show first 10 files as sample
            }

            if ($info['destination_exists']) {
                $info['destination_files_count'] = count(File::files($this->destinationFolder));
            }

            return response()->json([
                'success' => true,
                'data' => $info
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting storage info: ' . $e->getMessage(),
            ], 500);
        }
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