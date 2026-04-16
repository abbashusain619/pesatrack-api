@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-md">
    <h1 class="text-2xl font-bold mb-4">Add Account</h1>
    <form method="POST" action="{{ route('accounts.store') }}" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
        @csrf
        <div class="mb-4">
            <label class="block mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1">Type</label>
            <select name="type" id="account-type" class="w-full border rounded px-3 py-2" required>
                <option value="mpesa" {{ old('type') == 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                <option value="bank" {{ old('type') == 'bank' ? 'selected' : '' }}>Bank</option>
                <option value="cash" {{ old('type') == 'cash' ? 'selected' : '' }}>Cash</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="block mb-1">Currency</label>
            <input type="text" name="currency" value="{{ old('currency') }}" placeholder="USD, TZS, KES" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1">Initial Balance (optional)</label>
            <input type="number" step="0.01" name="balance" value="{{ old('balance') }}" class="w-full border rounded px-3 py-2">
        </div>

        <!-- M-Pesa fields (hidden by default) -->
        <div id="mpesa-fields" style="display: {{ old('type') == 'mpesa' ? 'block' : 'none' }};">
            <div class="mb-4">
                <label class="block mb-1">M-Pesa Phone Number</label>
                <input type="tel" name="phone_number" value="{{ old('phone_number') }}" placeholder="2557XXXXXXXX" class="w-full border rounded px-3 py-2">
                @error('phone_number')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Bank fields (hidden by default) -->
        <div id="bank-fields" style="display: {{ old('type') == 'bank' ? 'block' : 'none' }};">
            <div class="mb-4">
                <label class="block mb-1">Bank Account Number</label>
                <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" class="w-full border rounded px-3 py-2">
                @error('bank_account_number')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-4">
                <label class="block mb-1">Bank Code</label>
                <input type="text" name="bank_code" value="{{ old('bank_code') }}" placeholder="e.g., CRDB, NMB" class="w-full border rounded px-3 py-2">
                @error('bank_code')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
        <a href="{{ route('accounts.index') }}" class="ml-2 text-gray-600">Cancel</a>
    </form>
</div>

<script>
    const typeSelect = document.getElementById('account-type');
    const mpesaDiv = document.getElementById('mpesa-fields');
    const bankDiv = document.getElementById('bank-fields');

    function toggleFields() {
        const type = typeSelect.value;
        mpesaDiv.style.display = type === 'mpesa' ? 'block' : 'none';
        bankDiv.style.display = type === 'bank' ? 'block' : 'none';
    }

    typeSelect.addEventListener('change', toggleFields);
    // Ensure visibility on page load (in case of validation errors)
    toggleFields();
</script>
@endsection