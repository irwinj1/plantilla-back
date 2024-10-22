<?php

use App\Http\Controllers\Api\CreatePrimissionRolController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('auth')->group(function (){
    Route::post('login', [AuthController::class,'login']);
    Route::post('logout', [AuthController::class,'logout']);
    Route::post('refresh-token', [AuthController::class,'refresh']);
});

Route::middleware('auth:api')->prefix('users')->group(function (){
    Route::post('/permissions',[CreatePrimissionRolController::class,'createPermissionsAction'])->middleware('rol:Super Admin');
    Route::post('role',[CreatePrimissionRolController::class,'store'])->middleware('rol:Super Admin');
});


Route::middleware('auth:api')->group(function () {
    Route::get('/admin-dashboard', function () {
        return response()->json(['message' => 'Welcome to the admin dashboard']);
    })->middleware('rol:Admin,Super Admin');
});