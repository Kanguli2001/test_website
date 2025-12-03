<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;

class Logout extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {

        Auth::logout();


        //Invalidate session

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Explicitly forget the remember cookie
        Cookie::queue(Cookie::forget(Auth::getRecallerName()));

        return redirect('/')->with('status', 'Logged out successfully!');
    }
}
