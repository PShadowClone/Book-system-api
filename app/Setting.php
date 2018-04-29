<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{

    public $table = 'settings';
    public $fillable = ['in_city', 'out_city'];
}
