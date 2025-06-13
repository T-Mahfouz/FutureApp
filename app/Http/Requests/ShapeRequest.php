<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ShapeRequest extends FormRequest
{
    protected function failedValidation(Validator $validator) 
    { 
        throw new HttpResponseException( jsonResponse(422, $this->getMessages($validator->errors())) );
    }

    private function getMessages($errors)
    {
        $messages = [];
        $keys = $errors->keys();
        $errors = $errors->toArray();
        
        foreach($keys as $key) {
            $messages = array_merge($messages, $errors[$key]);
        }
        return $messages;
    }
}