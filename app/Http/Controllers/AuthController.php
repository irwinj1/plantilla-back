<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    //
    public function login(Request $request){
        $credential = $request->only('email', 'password');
        
        if(!$token = JWTAuth::attempt($credential)){
            return response()->json(['errors' => 'Credenciales incvalidas'], 401);
        }

        $user = Auth::user();
        
        // if(!$user->hasRole('Admin')){
        //     return response()->json(['error' => 'not_admin'], 403);
        // }
       // return auth()->user();
        return response()->json([ 'token' => $token, 'role' => $user->getRoleNames()]);
    }

    public function refresh()
    {
        return response()->json(['token' => JWTAuth::refresh()]);
    }

    // Logout and invalidate the JWT token
    public function logout()
    {
       // dd(JWTAuth::getToken());
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'User logged out successfully']);
    }
}
