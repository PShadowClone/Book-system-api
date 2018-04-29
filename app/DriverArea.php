<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DriverArea extends Model
{
    public $table = 'user_areas';
    public $primaryKey = 'id';
    public $fillable = ['user_id' , 'quarter_id' , 'created_at' , 'updated_at'];
    public $dates = ['created_at' , 'updated_at'];

    public function user(){
        return $this->hasOne(User::class , 'id' , 'user_id');
    }

    public function quarter(){
        return $this->hasOne(Quarter::class , 'id' , 'quarter_id');
    }
}
