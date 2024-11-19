<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePersonalInformationRequest;
use App\Http\Response\ApiResponse;
use App\Models\MntPersonalInformationUserModel;
use DB;
use Illuminate\Http\Request;

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
}
