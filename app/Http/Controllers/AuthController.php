<?php

namespace App\Http\Controllers;

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
               //return $request->all();
               $currentToken = JWTAuth::getToken();

        // Verifica si el token existe
        if (!$currentToken) {
            return response()->json(['error' => 'Token not provided'], 400);
        }

        // Si el token está presente, intenta obtener el usuario asociado
        $user = JWTAuth::toUser($currentToken); // Usa JWTAuth::toUser para obtener el usuario desde el token

        if (!$user) {
            return response()->json(['error' => 'User not found'], 400);
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

        // Invalida el token anterior (esto lo hace automáticamente JWTAuth::refresh)
        // JWTAuth::invalidate($currentToken);  // Opcional si quieres invalidar manualmente

        return response()->json([
            'token' => $newToken,
            'message' => 'Token updated successfully',
        ], 200);
            } catch (\Exception $e) {
                //throw $th;
                return $e->getMessage();
            }
        }

        // Logout and invalidate the JWT token
        public function logout()
        {
            //return 'aqui';
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json(['message' => 'User logged out successfully','status' => 200]);
        }
    }
