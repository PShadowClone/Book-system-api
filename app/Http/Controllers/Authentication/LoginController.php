<?php

namespace App\Http\Controllers\Authentication;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

    /**
     *
     * adopts login processes
     *
     * ***********************
     * login can be done using
     * client's email or even
     * client's phone number
     * ***********************
     *
     *
     * @param Request $request
     * @return $this
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules(), $this->messages());
        if ($validator->fails())
            return error($validator->errors());
        if ($user = User::where(['email' => $request->input('username') , 'password' => $request->input('password')])->first()) {
            Auth::login($user);
            $user = Auth::user();
            $data['token'] = $user->createToken(project_name())->accessToken;
            return success($data);
        } else if ($user = User::where(['phone' => $request->input('username') , 'password' => $request->input('password')])->first()) {
            Auth::login($user);
            $user = Auth::user();
            $data['token'] = $user->createToken(project_name())->accessToken;
            return success($data);
        }
        return error(trans('lang.username_password_wrong'));

    }

    /**
     * login validation rules
     *
     * @return array
     */
    private function rules()
    {
        return [
            'username' => 'required',
            'password' => 'required'
        ];
    }

    /**
     * login validation messages
     *
     * @return array
     */
    private function messages()
    {
        return [
            'username.required' => trans('lang.username_password_required'),
            'password.required' => trans('lang.username_password_required')
        ];
    }
}
