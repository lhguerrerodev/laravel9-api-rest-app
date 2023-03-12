<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CVController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
    Route::get('user', 'getUserData');
});

// USER
Route::controller(UserController::class)->middleware('authorization:rol_super_admin')->prefix('users')->group(function () {
   Route::get('/', 'index');
   Route::post('/', 'create');
   Route::get('/{id}', 'read');
   Route::put('/{id}', 'update');
   Route::delete('/{id}', 'delete');

   Route::post('/assign-role/{id}', 'assign_role');
   Route::post('/{id}/assign-permissions', 'assignPermissions');
   Route::delete('/{id}/remove-role/{roleId}', 'removeRole');
   Route::delete('/{id}/remove-permission/{permissionId}', 'removePermission');
});

// ROLES
Route::controller(RoleController::class)->middleware('authorization:rol_super_admin')->prefix('roles')->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'create');
    Route::get('/{id}', 'read');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'delete');
    Route::post('{id}/assign-permissions', 'assignPermissions');
    Route::delete('/{id}/remove-permission/{permissionId}', 'removePermission');
});

// PERMISSIONS
Route::controller(PermissionController::class)->middleware('authorization:rol_super_admin')->prefix('permissions')->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'create');
    Route::get('/{id}', 'read');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'delete');
});


Route::post('contact', [CVController::class, 'postContact']);