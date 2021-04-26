<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Validation\ValidationException;
use Auth;
use Route;
class AdminLoginController extends Controller
{
    //
    use AuthenticatesUsers;

    protected $redirectTo = "admin/dashboard";
    public function __construct()
    {
        $this->middleware('guest:admin')->except('logout');
    }

    public function showLoginForm()
    {
      return view('admin.auth.login');
    }

    public function login(Request $request)
    {
      // Validate the form data
      $this->validate($request, [
        'email'   => 'required',
        'password' => 'required|min:6'
      ]);

      // Attempt to log the user in
      if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {
        // if successful, then return success
        //return redirect('admin/dashboard');
        return redirect()->intended('admin/dashboard');

      }
      return $this->sendFailedLoginResponse($request);
    //   $errors = new MessageBag(['password' => ['Email and/or password invalid.']]);
    //   return back()->withInput($request->only('email', $request->remember))->withErrors([
    //     $this->username() => $errors,
    // ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        return $this->loggedOut($request) ?: redirect('admin/login');
    }
}
