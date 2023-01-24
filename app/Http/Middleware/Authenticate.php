<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;
use JWTAuth;

use Exception;


class Authenticate extends Middleware
{

    public function handle($request, Closure $next, ...$guards)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            error_log($e);

            if ($e instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenBlacklistedException){
                return response()->json([
                    'status' => 'error',
                    'error' => 'token_blaklist',
                	'message' => 'Token is in Blacklist',
                ], 403);
            } else if ($e instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json([
                    'status' => 'error',
                    'error' => 'token_invalid',
                	'message' => 'Token is Invalid',
                ], 403);
            } else if ($e instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json([
                    'status' => 'error',
                    'error' => 'token_expired',
                	'message' => 'Token is Expired',
                ], 403);
            } else {
                return response()->json([
                    'status' => 'error',
                    'error' => 'token_not_found',
                	'message' => 'Authorization Token not found',
                ], 403);
            }
        }
        return $next($request);
    }
}
