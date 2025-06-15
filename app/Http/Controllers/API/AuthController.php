<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\InitController;
use App\Http\Requests\API\User\AuthRequest;
use App\Http\Requests\API\Users\Auth\ChangePasswordRequest;
use App\Http\Requests\API\Users\Auth\ResetPasswordRequest;
use App\Http\Resources\API\AuthResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends InitController
{
    public function __construct()
    {
        parent::__construct();

        $this->pipeline->setModel('User');
    }

    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return jsonResponse(401, 'Wrong phone or password!');
        }
        
        $user = Auth::guard('api')->user();
        $user->access_token = $token;
        

        $data = new AuthResource($user);

        return jsonResponse(200, 'done.', $data);
    }

    public function register(AuthRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->only(['email','name','city_id','phone']);
            
            if($request->hasFile('image')) {

                $image = $request->file('image');

                $media = resizeImage($image, $this->storagePath, 'all_images'.DIRECTORY_SEPARATOR.'users');
            
                $imageId = $media->id ?? null;
                
                $data['image_id'] = $imageId;
            }

            $data['password'] = Hash::make($request->password);
            $user = $this->pipeline->setModel('User')->create($data);
            $user->access_token = auth()->guard('api')->tokenById($user->id);

            $data = new AuthResource($user);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return jsonResponse($e->getCode(), $e->getMessage());
        }
        
        return jsonResponse(201, 'done.', $data);
    }

    public function sendVerificationCode(Request $request)
    {
        $user = Auth::guard('api')->user();
        
        $phone = $request->phone;
        
        $user = $this->pipeline->setModel('User')->where(['phone' => $phone])->first();
        if (!$user) {
            return jsonResponse(404, 'not found.');
        }

        if ($user->settings->verified) {
            return jsonResponse(404, 'already verified!');
        }

        //TODO: sendSMS($phone, "Your AROUND code is: $user->activation_code");
        
        return jsonResponse(200, 'done.');
    }
    
    public function forgetPasswordRequest(Request $request)
    {
        $user = Auth::guard('api')->user();
        
        $phone = $request->phone;
        
        $code = generateCode(key: 'change_password_code');

        $user = $this->pipeline->setModel('User')
            ->where('phone', $phone)
            ->first();
        
        if (!$user) {
            return jsonResponse(404, 'User not found.');
        }

        $userSetting = $this->pipeline->setModel('UserSetting')
            ->where(['model_name' => 'User','model_id' => $user->id])
            ->first();

        if (!$userSetting) {
            return jsonResponse(404, 'something went wrong, please contact with the technical support.');
        }

        $userSetting->update(['change_password_code' => $code]);
        //TODO: sendSMS($phone, "Use : $user->activation_code");
        
        return jsonResponse(200, 'done.');
    }
    
    public function verify(Request $request)
    {
        $data = $request->only(['phone', 'verification_code']);

        $user = $this->pipeline->setModel('User')
            ->select(['users.*','us.verification_code','us.model_id'])
            ->leftJoin('user_settings as us', function($sql) use($data){
                return $sql->on('users.id','=','us.model_id');
            })
            ->where([
                'us.verification_code' => $data['verification_code'],
                'us.model_name' => 'User'
            ])
            ->where('phone', $data['phone'])
            ->first();
            
        if (!$user) {
            return jsonResponse(404, 'not found.');
        }

        $this->pipeline->setModel('UserSetting')
            ->update(['verified' => 1,'verification_code' => null], ['model_id' => $user->id, 'model_name' => 'User']);

        return jsonResponse(201, 'done.');
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $password = $request->password;
        $oldPassword = $request->old_password;
        
        $user = Auth::guard('api')->user();
        
        if (!$user || !Hash::check($oldPassword, $user->password)) {
            return jsonResponse(400, 'Invalid password!');
        }

        $user->password = $password;
        $user->save();

        $data = new AuthResource($user);

        return jsonResponse(201, 'done.', $data);
    }

    public function resetWithLogin(ResetPasswordRequest $request)
    {
        $password = $request->password;
        $phone = $request->phone;
        $code = $request->code;
        
        $user = $this->pipeline->setModel('User')
            ->select(['users.*','us.verification_code','us.model_id'])
            ->leftJoin('user_settings as us', function($sql) use($code){
                return $sql->on('users.id','=','us.model_id');
            })
            ->where([
                'us.change_password_code' => $code,
                'us.model_name' => 'User'
            ])
            ->where('users.phone', $phone)
            ->first();
        
        if (!$user) {
            return jsonResponse(404, 'check your code!');
        }

        $user->password = $password;
        $user->save();

        $this->pipeline->setModel('UserSetting')
            ->update(['change_password_code' => null], ['model_id' => $user->id, 'model_name' => 'User']);

        $data = new AuthResource($user);

        return jsonResponse(201, 'done.', $data);
    }

    public function logout(Request $request)
    {
        auth()->logout();

        return jsonResponse(200, 'Successfully logged out');
    }
}
