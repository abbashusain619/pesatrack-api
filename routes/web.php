<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\TransactionController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Profile\CompleteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard route - using your custom controller
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// Authenticated routes for profile, accounts, transactions
Route::middleware('auth')->group(function () {
    // Profile routes (from Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/currency', [ProfileController::class, 'updateCurrency'])->name('profile.currency');

    // Account management (web)
    Route::resource('accounts', AccountController::class);

    // Transaction management (web)
    Route::resource('transactions', TransactionController::class);

    // Budget Route
    Route::resource('budgets', App\Http\Controllers\Web\BudgetController::class);
    // Categories Route
    Route::resource('categories', App\Http\Controllers\Web\CategoryController::class);

    // Reports
    Route::get('/reports', [App\Http\Controllers\Web\ReportController::class, 'index'])->name('reports.index');

    Route::get('/reports/data', [App\Http\Controllers\Web\ReportController::class, 'data'])->name('reports.data');

    Route::get('/reports/networth-data', [App\Http\Controllers\Web\ReportController::class, 'networthData'])->name('reports.networth');

    Route::get('/reports/export', [App\Http\Controllers\Web\ReportController::class, 'export'])->name('reports.export');

    // Recurring Transactions
    Route::resource('recurring', App\Http\Controllers\Web\RecurringTransactionController::class);
    Route::get('/pending', [App\Http\Controllers\Web\RecurringTransactionController::class, 'pending'])->name('recurring.pending');
    Route::patch('/pending/{transaction}/confirm', [App\Http\Controllers\Web\RecurringTransactionController::class, 'confirm'])->name('recurring.confirm');
    Route::patch('/pending/{transaction}/reject', [App\Http\Controllers\Web\RecurringTransactionController::class, 'reject'])->name('recurring.reject');
    
    // Mock Route
    Route::get('/test-balance', function () {
        $provider = app(\App\Services\PaymentProviders\AccountProviderInterface::class);
        return response()->json($provider->getBalance('255712345678'));
    });
});

// Guest routes (login, register, password reset are in auth.php)
Route::middleware('guest')->group(function () {
    // Profile completion after Google login
    Route::get('complete-profile', [CompleteController::class, 'show'])->name('profile.complete');
    Route::post('complete-profile', [CompleteController::class, 'store']);

    // Google OAuth routes
    Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
});

require __DIR__.'/auth.php';