<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\HelperController;
use App\Library;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    public function show()
    {
        $source = Auth::user()->latitude.",".Auth::user()->longitude;
        $destinations =Library::all(['latitude','longitude'])->toArray();
//        $result = HelperController::getDistance($source,$destinations);

        dd(getDistance($source ,$destinations));
    }
}
