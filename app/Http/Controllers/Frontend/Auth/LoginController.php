<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

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
    protected $redirectTo = '/dashboard';



    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('frontend.auth.login');
    }

    public function username()
    {
        return 'username';
    }

    protected function authenticated(Request $request, $user)
    {

        if ($user->status == 1) {
            $role = Auth::user()->role_id;
            if ($role <= 2) {
                return redirect()->route('admin.index')->with([
                    'message' => 'Logged in successfully.',
                    'alert-type' => 'success'
                ]);
            } else {
                return redirect()->route('users.dashboard')->with([
                    'message' => 'Logged in successfully.',
                    'alert-type' => 'success'
                ]);
            }
        }
        return redirect()->route('frontend.index')->with([
            'message' => 'Please contact Bloggi Admin.',
            'alert-type' => 'warning'
        ]);


    }


}
