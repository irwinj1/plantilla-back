<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MntPersonalInformationUserModel extends Model
{
    //
    protected $table = 'mnt_personal_information_user';
    protected $guarded = [];

    public function user(){
        return $this->belongsTo(User::class);
    }

}
