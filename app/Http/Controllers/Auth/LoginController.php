<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

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
        if(!file_exists(storage_path()."/installed")){
            header('location:install');
            die;
        }
        $this->middleware('guest')->except('logout');
        $this->middleware('guest:client')->except(['logout']);
    }

    public function showClientLoginForm($lang = NULL)
    {
        if($lang){
            \App::setLocale($lang);
        }
        return view('auth.client_login');
    }
    public function clientLogin(Request $request)
    {
        $this->validate($request, [
            'email'   => 'required|email',
            'password' => 'required|min:6'
        ]);

        if (\Auth::guard('client')->attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))) {
            return redirect()->route('client.home');
        }
        return $this->sendFailedLoginResponse($request);
    }


    protected function authenticated(Request $request, $user)
    {
        if(!\Auth::guard('client')->check()) {
            return redirect('/check');
        }
    }
    public function showLoginForm($lang = NULL)
    {
        if($lang){
            \App::setLocale($lang);
        }
        return view('auth.login');
    }
}
