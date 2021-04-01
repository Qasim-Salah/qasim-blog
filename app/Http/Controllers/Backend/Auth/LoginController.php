<?php

namespace App\Http\Controllers\Backend\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminLoginRequest;


class LoginController extends Controller
{

    public function showLoginForm()
    {
        return view('backend.auth.login');
    }

    public function Login(AdminLoginRequest $request)
    {

        $remember_me = $request->has('remember') ? true : false;

        if (auth()->guard('admin')->attempt(['email' => $request->input("email"),
            'password' => $request->input("password")], $remember_me)) {
            return redirect()->route('admin.index');
        }
        return redirect()->back();
    }
    public function logout()
    {
        $guard = $this->getGaurd();
        $guard->logout();
        return redirect()->route('Frontend.index');
    }
    private function getGaurd()
    {
        return auth('admin');
    }
}
