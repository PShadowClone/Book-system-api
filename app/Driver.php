<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * it is used for view nothing else , insertion operation done using User::class
 * Class Driver
 * @package App
 */
class Driver extends Model
{
   public $table = 'drivers';
   public $fillable = ['id' , 'name' , 'email', 'phone', 'password' , 'instRate','total_profits' ,'company_id' , 'created_at' , 'updated_at' , 'status','single_company' , 'remember_token' , 'token'];
   public $hidden = ['remember_token' , 'token'];
   public $dates = ['created_at' , 'updated_at'];

    /**
     * get user company
     * @if $type == 2
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function company(){
        return $this->hasOne(Company::class, 'id' , 'company_id');
    }


    public function evaluations(){
        return $this->hasMany(UserEvaluations::class , 'driver_id' , 'id');
    }


    static function byQuarter($id = null){
        $libraries  = DB::table('drivers')
            ->join('user_areas','user_areas.user_id' , '=' , 'drivers.id')
            ->join('quarters','quarters.id' , '=' , 'user_areas.quarter_id')
            ->where('quarters.id' , '=' , $id)
            ->select(['drivers.*']);
        return $libraries;
    }


    static function byCity($id = null){
        $libraries  = DB::table('drivers')
            ->join('user_areas','user_areas.user_id' , '=' , 'drivers.id')
            ->join('quarters','quarters.id' , '=' , 'user_areas.quarter_id')
            ->join('cities','cities.id' , '=' , 'quarters.cityId')
            ->where('cities.id' , '=' , $id)
            ->select(['drivers.*']);
        return $libraries;
    }



    static function byArea($id = null){
        $libraries  = DB::table('drivers')
            ->join('user_areas','user_areas.user_id' , '=' , 'drivers.id')
            ->join('quarters','quarters.id' , '=' , 'user_areas.quarter_id')
            ->join('cities','cities.id' , '=' , 'quarters.cityId')
            ->join('areas','areas.id' , '=' , 'cities.area_id')
            ->where('areas.id' , '=' , $id)
            ->select(['drivers.*']);
        return $libraries;
    }
}
