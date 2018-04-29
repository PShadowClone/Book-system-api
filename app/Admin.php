<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Admin extends Authenticatable
{


    public $table = 'admins';
    public $primaryKey = 'id';
    public $fillable = ['id','name' , 'email' , 'type' , 'username'  ,'password'];
    public $hidden = ['password'];
}
