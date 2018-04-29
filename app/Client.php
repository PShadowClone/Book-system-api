<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    public $table = 'clients';
    protected $fillable = ['id', 'username', 'phone', 'name', 'email', 'password', 'status', 'type', 'single_company', 'instRate', 'total_profits', 'company_id', 'remember_token', 'token'
    ];
}
