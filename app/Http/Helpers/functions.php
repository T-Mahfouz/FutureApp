<?php

use App\Models\Media;
use App\Models\UserSetting;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Log;

if (!function_exists('jsonResponse')) {
    function jsonResponse($code = 200, $message = 'done', $data = []) {
        $code = getCode($code);
        return response()->json([
            'status_code' => $code,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}

if (!function_exists('jsonNotFoundResponse')) {
    function jsonNotFoundResponse($message = 'not found!') {
        return response()->json([
            'status_code' => 404,
            'message' => $message,
        ], 404);
    }
}

if (!function_exists('getCode')) {
    function getCode($code) {
        $code = (int)$code;
        return ($code >= 100 && $code < 600) ? $code : 500;
    }
}


if (!function_exists('jsonPaginateResponse')) {
    function jsonPaginateResponse(string $resource, $data) {
        
        if (!is_subclass_of($resource, JsonResource::class)) {
            throw new \InvalidArgumentException("The resource class must extend JsonResource.");
        }
        
        $collection = $resource::collection($data);

        return response()->json([
            'status_code' => 200,
            'message' => 'done',
            'data' => [
                'items' => $collection,
                'meta' => [
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                ]
            ],
        ], 200);
    }
}

if (!function_exists('uploadFile')) {
    function uploadFile($file, $path, $edit = false, $oldFile = null) {
        $destination = public_path().'/'.$path;
        $oldDestination = public_path().'/'.$path.'/'.$oldFile;
        if($edit && is_file($oldDestination)) {
            $name = explode('.', $oldFile)[0];
            if($name != 'default')
                unlink($oldDestination);
        }
        $ext = $file->getClientOriginalExtension();
        $name = time().Str::random(5);
        $fileName = $name.'.'.$ext;
        $file->move($destination, $fileName);
        return $fileName;
    }
}


if (!function_exists('resizeImage')) {
    function resizeImage($file, $path, $subfolder = 'all_images', $maxWidth = 512, $maxHeight = 512, $minDimension = 100, $quality = 85) {
        try {

            $imgManager = new ImageManager(new Driver());
            $img = $imgManager->read($file->getRealPath());

            // Get the original dimensions
            $originalWidth = $img->width();
            $originalHeight = $img->height();

            // Calculate new dimensions while maintaining aspect ratio
            $newDimensionsArray = calculateNewDimensions(
                $originalWidth, 
                $originalHeight, 
                $maxWidth, 
                $maxHeight, 
                $minDimension
            );
            
            $newWidth = $newDimensionsArray['width'];
            $newHeight = $newDimensionsArray['height'];

            // Generate filename
            $ext = strtolower($file->getClientOriginalExtension());
            $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $timestamp = time();
            $randomString = Str::random(8);
            $finalFilename = "{$baseName}-{$newWidth}x{$newHeight}-{$timestamp}-{$randomString}.{$ext}";

            $destination = $path . DIRECTORY_SEPARATOR . $subfolder;
            $imagePath = $subfolder . DIRECTORY_SEPARATOR . $finalFilename;
            $finalPath = $destination . DIRECTORY_SEPARATOR . $finalFilename;


            // Check if resize is actually needed
            if ($originalWidth == $newWidth && $originalHeight == $newHeight) {
                goto  insert;
            }
            
            // Ensure destination directory exists
            if (!file_exists($destination)) {
                if (!mkdir($destination, 0755, true)) {
                    throw new Exception("Failed to create directory: {$destination}");
                }
            }
            // Resize and save the image
            $img->resize($newWidth, $newHeight)->save($finalPath, $quality);

            insert:
                return insertToMedia($imagePath);

        } catch (Exception $e) {
            Log::error('Image resize failed: ' . $e->getMessage());
            return null;
        }
        
    }
}

if (!function_exists('deleteImage')) {
    function deleteImage(int $imageId): void
    {
        try {
            $media = Media::find($imageId);
            if ($media) {
                // Delete file from storage
                Storage::disk('public')->delete($media->path);
                
                // Delete media record
                $media->delete();
            }
        } catch (\Exception $e) {
            Log::error('Deleing Image failed => ', [$e->getMessage()]);
        }
    }
}

if (!function_exists('insertToMedia')) {
    function insertToMedia($path)
    {
        try {
            if (empty($path)) {
                return null;
            }
            
            // Check if media already exists
            $existingMedia = Media::where('path', $path)->first();
            if ($existingMedia) {
                return $existingMedia;
            }
            
            return Media::create([
                'path' => $path,
                'type' => 'image', // Assuming it's always an image, adjust as needed
                'size' => null, // Add file size if available
                'mime_type' => null // Add mime type if available
            ]);
            
        } catch (\Exception $e) {
            log::error('Failed to create media', [
                'image_path' => $path,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}

if (!function_exists('deleteImage')) {
    function deleteImage($id, $path) {
        if (File::exists(public_path($path))) {
            File::delete(public_path($path));

            Media::where('id', $id)->delete();
        }
        return;
    }
}


if (!function_exists('imagesSizes')) {
    function imagesSizes() {
        return [
            [110, 110],
            [165, 165],
            [180, 180],
            [200, 200],
            [300, 300],
        ];
    }
}

if (!function_exists('getFullImagePath')) {
    function getFullImagePath($model, $folder = 'storage') {

        $imagePath = $model->image->path ?? $model->path;

        $path = $folder . DIRECTORY_SEPARATOR . $imagePath;
        
        return ($model->image_id || $model->path ) ? url($path)  : null;
    }
}

if (!function_exists('getImagePath')) {
    function getImagePath($image) {
        if (!$image) {
            return null;
        }
        return env('APP_URL')."/$image";
    }
}


if (!function_exists('getUser')) {
    function getUser($guard = 'api') {
        if (Auth::guest()) {
            return null;
        }
        return Auth::guard($guard)->user();
    }
}


if (!function_exists('generateCode')) {
    function generateCode($digits=5, $key='verification_code') {
        $code = rand(pow(1, ($digits-1)), pow(10, $digits)-1);
        $exists = User::where($key, $code)->first();
        if ($exists)
            generateCode(key: $key);
        return $code;
    }
}


if (!function_exists('sendSMS')) {
    function sendSMS($to, $message) {
        $to = str_replace('+2','',$to);

        $url = "https://smsmisr.com/api/SMS/";

        $fields = "environment=1";
        $fields .= "&sender=c0b702cf5f1ee9a9407d5819203870d63e3acc49c76786c1b4f6c88f39e411ef";
        $fields .= "&username=66a17c01-4401-4f25-a0da-7465789671ad";
        $fields .= "&password=4857598902b008b747afcfbb6ea5228914faa5b226b57a1ab02cc1fbc0575a43";
        $fields .= "&mobile=2$to";
        $fields .= "&language=2";
        $fields .= "&message=$message";
        

        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_URL, sprintf($url));
        curl_setopt($ch, CURLOPT_POST, 3);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        // execute post
        $result = curl_exec($ch);
        $result = json_decode($result);

        // close connection
        curl_close($ch);
        return $result;
    }
}

if (!function_exists('getCurrentUser')) {
    function getCurrentUser() {
        $guards = ['api' => 'User', 'merchant' => 'Merchant', 'carrier' => 'Carrier'];
        $userGuard = 'api';
        foreach (array_keys($guards) as $guard) {
            if (Auth::guard($guard)->check()) {
                $userGuard = $guard;
                break;
            }
        }

        return [
            'model' => $guards[$userGuard],
            'data'  => Auth::guard($userGuard)->user() 
        ];
    }
}


if (!function_exists('FCMPush')) {
    function FCMPush($cityID,$title,$body,$type,$extra=[])
    {
        $config =  getConfig($cityID);
        if(!$config)
            return null;
        $url = 'https://fcm.googleapis.com/v1/projects/future-app-40ca2/messages:send';

        $data = array();
        foreach ($extra as $key => $value) {
        $data[$key] = $value;
        }

        if($config->firebase_topic != null) {
            $fields = [
            "message" => [
                "topic" => $config->firebase_topic,
                "notification" => [
                    "title" => (string)$title,
                    "body"  => (string)$body,
                    "image" => isset($extra['image']) ? (string)$extra['image'] : '',
                ],
                "data" => [
                    "priority" => "high",
                    "title" => (string)$title,
                    "body"  => (string)$body,
                    "image" => isset($extra['image']) ? $extra['image'] : '',
                    "institute_id" => isset($extra['institute_id']) ? (string)$extra['institute_id'] : 0
                ],

            ]
            ];
        }
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

        $credentials = new ServiceAccountCredentials($scopes, \Illuminate\Support\Facades\Storage::path('client_secret_google.json'));

        // Get the access token
        $token = $credentials->fetchAuthToken();

        // Print the access token
        $accessToken = $token['access_token'];

        $fcmApiKey = $config->firebase_token;

        $headers = array(
            'Authorization: Bearer ' . $accessToken,
            'Content-Type:application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === false) {
            die('cUrl faild: '.curl_error($ch));
        }
        curl_close($ch);
        
        return $result;
    }

}




if (!function_exists('resizeExistingImage')) {
    /**
     * Resize an existing image file
     * 
     * @param string $filename - The filename of the image
     * @param string $sourcePath - Full path to source directory
     * @param string $destinationPath - Full path to destination directory (optional, will overwrite original if not provided)
     * @param int $maxWidth - Maximum width for resizing
     * @param int $maxHeight - Maximum height for resizing
     * @param int $minDimension - Minimum dimension to prevent images from becoming too small
     * @param bool $keepOriginalName - Whether to keep the original filename
     * @param int $quality - Image quality (1-100)
     * @return array
     */
    function resizeExistingImage($filename, $sourcePath, $destinationPath = null, $maxWidth = 512, $maxHeight = 512, $minDimension = 100, $keepOriginalName = true, $quality = 85) {
        
        $success = false;
        $originalDimensions = null;
        $newDimensions = null;
        $finalPath = null;
        $message = 'resize completed';

        try {
            // Build full file paths
            $sourceFilePath = $sourcePath . DIRECTORY_SEPARATOR . $filename;
            
            // Check if source file exists
            if (!file_exists($sourceFilePath)) {
                throw new Exception("Source file does not exist: {$sourceFilePath}");
            }

            // If no destination provided, overwrite the original
            if ($destinationPath === null) {
                $destinationPath = $sourcePath;
            }

            $imgManager = new ImageManager(new Driver());
            $img = $imgManager->read($sourceFilePath);

            // Get the original dimensions
            $originalWidth = $img->width();
            $originalHeight = $img->height();

            // Calculate new dimensions while maintaining aspect ratio
            $newDimensionsArray = calculateNewDimensions(
                $originalWidth, 
                $originalHeight, 
                $maxWidth, 
                $maxHeight, 
                $minDimension
            );

            $newWidth = $newDimensionsArray['width'];
            $newHeight = $newDimensionsArray['height'];

            // Check if resize is actually needed
            if ($originalWidth == $newWidth && $originalHeight == $newHeight) {
                return [
                    'success' => true,
                    'message' => 'No resize needed - image already optimal size',
                    'original_dimensions' => "{$originalWidth}x{$originalHeight}",
                    'new_dimensions' => "{$newWidth}x{$newHeight}",
                    'path' => $sourceFilePath
                ];
            }

            // Generate filename
            if ($keepOriginalName) {
                $finalFilename = $filename;
            } else {
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $baseName = pathinfo($filename, PATHINFO_FILENAME);
                $timestamp = time();
                $randomString = Str::random(8);
                $finalFilename = "{$baseName}-{$newWidth}x{$newHeight}-{$timestamp}-{$randomString}.{$ext}";
            }

            // Ensure destination directory exists
            if (!file_exists($destinationPath)) {
                if (!mkdir($destinationPath, 0755, true)) {
                    throw new Exception("Failed to create directory: {$destinationPath}");
                }
            }

            $finalPath = $destinationPath . DIRECTORY_SEPARATOR . $finalFilename;

            // Resize and save the image
            $img->resize($newWidth, $newHeight)->save($finalPath, $quality);

            $originalDimensions = "{$originalWidth}x{$originalHeight}";
            $newDimensions = "{$newWidth}x{$newHeight}";
            $success = true;

        } catch (Exception $e) {
            $success = false;
            $message = $e->getMessage();
            Log::error('Image resize failed: ' . $e->getMessage());
        }

        return [
            'success' => $success,
            'message' => $message,
            'original_dimensions' => $originalDimensions,
            'new_dimensions' => $newDimensions,
            'path' => $finalPath
        ];
    }
}

if (!function_exists('calculateNewDimensions')) {
    /**
     * Calculate new dimensions while maintaining aspect ratio
     * 
     * @param int $originalWidth
     * @param int $originalHeight
     * @param int $maxWidth
     * @param int $maxHeight
     * @param int $minDimension
     * @return array
     */
    function calculateNewDimensions($originalWidth, $originalHeight, $maxWidth, $maxHeight, $minDimension) {
        // If image is already smaller than max dimensions, resize to smaller size
        if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
            $scaleFactor = 0.7; // Reduce to 70% of original size for small images
            $newWidth = max($minDimension, (int)($originalWidth * $scaleFactor));
            $newHeight = max($minDimension, (int)($originalHeight * $scaleFactor));
            
            return ['width' => $newWidth, 'height' => $newHeight];
        }

        // Calculate scale factor to fit within max dimensions
        $scaleWidth = $maxWidth / $originalWidth;
        $scaleHeight = $maxHeight / $originalHeight;
        $scaleFactor = min($scaleWidth, $scaleHeight, 1); // Don't upscale

        $newWidth = max($minDimension, (int)($originalWidth * $scaleFactor));
        $newHeight = max($minDimension, (int)($originalHeight * $scaleFactor));

        return ['width' => $newWidth, 'height' => $newHeight];
    }
}

if (!function_exists('resizeImageInPlace')) {
    /**
     * Resize image and replace the original file
     * 
     * @param string $filePath - Full path to the image file
     * @param int $maxWidth
     * @param int $maxHeight
     * @param int $minDimension
     * @param int $quality
     * @return array
     */
    function resizeImageInPlace($filePath, $maxWidth = 512, $maxHeight = 512, $minDimension = 100, $quality = 85) {
        $filename = basename($filePath);
        $directory = dirname($filePath);
        
        return resizeExistingImage($filename, $directory, $directory, $maxWidth, $maxHeight, $minDimension, true, $quality);
    }
}

/**
 * Alternative simpler version if you want to keep everything in one function
 */
if (!function_exists('resizeImageSimple')) {
    function resizeImageSimple($file, $path, $maxDimension = 400) {
        try {
            $imgManager = new ImageManager(
                new Intervention\Image\Drivers\Gd\Driver()
            );
            
            $img = $imgManager->read($file->getRealPath());

            // Get the original dimensions
            $originalWidth = $img->width();
            $originalHeight = $img->height();

            // Calculate scale factor to fit within max dimension
            $maxOriginal = max($originalWidth, $originalHeight);
            $scaleFactor = min($maxDimension / $maxOriginal, 1); // Don't upscale

            $newWidth = (int)($originalWidth * $scaleFactor);
            $newHeight = (int)($originalHeight * $scaleFactor);

            // Generate unique filename
            $ext = strtolower($file->getClientOriginalExtension());
            $fileName = $newWidth . 'x' . $newHeight . '-' . time() . '-' . Str::random(8) . '.' . $ext;

            // Ensure destination directory exists
            $destination = public_path($path);
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            // Resize and save the image
            $img->resize($newWidth, $newHeight)
                ->save($destination . '/' . $fileName, 85);
            
            return "$path/$fileName";

        } catch (Exception $e) {
            Log::error('Image resize failed: ' . $e->getMessage());
            return null; // or throw exception based on your error handling strategy
        }
    }
}



if (!function_exists('sendWhatsAppMessage')) {
    function sendWhatsAppMessage($to, $message) {
        $response = Http::withHeaders([
            'apikey' => 'YOUR_GUPSHUP_API_KEY',
        ])->post('https://api.gupshup.io/sm/api/v1/msg', [
            'channel' => 'whatsapp',
            'source' => 'YOUR_REGISTERED_NUMBER',
            'destination' => $to, // مثال: "2010xxxxxxx"
            'message' => json_encode([
                'type' => 'text',
                'text' => $message
            ]),
            'src.name' => 'Future'
        ]);
        return $response->json();
    }
}
