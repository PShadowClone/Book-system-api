<?php

namespace App\Http\Controllers\Quarter;

use App\Quarter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;

class Controller extends BaseController
{
    public function show(Request $request, $id = null)
    {
        try {
            if ($cityId = $request->input('city_id')) {
                $quarters = Quarter::where('cityId', '=', $cityId)->get();
                return success($quarters);
            }
            if ($id) {
                $quarter = Quarter::find($id);
                return success($quarter);
            }
            $quarters = Quarter::all();
            return success($quarters);
        } catch (\Exception $exception) {
            return error(trans('lang.quarter_show_error'));
        }

    }
}
