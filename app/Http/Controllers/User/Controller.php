<?php

namespace App\Http\Controllers\User;

use App\Library;
use App\User;
use App\UserEvaluations;
use App\Request as BookRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{

    /**
     *
     * returns user's info for updating.
     *
     * @return $this
     */
    public function edit()
    {
        try {
            $user = Auth::user();
            return success($user);
        } catch (\Exception $exception) {
            return error(trans('lang.show_profile_error'));
        }
    }

    /**
     *
     * update user's info
     *
     * @param Request $request
     * @return $this
     *
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules(), $this->messages());
        if ($validator->fails()) {
            return error($validator->errors());
        }
        try {
            $user = User::find(Auth::user()->id);
            $user->fill($request->all());
            $user->updated_at = Carbon::now();
            $user->update();
            return success($user);
        } catch (\Exception $exception) {
            return error(trans('lang.update_error'));
        }
    }


    /**
     *
     *  makes client evaluates driver
     * if client has been evaluated driver for the same request before, evaluation will be rejected
     *
     * @param Request $request
     * @return $this
     */
    public function evaluate(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules(2), $this->messages(2));
        if ($validator->fails()) {
            return error($validator->errors());
        }
        try {
            $bookRequest = BookRequest::find($request->input('request_id'));
            $book = $bookRequest->book()->first();
            if (!$book) {
                return error(trans('lang.book_not_found'));
            }
            $evaluation = UserEvaluations::where(['client_id' => Auth::user()->id, 'driver_id' => $request->input('driver_id'), 'request_id' => $request->input('request_id')])->first();
            if ($evaluation)
                return error(trans('lang.user_evaluated_before'));
            $request['book_id'] = $book->id;
            $request['client_id'] = Auth::user()->id;
            $evaluation = UserEvaluations::create($request->all());
            unset($evaluation->client_id); // hide client's id
            return success($evaluation);
        } catch (\Exception $exception) {
            return error(trans('lang.user_evaluation_error'));
        }
    }

    /**
     *
     * update user's token
     *
     * @if user instanceof LIBRARY
     *      LIBRARY's token will be updated for library
     * @else
     *      update user's token
     *
     * @param Request $request
     * @return $this
     */
    public function updateToken(Request $request)
    {
        $user = Auth::user();
        if ($request->input('provider') == 'LIBRARY') {
            $user = Library::find(Auth::user()->id);
        }
        $validator = Validator::make($request->all(), ['token' => 'required'], ['token.required' => trans('lang.token_required')]);
        if ($validator->fails()) {
            return error($validator->errors());
        }
        try {
            $user->token = $request->input('token');
            $user->update();
            return success(trans('lang.token_updated_successfully'));
        } catch (\Exception $exception) {
            return error(trans('lang.token_updated_error'));
        }
    }

    /**
     *
     * validation rules
     *
     * @return array
     */
    private function rules($function_type = 1)
    {
        if ($function_type == 2) {
            return [
                'driver_id' => 'required|exists:drivers,id',
                'evaluate' => 'required|integer|between:0,5',
                'request_id' => 'required'
            ];
        }
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::user()->id,
            'password' => 'required|string',
            'phone' => 'required|numeric|digits_between:1,15|regex:/^\d+$/|unique:users,phone,' . Auth::user()->id
        ];
    }

    /**
     *
     * validation messages
     *
     * @return array
     *
     */
    private function messages($function_type = 1)
    {
        if ($function_type == 2) {
            return [
                'driver_id.required' => trans('lang.driver_id_required'),
                'driver_id.exists' => trans('lang.driver_id_exists'),
                'evaluate.required' => trans('lang.evaluation_required'),
                'evaluate.between' => trans('lang.evaluation_between'),
                'evaluate.integer' => trans('lang.evaluation_integer'),
                'request.required' => trans('lang.request_not_found')
            ];
        }
        return [
            'name.required' => trans('lang.name_required'),
            'name.string' => trans('lang.name_string'),
            'name.max' => trans('lang.name_max'),
            'email.required' => trans('lang.email_required'),
            'email.string' => trans('lang.email_string'),
            'email.email' => trans('lang.email_email'),
            'email.max' => trans('lang.email_max'),
            'email.unique' => trans('lang.email_unique'),
            'password.required' => trans('lang.password_required'),
            'password.string' => trans('lang.password_string'),
            'phone.required' => trans('lang.phone_required'),
            'phone.numeric' => trans('lang.phone_numeric'),
            'phone.digits_between' => trans('lang.phone_digits_between'),
            'phone.regex' => trans('lang.phone_regex')
        ];
    }
}
