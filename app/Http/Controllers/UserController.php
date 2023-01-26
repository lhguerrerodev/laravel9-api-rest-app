<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get user list.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::where('reg_status', '0')
            //->orderBy('name')
            //->take(10)
            ->get();
        //User::all()->where('reg_status', '0'); // Company::orderBy('id','desc')->paginate(5);

        return response()->json([
            'status' => 'success',
            'message' => '',
            'data' => [
                'users' => $users
            ]
        ]);
    }

    /**
     * Get specified user in storage.
     *
     * @param  int  $id : User Id
     * @return \Illuminate\Http\Response
     */
    public function read($id)
    {
        $user = User::where('id', $id)->where('reg_status', '0')->first();

        if ($user) {
            $user->roles;
            return response()->json([
                'status' => 'success',
                'message' => '',
                'data' => [
                    'user' => $user,
                    'permissions' => $user->permissions()
                ]
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
                'error' => '404'
            ]);
        }
    }

    /**
     * Create a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $data = $request->only('name', 'email', 'last_name', 'middle_name', 'password');
        //Validate data
        $validator = Validator::make($data, [
            'name' => 'required|string|max:100',
            'middle_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->where(fn ($query) => $query->where('reg_status', '0'))
            ],
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'params validation errors',
                'error' => $validator->messages()
            ], 200);
        }

        $authUser = Auth::user();

        $user = User::create([
            'name' => $data['name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'created_by' => $authUser->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => [
                'user' => $user,
            ]
        ]);
    }


    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id : User Id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::where('id', $id)->where('reg_status', '0')->first();
        if ($user) {

            $data = $request->only('name', 'email', 'last_name', 'middle_name');
            //Validate data
            $validator = Validator::make($data, [
                'name' => 'required|string|max:100',
                'middle_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users')->ignore($user->id)->where(fn ($query) => $query->where('reg_status', '0'))
                ],
            ]);

            //Send failed response if request is not valid
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'params validation errors',
                    'error' => $validator->messages()
                ], 200);
            }



            $authUser = Auth::user();


            $user->update([
                'name' => $data['name'],
                'middle_name' => $data['middle_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'updated_by' => $authUser->id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully',
                'data' => [
                    'user' => $user,
                ]
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
                'error' => '404'
            ]);
        }
    }

    /**
     * Change reg status to 99 (Deleted)
     *
     * @param  int  $id : User Id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $user = User::where('id', $id)->where('reg_status', '0')->first();
        $authUser = Auth::user();
        if ($user) {
            $user->update([
                'reg_status' => '99',
                'updated_by' => $authUser->id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully',
                'data' => [
                    'user' => $user,
                ]
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
                'error' => '404'
            ]);
        }
    }

    /**
     * Assigns a role to the specified user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id : User Id
     * @return \Illuminate\Http\Response
     */
    public function assignRole(Request $request, $id)
    {
        //Validate data
        $data = $request->only('role_id');
        $validator = Validator::make($data, [
            'role_id' => 'required|integer',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad params',
                'error' => $validator->messages()
            ]);
        }

        $user = User::where('id', $id)->where('reg_status', '0')->first();


        if ($user) {

            $authUser = Auth::user();

            $role = Role::find($data['role_id']);

            if ($role) {
                try {
                    $user->roles()->attach([$role->id  => [
                        'created_by' => $authUser->id
                    ]]);
                } catch (Exception $e) {
                }
            } else {

                return response()->json([
                    'status' => 'error',
                    'message' => 'Role id not found',
                    'error' => '404'
                ]);
            }

            $user->roles;

            return response()->json([
                'status' => 'success',
                'message' => 'Role updated successfully',
                'data' => [
                    'user' => $user,
                    'permissions' => $user->permissions()
                ]
            ]);
        } else
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
                'error' => '404'
            ]);
    }

    /**
     * Assigns a permissions list to the specified user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id : User Id
     * @return \Illuminate\Http\Response
     */
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

        $user = User::where('id', $id)->where('reg_status', '0')->first();


        if ($user) {

            $authUser = Auth::user();

            foreach ($request->permissions as $per_id) {

                $per = Permission::find($per_id);

                if ($per) {

                    try {
                        $user->permissionsR()->attach([$per->id  => [
                            'created_by' => $authUser->id
                        ]]);
                    } catch (Exception $e) {
                    }
                }
            }

            $user->roles;

            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully',
                'data' => [
                    'user' => $user,
                    'permissions' => $user->permissions()
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
     * Remove role to the specified user.
     *
     * @param  int  $id : User Id
     * @param  int  $roleId : Role Id
     * @return \Illuminate\Http\Response
     */
    public function removeRole($id, $roleId)
    {


        $user = User::where('id', $id)->where('reg_status', '0')->first();

        if ($user) {

            try {
                $user->roles()->detach($roleId);
            } catch (Exception $e) {
            }

            $user->roles;
            return response()->json([
                'status' => 'success',
                'message' => 'Role removed successfuly',
                'data' => [
                    'user' => $user,
                    'permissions' => $user->permissions()
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
     * Remove permission to the specified user.
     *
     * @param  int  $id : User Id
     * @param  int  $permissionId : Permission Id
     * @return \Illuminate\Http\Response
     */
    public function removePermission($id, $permissionId)
    {


        $user = User::where('id', $id)->where('reg_status', '0')->first();

        if ($user) {

            try {
                $user->permissionsR()->detach($permissionId);
            } catch (Exception $e) {
            }

            $user->roles;
            return response()->json([
                'status' => 'success',
                'message' => 'Permission removed successfuly',
                'data' => [
                    'user' => $user,
                    'permissions' => $user->permissions()
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
