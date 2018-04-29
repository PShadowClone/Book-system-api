<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Library extends Model
{
    public $table = 'libraries';
    public $primaryKey = 'id';
    public $fillable = ['id', 'name', 'status', 'phone', 'mobile', 'password', 'email', 'address', 'instProfitRate', 'longitude', 'latitude', 'quarter_id', 'token'];
    public $hidden = ['password'];
    public $dates = ['created_at', 'updated_at', 'deleted_at'];


    /**
     *
     * get all library's books
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function books()
    {
        return $this->hasMany(Book::class, 'library_id', 'id');
    }

    /**
     * @return mixed
     */
    public function city()
    {
        return $this->quarter->city;
    }

    /**
     * returns the quarter of library
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function quarter()
    {
        return $this->hasOne(Quarter::class, 'id', 'quarter_id');
    }

    /**
     * returns all libraries according to city
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function byCity($id)
    {
        $libraries = DB::table('libraries')
            ->join('quarters', 'quarters.id', '=', 'libraries.quarter_id')
            ->join('cities', 'cities.id', '=', 'quarters.cityId')
            ->where('cities.id', '=', $id)
            ->select(['Libraries.*']);
        return $libraries;
    }

    /**
     *
     * returns all libraries according to area and city (if the last one exists)
     * @param $id
     * @param $city_id
     * @return mixed
     */

    public static function byArea($id, $city_id)
    {

        $libraries = DB::table('libraries')
            ->join('quarters', 'quarters.id', '=', 'libraries.quarter_id')
            ->join('cities', 'cities.id', '=', 'quarters.cityId')
            ->join('areas as area', 'area.id', '=', 'cities.area_id')
            ->where('area.id', '=', $id);
        if ($city_id != -1) {
            $libraries = $libraries->where('cities.id', '=', $city_id);
        }
        $libraries->select(['Libraries.*']);
        return $libraries;
    }
}
