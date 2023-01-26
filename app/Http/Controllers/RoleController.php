<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Role::where('reg_status', '0')->get()->makeVisible('id');

        return response()->json([
            'status' => 'success',
            'message' => '',
            'data' => [
                'roles' => $roles
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //Validate data
        $data = $request->only(['name', 'description']);
        $validator = Validator::make($data, [
            //'name' => 'required|string|unique:roles',
            'name' => [
                'required',
                'string',
                Rule::unique('roles')->where(fn ($query) => $query->where('reg_status', '0'))
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

        $user = Auth::user(); //JWTAuth::authenticate($request->token);

        $role = Role::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => $user->id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Role stored succesfuly',
            'data' => [
                'role' => $role
            ]
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function read($id)
    {
        $role = Role::where('id', $id)->where('reg_status', '0')->first();

        if ($role) {
            $role->permissions;
            return response()->json([
                'status' => 'success',
                'message' => '',
                'data' => [
                    'role' => $role->makeVisible('id')
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $role = Role::where('id', $id)->where('reg_status', '0')->first();

        if ($role) {

            //Validate data
            $data = $request->only(['name', 'description']);
            $validator = Validator::make($data, [
                'name' => [
                    'required',
                    'string',
                    Rule::unique('roles')->ignore($role->id)->where(fn ($query) => $query->where('reg_status', '0'))
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


            $role = Role::where('id', $id)->where('reg_status', '0')->first();

            $user = Auth::user();


            $role->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated_by' => $user->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Role updated successfully',
                'data' => [
                    'role' => $role
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $role = Role::where('id', $id)->where('reg_status', '0')->first();
        $authUser = Auth::user();

        if ($role) {
            $role->update([
                'reg_status' => '99',
                'updated_by' => $authUser->id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Role deleted successfully',
            ]);
        } else
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found',
                'error' => '404'
            ]);
    }


    public function assignPermissions(Request $request, $id)
    {
        //Validate data
        $data = $request->only('permissions');
        $validator = Validator::make($data, [
            'permissions' => 'required|array',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad params',
                'error' => $validator->messages()
            ]);
        }

        $role = Role::where('id', $id)->where('reg_status', '0')->first();


        if ($role) {

            $authUser = Auth::user();

            foreach ($request->permissions as $per_id) {

                $per = Permission::find($per_id);

                if ($per) {

                    try {
                        $role->permissions()->attach([$per->id  => [
                            'created_by' => $authUser->id
                        ]]);
                    } catch (Exception $e) {
                    }
                }
            }

            $role->permissions;

            return response()->json([
                'status' => 'success',
                'message' => 'Role updated successfully',
                'data' => [
                    'role' => $role
                ]
            ]);
        } else
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found',
                'error' => '404'
            ]);
    }

    public function removePermission($id, $permissionId)
    {
        $role = Role::where('id', $id)->where('reg_status', '0')->first();
        if ($role) {
            try {
                $role->permissions()->detach($permissionId);
            } catch (Exception $e) {
            }

            $role->permissions;

            return response()->json([
                'status' => 'success',
                'message' => 'Permission removed successfuly',
                'data' => [
                    'role' => $role
                ]
            ]);
        } else
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found',
                'error' => '404'
            ]);
    }
}
