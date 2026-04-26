@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-md">
    <h1 class="text-2xl font-bold mb-4">Create Recurring Template</h1>
    <form method="POST" action="{{ route('recurring.store') }}" class="bg-white dark:bg-gray-800 p-6 rounded shadow">
        @csrf
        <div class="mb-4">
            <label class="block mb-1">Account</label>
            <select name="account_id" class="w-full border rounded px-3 py-2" required>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block mb-1">Category</label>
            <select name="category_id" class="w-full border rounded px-3 py-2">
                <option value="">None</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block mb-1">Type</label>
            <select name="type" class="w-full border rounded px-3 py-2" required>
                <option value="expense">Expense</option>
                <option value="income">Income</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="block mb-1">Amount</label>
            <input type="number" step="0.01" name="amount" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1">Description</label>
            <input type="text" name="description" class="w-full border rounded px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block mb-1">Reference</label>
            <input type="text" name="reference" class="w-full border rounded px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block mb-1">Frequency</label>
            <select name="frequency" class="w-full border rounded px-3 py-2" required>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="block mb-1">Start Date</label>
            <input type="date" name="start_date" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1">End Date (optional)</label>
            <input type="date" name="end_date" class="w-full border rounded px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block mb-1">Interval Count</label>
            <input type="number" name="interval_count" value="1" min="1" class="w-full border rounded px-3 py-2">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
        <a href="{{ route('recurring.index') }}" class="ml-2 text-gray-600">Cancel</a>
    </form>
</div>
@endsection