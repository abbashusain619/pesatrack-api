@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-md">
    <h1 class="text-2xl font-bold mb-4">Add Account</h1>
    <form method="POST" action="{{ route('accounts.store') }}" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
        @csrf
        <div class="mb-4">
            <label class="block mb-1">Name</label>
            <input type="text" name="name" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1">Type</label>
            <select name="type" class="w-full border rounded px-3 py-2" required>
                <option value="mpesa">M-Pesa</option>
                <option value="bank">Bank</option>
                <option value="cash">Cash</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="block mb-1">Currency</label>
            <input type="text" name="currency" placeholder="USD, TZS, KES" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1">Initial Balance (optional)</label>
            <input type="number" step="0.01" name="balance" class="w-full border rounded px-3 py-2">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
        <a href="{{ route('accounts.index') }}" class="ml-2 text-gray-600">Cancel</a>
    </form>
</div>
@endsection