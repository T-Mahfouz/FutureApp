<?php

use App\Models\Media;
use App\Models\UserSetting;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

if (!function_exists('jsonResponse')) {
    function jsonResponse($code = 200, $message = 'done', $data = []) {
        $code = getCode($code);
        return response()->json([
            'status_code' => $code,
            'message' => $message,
            'data' => $data,
        ], $code);

        // return response()->json(
        //     $data = [],
        //     int $status = 200,
        //     array $headers = [],
        //     int $options = 0
        // ):
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
    function resizeImage($file, $path) {
        
        $imgManager = new ImageManager(
            new Intervention\Image\Drivers\Gd\Driver()
        );
        
        $img = $imgManager->read($file->getRealPath());

        // Get the original dimensions
        $originalWidth = $img->width();
        $originalHeight = $img->height();

        // Calculate the new dimensions (one-third of the original dimensions)
        if ($originalWidth > 400 || $originalHeight > 400) {
            $newWidth = (int) ($originalWidth - ($originalWidth * 0.9));
            $newHeight = (int) ($originalHeight - ($originalHeight * 0.9));
            
            if ($newWidth >= 200 || $newHeight >= 200) {
                $newWidth = (int) ($newWidth / 2);
                $newHeight = (int) ($newHeight / 2);
            }
        } else {
            $newWidth = (int) ($originalWidth / 4);
            $newHeight = (int) ($originalHeight / 4);
        }

        $ext = strtolower($file->getClientOriginalExtension());
        $name = time().Str::random(5);
        $fileName = $newWidth.'X'."$newHeight-$name.$ext";

        $destination = public_path($path);
        
        if (!file_exists($destination)) {
            mkdir($destination, 0777, true);
        }

        // Resize the image while maintaining the aspect ratio
        $img->resize($newWidth, $newHeight)
            ->save($destination.'/'.$fileName);
        
        return "$path/$fileName";
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
    function getFullImagePath($model, $folder = 'uploads') {
        return $model->image_id ? $model->image->realPath($folder) : null;
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