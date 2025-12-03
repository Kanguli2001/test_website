<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResendVerificationEmail extends Controller
{
    /**
     * Resend the email verification notification.
     */
    public function __invoke(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended('/');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Verification link sent!');
    }
}
