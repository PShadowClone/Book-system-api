<?php

namespace App\Http\Controllers\Notification;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{


    public function updateToken(Request $request)
    {
        $userId = Auth::user()->id;
//        User::find();
    }
}
