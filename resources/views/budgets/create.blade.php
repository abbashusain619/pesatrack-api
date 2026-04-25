@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-md">
    <h1 class="text-2xl font-bold mb-4">Create Budget</h1>
    <form method="POST" action="{{ route('budgets.store') }}" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
        @csrf

        <div class="mb-4">
            <label class="block mb-1">Category</label>
            <select name="category_id" class="w-full border rounded px-3 py-2" required>
                <option value="">Select category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div class="mb-4">
            <label class="block mb-1">Amount ({{ Auth::user()->base_currency }})</label>
            <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="w-full border rounded px-3 py-2" required>
            @error('amount') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div class="mb-4">
            <label class="block mb-1">Period</label>
            <select name="period" class="w-full border rounded px-3 py-2" required>
                <option value="monthly" {{ old('period') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                <option value="weekly" {{ old('period') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                <option value="yearly" {{ old('period') == 'yearly' ? 'selected' : '' }}>Yearly</option>
            </select>
            @error('period') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div class="mb-4">
            <label class="block mb-1">Start Date (optional)</label>
            <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full border rounded px-3 py-2">
            @error('start_date') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div class="mb-4">
            <label class="block mb-1">End Date (optional)</label>
            <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full border rounded px-3 py-2">
            @error('end_date') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div class="mb-4">
            <label class="block mb-1">Alert Threshold (%) – optional</label>
            <input type="number" step="0.01" name="alert_threshold" value="{{ old('alert_threshold', 80) }}" class="w-full border rounded px-3 py-2">
            <p class="text-xs text-gray-500">You'll be notified when spending reaches this percentage of the budget.</p>
            @error('alert_threshold') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
        <a href="{{ route('budgets.index') }}" class="ml-2 text-gray-600">Cancel</a>
    </form>
</div>
@endsection