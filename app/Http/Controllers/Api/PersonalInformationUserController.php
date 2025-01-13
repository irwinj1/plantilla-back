<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePersonalInformationRequest;
use App\Http\Requests\UpdatePersonalInformationResquest;
use App\Http\Response\ApiResponse;
use App\Models\MntPersonalInformationUserModel;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Storage;

class PersonalInformationUserController extends Controller
{
    //
    public function index(){}

    public function store(StorePersonalInformationRequest $request){
        try {
            //code...
            
           // return $request->validated();
            DB::beginTransaction();
            //code...
            
            
            $user_id = auth()->user()->id;
            $request->merge(['user_id' => $user_id]);
            $personalInformation = MntPersonalInformationUserModel::create($request->validated());

            DB::commit();
            return ApiResponse::success('Información personal guardad',200,$personalInformation);

        } catch (\Exception $e) {
            //throw $th;
            return ApiResponse::error($e->getMessage());
        }
    }
    public function getinfoUser(){
        try {
            // Obtén el ID del usuario autenticado
            $user_id = auth()->user()->id;
  
            // Recupera la información personal del usuario
            $personalInformation = MntPersonalInformationUserModel::with(['user' => function($query) {
                $query->select(['id', 'name', 'email']);
            }])->where('user_id', $user_id)
            ->select([
                'first_name', 'second_name', 'third_name', 
                'first_last_name', 'second_last_name', 
                'married_name', 'image_url', 'phone_number', 'user_id','id'
            ])
            ->first();
    
            // Si no se encuentra la información personal
            if (!$personalInformation) {
                return ApiResponse::error('No se encontró la información personal', 404);
            }
    
            // Verifica si hay una imagen
            // if ($personalInformation->image_url) {
            //     // Ruta completa de la imagen almacenada
            //     $imagePath = storage_path('app/avatars/' . $personalInformation->image_url);
                
            //     // Si la imagen existe
            //     if (file_exists($imagePath)) {
            //         // Obtiene los datos de la imagen
            //         $imageData = file_get_contents($imagePath);
            //         $base64Image = base64_encode($imageData);
                    
            //         // Obtiene el tipo MIME de la imagen
            //         $mimeType = mime_content_type($imagePath);
    
            //         // Combina el tipo MIME con la cadena Base64 para crear un URI de datos
            //         $base64ImageWithMime = 'data:' . $mimeType . ';base64,' . $base64Image;
    
            //         // Asigna la imagen base64 al campo `image_url`
            //         $personalInformation->image_url = $base64ImageWithMime;
            //     } else {
            //         $personalInformation->image_url = null;  // Imagen no encontrada
            //     }
            // }
    
            // Devuelve la respuesta con la información del perfil
            return ApiResponse::success('Profile information', 200, $personalInformation);
    
        } catch (\Exception $e) {
            // Captura errores y los devuelve en la respuesta
            return ApiResponse::error($e->getMessage());
        }
    }

    public function SaveImage(Request $request){
        try {
            //code...
            //dd($request->all());
            //return auth()->user();
            
            DB::beginTransaction();
            $validations = Validator::make($request->all(),[
                'image' => 'required',
            ],[
                'image.required' => 'La imagen es obligatoria',
                
                // 'image.dimensions' => 'La imagen debe tener un tamaño de 1000x1000 pixels',
            ]);
            
            if($validations->fails()){
                return ApiResponse::error($validations->errors()->first(), 422);
            }
            $personalInformation = MntPersonalInformationUserModel::where('user_id', auth()->user()->id)->first();
            if ($personalInformation->image_url){
               Storage::disk('avatars')->delete($personalInformation->image_url);  // Borrar la imagen anterior si hay una existente
            } 
            $base64Image = $request->input('image');
           // return $base64Image;
            // Extraer el tipo de archivo desde el Base64
            $imageData = base64_decode($base64Image);
           
            $imageName = uniqid() . '.png';
        
            // Obtener la ruta completa usando Storage::path
            $filePath =  $imageName;
            $absolutePath = Storage::disk('avatars')->path($filePath);
        
            // Asegurarte de que la carpeta exista antes de guardar
            //Storage::disk('avatars')->makeDirectory('images');
        
            // Guardar la imagen en la ruta absoluta
            file_put_contents($absolutePath, $imageData);

             
            if($personalInformation){
                $personalInformation->image_url = $filePath;
                DB::commit();
                $personalInformation->save();
                return ApiResponse::success('Imagen guardada',200);
            }else{
                DB::commit();
                return ApiResponse::error('No se encontró información personal asociada al usuario', 404);
            }
        } catch (\Exception $e) {
            //throw $th;
            return ApiResponse::error($e->getMessage());
        }
    }
    public function updateDisplayName(UpdatePersonalInformationResquest $request){
        try {
            //code...
           
            return ApiResponse::success('Informacion personal actualizada',200);

        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
