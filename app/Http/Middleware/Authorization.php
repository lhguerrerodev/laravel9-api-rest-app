<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class Authorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $param rol_super_admin|per_delete_user
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $param)
    {
        $rules = explode("|", $param);
        $user = Auth::user();

        foreach ($rules as &$val) {
            $type = substr($val, 0, 3);
            $value = substr($val, 4);

            if($type == 'rol'){
                $role = $user->roles()->where('name', $value)->first();

                if($role){
                    return $next($request);
                }

            } else if ($type == 'per' ) {
                $permissions = $user->allPermissions();
                error_log(implode(" ",$permissions));

                if (in_array($value, $permissions)) {
                    return $next($request);
                }

            } 
        }

        return response()->json([
            'status' => 'error',
            'error' => '403',
            'message' => 'Access denied',
        ]);
    }
}
