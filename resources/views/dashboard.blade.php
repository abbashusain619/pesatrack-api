@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Welcome, {{ Auth::user()->name }}</h1>

    <div class="grid grid-cols-3 md:grid-cols-3 gap-2 mb-4">
        <!-- Net worth card with currency selector -->
        <div class="bg-white dark:bg-gray-400 rounded-lg shadow p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-xl font-semibold mb-2">Net Worth</h2>
                    <p class="text-3xl font-bold text-green-600">{{ number_format($netWorth, 2) }} {{ $displayCurrency }}</p>
                </div>
                <div class="w-48">
                    <form method="GET" action="{{ route('dashboard') }}" id="currency-form">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-800 mb-1">View in:</label>
                        <select name="currency" id="currency-select" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-800 rounded-md shadow-sm">
                            @foreach($currencies as $currency)
                                <option value="{{ $currency->code }}" {{ $displayCurrency == $currency->code ? 'selected' : '' }}>
                                    {{ $currency->code }} - {{ $currency->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
            @if(count($conversionErrors)) //this is line 28
                <p class="text-sm text-red-500 mt-2">⚠️ {{ implode(', ', $conversionErrors) }}</p>
            @endif
        </div>

        <!-- Accounts summary card -->
        <div class="bg-white dark:bg-gray-400 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-2">Accounts</h2>
            <ul>
                @foreach($accounts as $account)
                    <li class="flex justify-between border-b py-1">
                        <span>{{ $account->name }} ({{ $account->currency }})</span>
                        <span class="font-mono">{{ number_format($account->balance, 2) }}</span>
                    </li>
                @endforeach
            </ul>
            <a href="{{ route('accounts.index') }}" class="text-blue-600 hover:underline text-sm mt-2 inline-block">Manage accounts →</a>
        </div>

        <!-- Budget summary card -->
        <div class="bg-white dark:bg-gray-400 rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-2">
                <h2 class="text-xl font-semibold">Budget Overview</h2>
                <a href="{{ route('budgets.create') }}" class="text-blue-800 hover:underline text-sm">+ Add Budget</a>
            </div>
            @if(isset($budgets) && $budgets->count())
                <div class="space-y-3">
                    @foreach($budgets->take(3) as $budget)
                        <div>
                            <div class="flex justify-between text-sm">
                                <span>{{ $budget->category->name }}</span>
                                <span>{{ number_format($budget->spent, 2) }} / {{ number_format($budget->amount, 2) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="rounded-full h-2" 
                                    style="width: {{ min($budget->percentage, 100) }}%; background-color: 
                                            {{ $budget->percentage < 80 ? '#10b981' : ($budget->percentage < 100 ? '#eab308' : '#ef4444') }};">
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @if($budgets->count())
                        <div class="text-left"><a href="{{ route('budgets.index') }}" class="text-blue-600 hover:underline text-sm">View all budgets →</a></div>
                    @endif
                </div>
            @else
                <p class="text-gray-500">No budgets set. <a href="{{ route('budgets.create') }}" class="text-blue-600">Create one</a> to track spending.</p>
            @endif
        </div>

    </div>

    <!-- Recent transactions -->
    <div class="bg-white dark:bg-gray-400 rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Recent Transactions</h2>
            <a href="{{ route('transactions.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded text-sm">+ New Transaction</a>
        </div>
        @if($recentTransactions->count())
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2">Date</th>
                        <th class="text-left py-2">Account</th>
                        <th class="text-left py-2">Description</th>
                        <th class="text-right py-2">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTransactions as $tx)
                        <tr class="border-b">
                            <td class="py-2">{{ $tx->transaction_date->format('Y-m-d') }}</td>
                            <td class="py-2">{{ $tx->account->name }}</td>
                            <td class="py-2">{{ $tx->description ?? '—' }}</td>
                            <td class="py-2 text-right {{ $tx->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $tx->type === 'income' ? '+' : '-' }}{{ number_format($tx->amount, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <a href="{{ route('transactions.index') }}" class="text-blue-600 hover:underline text-sm mt-4 inline-block">View all transactions →</a>
        @else
            <p class="text-gray-500">No transactions yet.</p>
        @endif
    </div>
</div>

<script>
    document.getElementById('currency-select').addEventListener('change', function() {
        document.getElementById('currency-form').submit();
    });
</script>
@endsection