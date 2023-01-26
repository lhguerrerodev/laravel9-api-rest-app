<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get Permissions list.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $permissions = Permission::where('reg_status', '0')->get();

        return response()->json([
            'status' => 'success',
            'message' => '',
            'data' => [
                'permissions' => $permissions
            ]
        ]);
    }

    /**
     * Create a new Permission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //Validate data
        $data = $request->only(['name', 'description']);
        $validator = Validator::make($data, [
            'name' => [
                'required',
                'string',
                Rule::unique('permissions')->where(fn ($query) => $query->where('reg_status', '0'))
            ],
            'description' => 'required|string|',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad params',
                'error' => $validator->messages()
            ]);
        }

        $user = Auth::user();

        $permission = Permission::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => $user->id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Permission stored succesfuly',
            'data' => [
                'permission' => $permission
            ]
        ]);
    }

    /**
     * Get specified Permission in storage.
     *
     * @param  int  $id : Permission Id
     * @return \Illuminate\Http\Response
     */
    public function read($id)
    {
        $permission = Permission::where('id', $id)->where('reg_status', '0')->first();

        if ($permission)
            return response()->json([
                'status' => 'success',
                'message' => '',
                'data' => [
                    'permission' => $permission
                ]
            ]);
        else
            return response()->json([
                'status' => 'error',
                'message' => 'Permission not found',
                'error' => '404'
            ]);
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id : Permission Id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::where('id', $id)->where('reg_status', '0')->first();
        if ($permission) {

            //Validate data
            $data = $request->only(['name', 'description']);
            $validator = Validator::make($data, [
                'name' => [
                    'required',
                    'string',
                    Rule::unique('permissions')->ignore($permission->id)->where(fn ($query) => $query->where('reg_status', '0'))
                ],
                'description' => 'required|string',
            ]);

            //Send failed response if request is not valid
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bad params',
                    'error' => $validator->messages()
                ]);
            }




            $user = Auth::user();


            $permission->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated_by' => $user->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Role updated successfully',
                'data' => [
                    'permission' => $permission
                ]
            ]);
        } else
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found',
                'error' => '404'
            ]);
    }

    /**
     * Change reg status to 99 (Deleted)
     *
     * @param  int  $id : Permission Id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $permission = Permission::where('id', $id)->where('reg_status', '0')->first();

        $user = Auth::user();

        if ($permission) {
            $permission->update([
                'reg_status' => '99',
                'updated_by' => $user->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Permission deleted successfully',
            ]);
        } else
            return response()->json([
                'status' => 'error',
                'message' => 'Permission not found',
                'error' => '404'
            ]);
    }
}
