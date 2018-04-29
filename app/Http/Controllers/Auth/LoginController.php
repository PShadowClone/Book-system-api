<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(\Illuminate\Http\Request $request){
        if(Auth::attempt(['email' => $request->input('username') , 'password' => $request->input('password')])){
            $user = Auth::user();
            $user['token']=  $user->createToken(project_name())->accessToken;
            unset($user['id']);
        }
    }
}
