<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    public $table = 'cities';
    public $primaryKey = 'id';
    public $fillable = ['name' , 'englishName' ,'area_id' , 'created_at' , 'updated_at'];
    public $dates = ['created_at' , 'updated_at'];

    public function area(){
        return $this->hasOne(Area::class , 'id' , 'area_id');
    }


}
