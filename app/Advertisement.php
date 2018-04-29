<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    public $table = "advertisements";
    public $fillable = ['content' ,'arrange' ,'image' , 'contact_phone' ,'start_publish' , 'end_publish' , 'created_at' , 'updated_at'];
    public $primaryKey = "id";


}
