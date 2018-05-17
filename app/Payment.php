<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public $table = 'payments';
    public $primaryKey = 'id';
    public $fillable = ['id', 'image', 'client_id' , 'request_id' , 'created_at', 'updated_at'];
}
