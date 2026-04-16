@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-md">
    <h1 class="text-2xl font-bold mb-4">Add Transaction</h1>
    <form method="POST" action="{{ route('transactions.store') }}" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
        @csrf
        <div class="mb-4">
            <label class="block mb-1">Account</label>
            <select name="account_id" class="w-full border rounded px-3 py-2" required>
                <option value="">Select account</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->currency }})</option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block mb-1">Type</label>
            <select name="type" class="w-full border rounded px-3 py-2" required>
                <option value="income">Income</option>
                <option value="expense">Expense</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="block mb-1">Amount</label>
            <input type="number" step="0.01" name="amount" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1">Description (optional)</label>
            <input type="text" name="description" class="w-full border rounded px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block mb-1">Reference (optional)</label>
            <input type="text" name="reference" class="w-full border rounded px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block mb-1">Transaction Date</label>
            <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" class="w-full border rounded px-3 py-2">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
        <a href="{{ route('transactions.index') }}" class="ml-2 text-gray-600">Cancel</a>
    </form>
</div>
@endsection