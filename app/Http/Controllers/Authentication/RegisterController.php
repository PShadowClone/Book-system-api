<?php

namespace App\Http\Controllers\Authentication;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{

    /**
     * register new client
     *
     * @param Request $request
     * @return $this
     */
    public function create(Request $request)
    {

        $validator = Validator::make($request->all(), $this->rules(), $this->messages());
        if ($validator->fails())
            return error($validator->errors());
        try {
            $user = new User();
            $user->fill($request->all());
            $user->type = CLIENT;
            $user->save();
            unset($user->password); // hide user's password from the returned response
            return success($user);
        } catch (\Exception $exception) {
            return error(trans('created_error'));
        }
    }


    /**
     *
     * validation rules
     *
     * @return array
     */
    private function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string',
            'phone' => 'required|numeric|digits_between:1,15|regex:/^\d+$/'
        ];
    }

    /**
     *
     * validation messages
     *
     * @return array
     *
     */
    private function messages()
    {
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
