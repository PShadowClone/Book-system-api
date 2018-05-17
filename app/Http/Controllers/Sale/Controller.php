<?php

namespace App\Http\Controllers\Sale;

use App\Cart;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{


    public $returnedResult = null;

    /**
     *
     * function adopts sale process
     *
     *
     * @param Request $request
     * @return $this|null
     */
    public function sale(Request $request)
    {
        $validations = Validator::make($request->all(), $this->rules(), $this->messages());
        if ($validations->fails())
            return error($validations->errors());
        $cart = Cart::with(['request'])->where(['client_id' => Auth::user()->id]);
        if ($library_id = $request->input('library_id'))
            $cart = $cart->where(['library_id' => $library_id]);
        try {
            $cart->get()->map(function ($item) use ($request) {
                $bookRequest = $item->request;
                if (!$bookRequest)
                    return $this->returnedResult = error(trans('lang.request_not_found'));
                if ($bookRequest->status == FOR_CONFIRMING)
                    return $this->returnedResult = error(trans('lang.request_not_confirmed_yet'));
                if ($bookRequest->status == CONFIRMED) {
                    $timeDifference = Carbon::parse($bookRequest->confirming_date)->diff(Carbon::parse($bookRequest->created_at))->m;
                    if ($timeDifference > MAX_DELAY_TIME) {
                        $bookRequest->status = FOR_CONFIRMING;
                        $bookRequest->update();
                        return $this->returnedResult = error(trans('lang.sales_exceed_the_delay_time'));
                    }
                }
                $bookRequest->quarter_id = $request->input('quarter_id');
                $bookRequest->delivery_time = $request->input('delivery_time');
                $bookRequest->promo_code = $request->input('promo_code');
                $bookRequest->longitude = $request->input('longitude');
                $bookRequest->latitude = $request->input('latitude');
                $bookRequest->update();
                $this->returnedResult = null;
                return $item;
            });
            if ($this->returnedResult != null)
                return $this->returnedResult;
            $this->sendCustomNotification($request);
            return success($cart);
        } catch (\Exception $exception) {
            return error(trans('lang.sale_error'));
        }
    }


    /**
     *
     * handel notification operations
     *
     *
     * @param Request $request
     */
    private function sendCustomNotification(Request $request)
    {
        $drivers = nearestDistances($request->input('latitude'), $request->input('longitude'), 'users where type = "' . DRIVER . '"', 5);
        $drivers = User::hydrate($drivers); // cast the given result into user objects
        $driversTokens = $drivers->pluck('token')->toArray(); // extract tokens form the collection of drivers
        notify($driversTokens, trans('lang.delivery_order'), trans('lang.new_delivery_order'), $request->all(), DELIVERY_NOTIFICATION);
    }

    /**
     *
     * validation's rules
     *
     *
     *
     * @return mixed
     */
    private function rules()
    {
        $rules['longitude'] = 'required|numeric';
        $rules['latitude'] = 'required|numeric';
        $rules['quarter_id'] = 'required|exists:quarters,id';
        $rules['delivery_time'] = 'required';
        return $rules;
    }

    /**
     *
     * validation's messages
     *
     *
     * @return array
     */
    private function messages()
    {
        return [
            'longitude.required' => trans('lang.longitude_required'),
            'longitude.numeric' => trans('lang.longitude_numeric'),
            'latitude.required' => trans('lang.latitude_required'),
            'latitude.numeric' => trans('lang.latitude_numeric'),
            'quarter_id.required' => trans('lang.quarter_id_required'),
            'quarter_id.numeric' => trans('lang.quarter_id_numeric'),
            'quarter_id.exists' => trans('lang.quarter_id_exists'),
            'delivery_time.required' => trans('lang.delivery_time_required')
        ];
    }
}
