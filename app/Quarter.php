<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Quarter extends Model
{
    public $table = 'quarters';
    public $primaryKey = 'id';
    public $fillable = ['name' , 'englishName' ,'cityId' , 'created_at' , 'updated_at'];
    public $dates = ['created_at' , 'updated_at'];

    public function city(){
        return $this->hasOne(City::class , 'id' , 'cityId');
    }

    public function area(){
        return $this->belongsTo(Area::class,'cities','id','cityId');
    }
}
