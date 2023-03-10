<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register', 'refresh']]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid params',
                'error' => $validator->messages()
            ], 200);
        }

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'error' => 'credentials_invalid.',
                'message' => 'Login credentials are invalid.',
            ], 200);
        }

        $user = Auth::user();
        $user->roles;
        $user->permissions;

        //$user->permissions = $user->permissions();


        $payload = auth('api')->payload();

        return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => $user,
                    //'permissions' => $user->permissions(),
                    'jwtExpiresAt' => $payload('exp'),
                    'jwtExpiresIn' => auth('api')->factory()->getTTL() . " min.",
                    'jwtExpireDate' => date('m/d/Y H:i:s', $payload('exp')),
                ],
            ])
            ->header( 'jwt' ,$token)
            ->header( 'Access-Control-Expose-Headers' ,'jwt');
    }

    public function register(Request $request){        
        //Validate data
        $data = $request->only('name', 'email', 'last_name', 'middle_name', 'password');
        $validator = Validator::make($data, [
            'name' => 'required|string|max:100',
            'middle_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'error' => $validator->messages()], 200);
        }


        $user = User::create([
            'name' => $data["name"],
            'middle_name' => $data["middle_name"],
            'last_name' => $data["last_name"],
            'email' => $data["email"],
            'password' => bcrypt($data["password"]), // Other way: Hash::make($data["password"]),
            'created_by' => 0,
        ]);

        $token = Auth::login($user);
        $payload = auth('api')->payload();
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => [
                'user' => $user,
                'authorisation' => [
                    'type' => 'bearer',
                    'tkn_expires_in' => auth('api')->factory()->getTTL() . " min.",
                    'tkn_expire_date' => date('m/d/Y H:i:s', $payload('exp')),
                ]
            ]
        ])
        ->header( 'jwt' ,$token)
        ->header( 'Access-Control-Expose-Headers' ,'jwt');
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        $token = Auth::refresh(true, true);
        auth('api')->setToken($token);
        
        $user = Auth::user();
        $user->roles;

        $user->permissions; //= $user->permissions();


        $payload = auth('api')->payload();

        return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => $user,
                    //'permissions' => $user->permissions(),
                    'jwtExpiresAt' => $payload('exp'),
                    'jwtExpiresIn' => auth('api')->factory()->getTTL() . " min.",
                    'jwtExpireDate' => date('m/d/Y H:i:s', $payload('exp')),
                ],
            ])
            ->header( 'jwt' ,$token)
            ->header( 'Access-Control-Expose-Headers' ,'jwt');





        /*$payload = auth('api')->payload();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully refreshed token',
            'data' => [
                'user' => Auth::user(),
                'authorization' => [
                    'type' => 'bearer',
                    'tkn_expires_in' => auth('api')->factory()->getTTL() . " min.",
                    'tkn_expire_date' => date('m/d/Y H:i:s', $payload('exp')),
                ]
            ] 
        ])
        ->header( 'jwt' ,$token)
        ->header( 'Access-Control-Expose-Headers' ,'jwt');*/
    }

    public function getUserData()
    {
        $payload = auth('api')->payload();

        $user = Auth::user();
        $user->roles;
        $user->permissions;
 
        return response()->json([
            'status' => 'success',
            'message' => '',
            'data' => [
                'user' => $user,
                //'permissions' => $user->permissions(),
                'expire_in' => auth('api')->factory()->getTTL() . " min. ",
                'expire_date' => date('m/d/Y H:i:s', $payload('exp')),
                'payload' => $payload->toArray()
            ]
        ]);
    }
}
