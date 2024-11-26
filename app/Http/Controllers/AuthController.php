<?php

namespace App\Http\Controllers;

use App\Http\Response\ApiResponse;
use App\Models\MntPersonalInformationUserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

    class AuthController extends Controller
    {
        //
        public function login(Request $request){
            $credential = $request->only('email', 'password');
            
            if(!$token = JWTAuth::attempt($credential)){
                return response()->json(['errors' => 'Credenciales invalidas'], 401);
            }

            $user = Auth::user();
            $userInformation = MntPersonalInformationUserModel::where('user_id', $user->id)->first();
            
            $customClaims = [
                'user_id' => $user->id,
                'email'=>$user->email,
                'userInformation' => $userInformation ? $userInformation->toArray() : null, // Asegúrate de convertir a array
                'role' => $user->getRoleNames() // Incluye roles si es necesario
            ];
            

            // if(!$user->hasRole('Admin')){
            //     return response()->json(['error' => 'not_admin'], 403);
            // }
            $token = JWTAuth::claims($customClaims)->fromUser($user);

            return response()->json([ 'token' => $token, 'role' => $user->getRoleNames()]);
        }

        public function refresh(Request $request)
{
    try {
        $currentToken = JWTAuth::getToken();

        // Verifica si el token existe
        if (!$currentToken) {
            return response()->json(['error' => 'Token not provided'], 400);
        }

        // Intenta obtener el usuario asociado con el token
        try {
            $user = JWTAuth::toUser($currentToken);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            // Si el token ha expirado, puedes intentar refrescarlo
            $currentToken = JWTAuth::refresh($currentToken);
            $user = JWTAuth::toUser($currentToken);
        }

        // Si no se encuentra el usuario, usa el user_id de la solicitud
        $id_user = $user->id ?? $request->user_id;

        // Obtén la información del usuario
        $userInformation = MntPersonalInformationUserModel::where('user_id', $id_user)->first();

        // Personaliza los claims
        $customClaims = [
            'user_id' => $user->id,
            'email' => $user->email,
            'userInformation' => $userInformation ? $userInformation->toArray() : null, // Convierte a array si es necesario
            'role' => $user->getRoleNames() // Incluye roles si es necesario
        ];

        // Refresca el token con los nuevos claims
        $newToken = JWTAuth::claims($customClaims)->refresh($currentToken);

        return response()->json([
            'token' => $newToken,
            'message' => 'Token updated successfully',
        ], 200);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

        // Logout and invalidate the JWT token
        public function logout()
{
    try {
        // Verifica si el token está presente
        $token = JWTAuth::getToken();
        
        if (!$token) {
            return response()->json([
                'message' => 'No token provided',
                'status' => 400
            ], 400);
        }

        // Invalidar el token
        JWTAuth::invalidate($token);

        return response()->json([
            'message' => 'User logged out successfully',
            'status' => 200
        ], 200);
        
    } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
        // Error relacionado con el token JWT
        return response()->json([
            'message' => 'Error invalidating token: ' . $e->getMessage(),
            'status' => 500
        ], 500);
    } catch (\Exception $e) {
        // Error general
        return response()->json([
            'message' => 'Error al desloguear el usuario: ' . $e->getMessage(),
            'status' => 500
        ], 500);
    }
}
    }
