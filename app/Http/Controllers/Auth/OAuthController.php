<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    /**
     * Redirect to Google OAuth provider
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google callback
     */
    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Failed to authenticate with Google');
        }

        return $this->findOrCreateUser($user, 'google');
    }

    /**
     * Redirect to GitHub OAuth provider
     */
    public function redirectToGitHub()
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * Handle GitHub callback
     */
    public function handleGitHubCallback()
    {
        try {
            $user = Socialite::driver('github')->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Failed to authenticate with GitHub');
        }

        return $this->findOrCreateUser($user, 'github');
    }

    /**
     * Find or create user based on OAuth provider data
     */
    private function findOrCreateUser($oauthUser, $provider)
    {
        // Try to find user by OAuth ID
        $user = User::where("{$provider}_id", $oauthUser->getId())->first();

        if ($user) {
            // User exists with OAuth ID, log them in
            Auth::login($user, remember: true);
            return redirect('/home')->with('success', "Logged in via " . ucfirst($provider));
        }

        // Try to find user by email
        $user = User::where('email', $oauthUser->getEmail())->first();

        if ($user) {
            // User exists with email but no OAuth ID, link the account
            $user->update([
                "{$provider}_id" => $oauthUser->getId(),
                'oauth_provider' => $provider,
                'oauth_linked_at' => now(),
            ]);
            Auth::login($user, remember: true);
            return redirect('/home')->with('success', "{$provider} account linked to your existing account");
        }

        // Create new user
        $user = User::create([
            'name' => $oauthUser->getName() ?? $oauthUser->getNickname(),
            'email' => $oauthUser->getEmail(),
            'password' => bcrypt(uniqid()), // Random password for OAuth users
            "{$provider}_id" => $oauthUser->getId(),
            'oauth_provider' => $provider,
            'oauth_linked_at' => now(),
            'email_verified_at' => now(), // Auto-verify email from OAuth providers
        ]);

        Auth::login($user, remember: true);
        return redirect('/home')->with('success', "Welcome! Logged in via " . ucfirst($provider));
    }
}
