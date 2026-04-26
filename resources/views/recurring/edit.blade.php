@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-md">
    <h1 class="text-2xl font-bold mb-4">Edit Recurring Template</h1>
    <form method="POST" action="{{ route('recurring.update', $recurring) }}" class="bg-white dark:bg-gray-800 p-6 rounded shadow">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block mb-1">Account</label>
            <select name="account_id" class="w-full border rounded px-3 py-2" required>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ old('account_id', $recurring->account_id) == $account->id ? 'selected' : '' }}>
                        {{ $account->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block mb-1">Category</label>
            <select name="category_id" class="w-full border rounded px-3 py-2">
                <option value="">None</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $recurring->category_id) == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block mb-1">Type</label>
            <select name="type" class="w-full border rounded px-3 py-2" required>
                <option value="expense" {{ old('type', $recurring->type) == 'expense' ? 'selected' : '' }}>Expense</option>
                <option value="income" {{ old('type', $recurring->type) == 'income' ? 'selected' : '' }}>Income</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block mb-1">Amount</label>
            <input type="number" step="0.01" name="amount" value="{{ old('amount', $recurring->amount) }}" class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-4">
            <label class="block mb-1">Description</label>
            <input type="text" name="description" value="{{ old('description', $recurring->description) }}" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-4">
            <label class="block mb-1">Reference</label>
            <input type="text" name="reference" value="{{ old('reference', $recurring->reference) }}" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-4">
            <label class="block mb-1">Frequency</label>
            <select name="frequency" class="w-full border rounded px-3 py-2" required>
                <option value="daily" {{ old('frequency', $recurring->frequency) == 'daily' ? 'selected' : '' }}>Daily</option>
                <option value="weekly" {{ old('frequency', $recurring->frequency) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                <option value="monthly" {{ old('frequency', $recurring->frequency) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                <option value="yearly" {{ old('frequency', $recurring->frequency) == 'yearly' ? 'selected' : '' }}>Yearly</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block mb-1">Start Date</label>
            <input type="date" name="start_date" value="{{ old('start_date', optional($recurring->start_date)->format('Y-m-d')) }}" class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-4">
            <label class="block mb-1">End Date (optional)</label>
            <input type="date" name="end_date" value="{{ old('end_date', optional($recurring->end_date)->format('Y-m-d')) }}" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-4">
            <label class="block mb-1">Interval Count</label>
            <input type="number" name="interval_count" value="{{ old('interval_count', $recurring->interval_count) }}" min="1" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-4">
            <label class="block mb-1">Active</label>
            <select name="is_active" class="w-full border rounded px-3 py-2">
                <option value="1" {{ old('is_active', $recurring->is_active) ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ old('is_active', $recurring->is_active) ? '' : 'selected' }}>No</option>
            </select>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
        <a href="{{ route('recurring.index') }}" class="ml-2 text-gray-600">Cancel</a>
    </form>
</div>
@endsection