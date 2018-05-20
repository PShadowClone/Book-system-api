<?php

namespace App\Http\Controllers\Payment;

use App\Payment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{

    /**
     *
     *
     * upload the bill of payment
     *
     * @note : there is no control for payment in control panel
     *
     *
     * @param Request $request
     * @return $this
     */
    public function store(Request $request)
    {
        $validations = Validator::make($request->all(), $this->rules(), $this->messages());
        if ($validations->fails())
            return error($validations->errors());
        try {
            $image = image_upload($request->file('image'), PAYMENTS_DIR);
            if ($image['status'] == SERVER_ERROR)
                return error(trans('lang.image_uploaded_empty'));
            $payment = Payment::create(['image' => $image['data'], 'request_id' => $request->input('request_id'), 'client_id' => Auth::user()->id]);
            $payment['image'] = URL::to('/') . $payment->image;
            return success($payment);
        } catch (\Exception $exception) {
            return error(trans('lang.image_uploaded_error'));
        }
    }

    /**
     *
     *
     * validation rules
     *
     *
     * @return array
     */
    private function rules()
    {
        return [
            'image' => 'required',
            'request_id' => 'required|exists:requests,id'
        ];
    }

    /**
     *
     * validation messages
     *
     *
     * @return array
     */
    private function messages()
    {
        return [
            'image.required' => trans('lang.image_required'),
            'request_id.required' => trans('lang.request_id_required'),
            'request_id.exists' => trans('lang.request_id_exists'),

        ];
    }
}
