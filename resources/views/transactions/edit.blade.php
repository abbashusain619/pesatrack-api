@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-md">
    <h1 class="text-2xl font-bold mb-4">Edit Transaction</h1>
    <form method="POST" action="{{ route('transactions.update', $transaction) }}" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
        @csrf
        @method('PUT')

        @if($transaction->is_synced)
            <div class="mb-4 bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded border border-yellow-200">
                <p class="text-yellow-800 dark:text-yellow-200 text-sm">
                    ℹ️ This transaction was imported automatically. You can only change its category.
                </p>
            </div>
        @endif

        <!-- Account (disabled for synced) -->
        <div class="mb-4">
            <label class="block mb-1">Account</label>
            <select name="account_id" class="w-full border rounded px-3 py-2" {{ $transaction->is_synced ? 'disabled' : 'required' }}>
                <option value="">Select account</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ old('account_id', $transaction->account_id) == $account->id ? 'selected' : '' }}>
                        {{ $account->name }} ({{ $account->currency }})
                    </option>
                @endforeach
            </select>
            @if($transaction->is_synced)
                <input type="hidden" name="account_id" value="{{ $transaction->account_id }}">
            @endif
        </div>

        <!-- Category (always editable) -->
        <div class="mb-4">
            <label class="block mb-1">Category</label>
            <select name="category_id" class="w-full border rounded px-3 py-2">
                <option value="">Select category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $transaction->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Type (disabled for synced) -->
        <div class="mb-4">
            <label class="block mb-1">Type</label>
            <select name="type" class="w-full border rounded px-3 py-2" {{ $transaction->is_synced ? 'disabled' : 'required' }}>
                <option value="income" {{ old('type', $transaction->type) == 'income' ? 'selected' : '' }}>Income</option>
                <option value="expense" {{ old('type', $transaction->type) == 'expense' ? 'selected' : '' }}>Expense</option>
            </select>
            @if($transaction->is_synced)
                <input type="hidden" name="type" value="{{ $transaction->type }}">
            @endif
        </div>

        <!-- Amount (disabled for synced) -->
        <div class="mb-4">
            <label class="block mb-1">Amount</label>
            <input type="number" step="0.01" name="amount" value="{{ old('amount', $transaction->amount) }}" class="w-full border rounded px-3 py-2" {{ $transaction->is_synced ? 'disabled' : 'required' }}>
            @if($transaction->is_synced)
                <input type="hidden" name="amount" value="{{ $transaction->amount }}">
            @endif
        </div>

        <!-- Description (editable always) -->
        <div class="mb-4">
            <label class="block mb-1">Description (optional)</label>
            <input type="text" name="description" value="{{ old('description', $transaction->description) }}" class="w-full border rounded px-3 py-2">
        </div>

        <!-- Reference (editable always) -->
        <div class="mb-4">
            <label class="block mb-1">Reference (optional)</label>
            <input type="text" name="reference" value="{{ old('reference', $transaction->reference) }}" class="w-full border rounded px-3 py-2">
        </div>

        <!-- Transaction Date (disabled for synced) -->
        <div class="mb-4">
            <label class="block mb-1">Transaction Date</label>
            <input type="date" name="transaction_date" value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d')) }}" class="w-full border rounded px-3 py-2" {{ $transaction->is_synced ? 'disabled' : '' }}>
            @if($transaction->is_synced)
                <input type="hidden" name="transaction_date" value="{{ $transaction->transaction_date->format('Y-m-d') }}">
            @endif
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
        <a href="{{ route('transactions.index') }}" class="ml-2 text-gray-600">Cancel</a>
    </form>
</div>
@endsection