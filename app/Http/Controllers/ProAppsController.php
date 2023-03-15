<?php

namespace App\Http\Controllers;


use App\Models\ProApp;

class ProAppsController extends Controller
{
    

    public function checkStatus($appName)
    {
        $app = ProApp::where('name', $appName)->first();
        

        if ($app) {
            return response()->json([
                'status' => 'success',
                'message' => '',
                'data' => [
                    'status' => $app->status,
                    //'permissions' => $user->permissions()
                ]
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'App not found',
                'error' => '404'
            ]);
        }
    }


}
