<?php

namespace App\Http\Controllers;

use App\Rules\ReCaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class CVController extends Controller
{
    public function postContact(Request $request)
    {
        $data = $request->only('name', 'email', 'subject', 'message', 'gRecaptchaToken');
        //Validate data
        $validator = Validator::make($data, [
            'name' => 'required',
            'email' => 'required',
            'subject' => 'required',
            'message' => 'required',
            'gRecaptchaToken' => ['required', new ReCaptcha]
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'params validation errors',
                'error' => $validator->messages()
            ], 200);
        }


        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => [
                'data' => $data ,
            ]
        ]);
    }
    
}
