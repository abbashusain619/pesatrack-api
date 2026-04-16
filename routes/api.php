<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::post('/register', function (Request $request) {
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'base_currency' => $request->base_currency ?? 'USD',
    ]);
    $token = $user->createToken('auth')->plainTextToken;
    return response()->json(['user' => $user, 'token' => $token]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) { return $request->user(); });
    
    // Account routes
    Route::apiResource('accounts', App\Http\Controllers\Api\AccountController::class)
        ->names('api.accounts');

    // Networth routes
    Route::get('/networth', [App\Http\Controllers\Api\NetWorthController::class, 'index']);

    // Transaction routes
    Route::apiResource('transactions', App\Http\Controllers\Api\TransactionController::class)
        ->names('api.transactions');
});