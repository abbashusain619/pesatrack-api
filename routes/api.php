<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebhookLoggerController;
use Illuminate\Support\Facades\Auth;

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

Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');
    
    if (!Auth::attempt($credentials)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }
    
    $user = Auth::user();
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

    // Budgets
    Route::apiResource('budgets', App\Http\Controllers\Api\BudgetController::class)
        ->names('api.budgets');

    // Categories
    Route::apiResource('categories', App\Http\Controllers\Api\CategoryController::class)
        ->names('api.categories');

    // Reports
    Route::get('/reports', [App\Http\Controllers\Api\ReportController::class, 'data']);
    Route::get('/reports/export', [App\Http\Controllers\Api\ReportController::class, 'export']);

    // Recurring Transactions
   Route::apiResource('recurring', App\Http\Controllers\Api\RecurringTransactionController::class)
    ->names('api.recurring');
    Route::get('/recurring/pending', [App\Http\Controllers\Api\RecurringTransactionController::class, 'pending']);
    Route::patch('/recurring/pending/{transaction}/confirm', [App\Http\Controllers\Api\RecurringTransactionController::class, 'confirm']);
    Route::patch('/recurring/pending/{transaction}/reject', [App\Http\Controllers\Api\RecurringTransactionController::class, 'reject']);
});

// Webhook logger route (no authentication)
Route::post('/webhooks/{provider?}', WebhookLoggerController::class);