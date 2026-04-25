@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Transactions</h1>
        <a href="{{ route('transactions.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">+ Add Transaction</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4">{{ session('success') }}</div>
    @endif

    <!-- FILTER FORM (inserted here) -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow mb-6">
        <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" name="search" placeholder="Search description/reference" value="{{ request('search') }}" class="border rounded px-3 py-2">
            <input type="date" name="from_date" placeholder="From date" value="{{ request('from_date') }}" class="border rounded px-3 py-2">
            <input type="date" name="to_date" placeholder="To date" value="{{ request('to_date') }}" class="border rounded px-3 py-2">
            <select name="category_id" class="border rounded px-3 py-2">
                <option value="">All Categories</option>
                <option value="null" {{ request('category_id') == 'null' ? 'selected' : '' }}>Uncategorized</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="account_id" class="border rounded px-3 py-2">
                <option value="">All Accounts</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                @endforeach
            </select>
            <select name="type" class="border rounded px-3 py-2">
                <option value="">All Types</option>
                <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Income</option>
                <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expense</option>
            </select>
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
                <a href="{{ route('transactions.index') }}" class="bg-gray-300 text-gray-800 px-4 py-2 rounded">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left">Date</th>
                    <th class="px-6 py-3 text-left">Account</th>
                    <th class="px-6 py-3 text-left">Category</th>
                    <th class="px-6 py-3 text-left">Type</th>
                    <th class="px-6 py-3 text-left">Description</th>
                    <th class="px-6 py-3 text-right">Amount</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                <tr class="border-t">
                    <td class="px-6 py-4">{{ $tx->transaction_date->format('Y-m-d') }}</td>
                    <td class="px-6 py-4">{{ $tx->account->name }}</td>
                    <td class="px-6 py-4">
                        @if($tx->category)
                            <span style="background-color: {{ $tx->category->color }}22; color: {{ $tx->category->color }};" 
                                  class="px-2 py-1 rounded-full text-xs font-semibold">
                                {{ $tx->category->icon ?? '' }} {{ $tx->category->name }}
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">Uncategorised</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">{{ ucfirst($tx->type) }}</td>
                    <td class="px-6 py-4">{{ $tx->description ?? '—' }}</td>
                    <td class="px-6 py-4 text-right {{ $tx->type == 'income' ? 'text-green-600' : 'text-red-600' }}">
                        {{ $tx->type == 'income' ? '+' : '-' }}{{ number_format($tx->amount, 2) }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a href="{{ route('transactions.edit', $tx) }}" class="text-blue-600 hover:underline mr-2">Edit</a>
                        <form action="{{ route('transactions.destroy', $tx) }}" method="POST" class="inline" onsubmit="return confirm('Delete this transaction?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No transactions found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4">
            {{ $transactions->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection