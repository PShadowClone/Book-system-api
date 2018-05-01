<?php

namespace App\Http\Controllers\Cart;

use App\Cart;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use MongoDB\Driver\Exception\ExecutionTimeoutException;

class Controller extends BaseController
{


    public function show()
    {
        try {
            $cart = Cart::with(['library', 'book'])->where(['client_id' => Auth::user()->id])->get()->map(function ($item) {
                $item['date'] = explode(' ', $item->created_at)[0];
                $item->book['image'] = env('ASSETS_URL') . $item->book['image'];
                return $item;
            });
            return success($cart);
        } catch (\Exception $exception) {
            return error('lang.cart_show_error');
        }
    }
}
