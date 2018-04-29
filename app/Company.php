<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    public $table= 'companies';
    public $primaryKey = 'id';
    public $fillable = ['id' ,'email' , 'address','phone' , 'created_at' , 'updated_at'];
    public $dates = ['created_at' , 'updated_at'];
}
