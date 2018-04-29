<?php

namespace App\Http\Controllers\City;

use App\City;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;

class Controller extends BaseController
{

    /**
     *
     * show all available cities or get city by it's id
     *
     * @param null $id
     * @return $this
     */
    public function show($id = null)
    {
        try {
            if ($id) {
                $city = City::find($id);
                return success($city);
            }
            $cities = City::all();
            return success($cities);
        } catch (\Exception $exception) {
            return error(trans('lang.city_show_error'));
        }
    }
}
