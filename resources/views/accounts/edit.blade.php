@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-md">
    <h1 class="text-2xl font-bold mb-4">Edit Account</h1>
    <form method="POST" action="{{ route('accounts.update', $account) }}" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
        @csrf @method('PUT')
        <div class="mb-4">
            <label class="block mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name', $account->name) }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1">Type</label>
            <select name="type" class="w-full border rounded px-3 py-2" required>
                <option value="mpesa" {{ $account->type == 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                <option value="bank" {{ $account->type == 'bank' ? 'selected' : '' }}>Bank</option>
                <option value="cash" {{ $account->type == 'cash' ? 'selected' : '' }}>Cash</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="block mb-1">Currency</label>
            <input type="text" name="currency" value="{{ old('currency', $account->currency) }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1">Balance</label>
            <input type="number" step="0.01" name="balance" value="{{ old('balance', $account->balance) }}" class="w-full border rounded px-3 py-2">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
        <a href="{{ route('accounts.index') }}" class="ml-2 text-gray-600">Cancel</a>
    </form>
</div>
@endsection