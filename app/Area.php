<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
   public $table = 'areas';
   public $primaryKey = 'id';
   public $fillable = ['name' , 'englishName' , 'created_at' , 'updated_at'];
   public $dates = ['created_at' , 'updated_at'];
}
