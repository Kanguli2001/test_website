<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Login extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        //validate the input
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        //Attempt to login in the user
        if (Auth::attempt($credentials, $remember)) {

            //Regenerate the session to prevent fixation attacks
            $request->session()->regenerate();

            //Redirect to intended page
            return redirect()->intended('/')->with('status', 'Logged in successfully!');
        }

        //If login Fails redirect back with error
        return back()->withErrors(['email' => 'The provided credentials do not match our records.'])->onlyInput('email');

    }
}
