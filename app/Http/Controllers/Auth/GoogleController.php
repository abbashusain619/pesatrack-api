<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        \Log::info('1. Google callback started');
        try {
            $googleUser = Socialite::driver('google')->user();
            \Log::info('2. Google user fetched: ' . $googleUser->getEmail());
        } catch (\Exception $e) {
            \Log::error('3. Exception: ' . $e->getMessage());
            return redirect('/login')->withErrors('Google auth failed: ' . $e->getMessage());
        }

        // Check if user already exists by google_id or email
        $user = User::where('google_id', $googleUser->getId())
                    ->orWhere('email', $googleUser->getEmail())
                    ->first();

        if ($user) {
            \Log::info('4. Existing user found: ' . $user->email);
            // Ensure google_id and email_verified_at are set
            if (!$user->google_id) {
                $user->google_id = $googleUser->getId();
            }
            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
            }
            $user->save();
            \Log::info('5. Existing user updated, logging in');
            Auth::login($user);
            \Log::info('6. Redirecting existing user to dashboard');
            return redirect()->intended('/dashboard');
        }

        // New user: store Google data in session, redirect to complete profile
        \Log::info('7. New user, storing session and redirecting to profile completion');
        Session::put('google_user', [
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
        ]);

        return redirect()->route('profile.complete');
    }
}