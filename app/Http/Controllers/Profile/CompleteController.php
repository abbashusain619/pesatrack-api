<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CompleteController extends Controller
{
    public function show()
    {
        if (!Session::has('google_user')) {
            return redirect('/login');
        }

        $countries = [
            'TZ' => 'Tanzania',
            'KE' => 'Kenya',
            'UG' => 'Uganda',
            'RW' => 'Rwanda',
            'BI' => 'Burundi',
            'SS' => 'South Sudan',
            'CD' => 'DR Congo',
            // add more as needed
        ];

        return view('auth.complete-profile', compact('countries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'country' => 'required|string|size:2',
        ]);

        $googleData = Session::get('google_user');
        if (!$googleData) {
            return redirect('/login');
        }

        $user = User::create([
            'name' => $googleData['name'],
            'email' => $googleData['email'],
            'google_id' => $googleData['google_id'],
            'country' => $request->country,
            'password' => bcrypt(uniqid()), // dummy password, user will use Google only
            'email_verified_at' => now(),
        ]);

        Auth::login($user, true);
        Session::forget('google_user');

        return redirect('/dashboard')->with('success', 'Profile completed! You can now add accounts.');
    }
}