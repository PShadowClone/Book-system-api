<?php

namespace App\Http\Controllers\Request;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{
    public function store(Request $request)
    {
        $validations = Validator::make($request->all(), $this->rules(), $this->messages());
        if ($validations) {
            return error($validations->errors());
        }
        dd($request->all());
    }


    private function rules()
    {
        return [
            'book_id' => 'required|exists:books,id',
            'amount' => 'required|numeric',
            'delivery_time' => 'required',
            'quarter_id' => 'required|exists:quarters,id'
        ];
    }

    private function messages()
    {
        return [
            'book_id.required' => trans('lang.book_id_required'),
            'book_id.exists' => trans('lang.book_id_exists'),
            'amount.required' => trans('lang.amount_required'),
            'amount.numeric' => trans('lang.amount_numeric'),
            'delivery_time.required' => trans('lang.delivery_time_required'),
            'quarter_id.required' => trans('lang.quarter_id_required'),
            'quarter_id.exists' => trans('lang.quarter_id_exists'),
        ];
    }
}
