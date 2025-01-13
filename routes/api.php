<?php

use App\Http\Controllers\Api\CreatePermissionRolController;
use App\Http\Controllers\Api\CreatePrimissionRolController;
use App\Http\Controllers\Api\PersonalInformationUserController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('auth')->group(function (){
    Route::post('login', [AuthController::class,'login']);
    
    Route::post('refresh-token', [AuthController::class,'refresh']);
    Route::post('register',[AuthController::class,'register']);
});

Route::middleware('auth:api')->prefix('users')->group(function (){
    Route::get('/role',[CreatePermissionRolController::class, 'getRole'])->middleware('rol:Super Admin');
    Route::post('/permissions',[CreatePermissionRolController::class,'createPermissionsAction'])->middleware('rol:Super Admin,Admin');
    Route::post('/role',[CreatePermissionRolController::class,'store'])->middleware('rol:Super Admin');
    Route::post('/user-information',[PersonalInformationUserController::class,'store'])->middleware('rol:Super Admin,Admin,User');
    Route::get('/profile', [PersonalInformationUserController::class,'getinfoUser'])->middleware('rol:Super Admin,Admin,User');
    Route::post('/save-photo-perfil', [PersonalInformationUserController::class,'SaveImage'])->middleware('rol:Super Admin,Admin,User');
});


Route::middleware('auth:api')->group(function () {
    Route::get('/admin-dashboard', function () {
        return response()->json(['message' => 'Welcome to the admin dashboard']);
    })->middleware('rol:Admin,Super Admin');
});


Route::middleware('auth:api')->prefix('users')->group(function () {
    Route::put('display-name',[PersonalInformationUserController::class,'updateDisplayName'])->middleware('rol:Super Admin,Admin,User');
    Route::post('logout', [AuthController::class,'logout']);
});

