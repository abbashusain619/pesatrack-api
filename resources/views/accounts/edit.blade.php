@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-md">
    <h1 class="text-2xl font-bold mb-4">Edit Account</h1>

    @if($account->is_synced)
        <div class="bg-blue-100 text-blue-800 p-3 rounded mb-4">
            ℹ️ This account is automatically synced. You can only edit its name.
        </div>
    @endif

    <form method="POST" action="{{ route('accounts.update', $account) }}" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
        @csrf
        @method('PUT')

        <!-- Name (always editable) -->
        <div class="mb-4">
            <label class="block mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name', $account->name) }}" class="w-full border rounded px-3 py-2" required>
            @error('name') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
        </div>

        @if(!$account->is_synced)
            <!-- Type (for manual accounts) -->
            <div class="mb-4">
                <label class="block mb-1">Type</label>
                <select name="type" id="account-type" class="w-full border rounded px-3 py-2" required>
                    <option value="mobile_money" {{ old('type', $account->type) == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                    <option value="bank" {{ old('type', $account->type) == 'bank' ? 'selected' : '' }}>Bank</option>
                    <option value="cash" {{ old('type', $account->type) == 'cash' ? 'selected' : '' }}>Cash</option>
                </select>
                @error('type') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
            </div>

            <!-- Mobile Money fields (only for manual mobile money) -->
            <div id="mobile-money-fields" style="display: {{ old('type', $account->type) == 'mobile_money' ? 'block' : 'none' }};">
                <div class="mb-4">
                    <label class="block mb-1">Provider</label>
                    <select name="mobile_provider" id="mobile-provider" class="w-full border rounded px-3 py-2">
                        <option value="">-- Select Provider --</option>
                        <option value="mpesa_tz" data-currency="TZS" {{ old('mobile_provider', $account->mobile_provider) == 'mpesa_tz' ? 'selected' : '' }}>M-Pesa (Tanzania)</option>
                        <option value="mpesa_ke" data-currency="KES" {{ old('mobile_provider', $account->mobile_provider) == 'mpesa_ke' ? 'selected' : '' }}>M-Pesa (Kenya)</option>
                        <option value="tigo_pesa" data-currency="TZS" {{ old('mobile_provider', $account->mobile_provider) == 'tigo_pesa' ? 'selected' : '' }}>Tigo Pesa (Tanzania)</option>
                        <option value="airtel_tz" data-currency="TZS" {{ old('mobile_provider', $account->mobile_provider) == 'airtel_tz' ? 'selected' : '' }}>Airtel Money (Tanzania)</option>
                        <option value="airtel_ke" data-currency="KES" {{ old('mobile_provider', $account->mobile_provider) == 'airtel_ke' ? 'selected' : '' }}>Airtel Money (Kenya)</option>
                        <option value="mtn_mobile_money" data-currency="UGX" {{ old('mobile_provider', $account->mobile_provider) == 'mtn_mobile_money' ? 'selected' : '' }}>MTN Mobile Money (Uganda)</option>
                        <option value="halopesa" data-currency="TZS" {{ old('mobile_provider', $account->mobile_provider) == 'halopesa' ? 'selected' : '' }}>HaloPesa (Tanzania)</option>
                        <option value="azampesa" data-currency="TZS" {{ old('mobile_provider', $account->mobile_provider) == 'azampesa' ? 'selected' : '' }}>Azam Pesa (Tanzania)</option>
                        <option value="vodafone_cash" data-currency="GHS" {{ old('mobile_provider', $account->mobile_provider) == 'vodafone_cash' ? 'selected' : '' }}>Vodafone Cash (Ghana)</option>
                    </select>
                    @error('mobile_provider') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="mb-4">
                    <label class="block mb-1">Phone Number</label>
                    <input type="tel" name="phone_number" value="{{ old('phone_number', $account->phone_number) }}" placeholder="e.g., 2557XXXXXXXX" class="w-full border rounded px-3 py-2">
                    @error('phone_number') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Bank fields (only for manual bank accounts) -->
            <div id="bank-fields" style="display: {{ old('type', $account->type) == 'bank' ? 'block' : 'none' }};">
                <div class="mb-4">
                    <label class="block mb-1">Bank Account Number</label>
                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $account->bank_account_number) }}" class="w-full border rounded px-3 py-2">
                    @error('bank_account_number') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="mb-4">
                    <label class="block mb-1">Bank Code</label>
                    <input type="text" name="bank_code" value="{{ old('bank_code', $account->bank_code) }}" placeholder="e.g., CRDB, NMB" class="w-full border rounded px-3 py-2">
                    @error('bank_code') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Currency (for manual accounts) -->
            <div class="mb-4">
                <label class="block mb-1">Currency</label>
                <select name="currency" id="currency-select" required>
                    <option value="">Select a currency</option>
                    <option value="TZS" {{ old('currency', $account->currency) == 'TZS' ? 'selected' : '' }}>TZS – Tanzanian Shilling</option>
                    <option value="USD" {{ old('currency', $account->currency) == 'USD' ? 'selected' : '' }}>USD – US Dollar</option>
                    <option value="EUR" {{ old('currency', $account->currency) == 'EUR' ? 'selected' : '' }}>EUR – Euro</option>
                    <option value="GBP" {{ old('currency', $account->currency) == 'GBP' ? 'selected' : '' }}>GBP – British Pound</option>
                    <option value="KES" {{ old('currency', $account->currency) == 'KES' ? 'selected' : '' }}>KES – Kenyan Shilling</option>
                    <option value="UGX" {{ old('currency', $account->currency) == 'UGX' ? 'selected' : '' }}>UGX – Ugandan Shilling</option>
                    <option value="GHS" {{ old('currency', $account->currency) == 'GHS' ? 'selected' : '' }}>GHS – Ghanaian Cedi</option>
                </select>
                <p class="text-xs text-gray-500 mt-1" id="currency-hint"></p>
                @error('currency') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
            </div>

            <!-- Balance (only for manual accounts) -->
            <div class="mb-4">
                <label class="block mb-1">Balance</label>
                <input type="number" step="0.01" name="balance" value="{{ old('balance', $account->balance) }}" class="w-full border rounded px-3 py-2">
                @error('balance') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
            </div>
        @else
            <!-- For synced accounts, preserve hidden fields so they aren't overwritten -->
            <input type="hidden" name="type" value="{{ $account->type }}">
            <input type="hidden" name="currency" value="{{ $account->currency }}">
            <input type="hidden" name="balance" value="{{ $account->balance }}">
            <!-- Also preserve identifier fields if needed -->
            @if($account->mobile_provider)
                <input type="hidden" name="mobile_provider" value="{{ $account->mobile_provider }}">
                <input type="hidden" name="phone_number" value="{{ $account->phone_number }}">
            @endif
            @if($account->bank_account_number)
                <input type="hidden" name="bank_account_number" value="{{ $account->bank_account_number }}">
                <input type="hidden" name="bank_code" value="{{ $account->bank_code }}">
            @endif
        @endif

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
        <a href="{{ route('accounts.index') }}" class="ml-2 text-gray-600">Cancel</a>
    </form>
</div>

@if(!$account->is_synced)
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('account-type');
        const mobileMoneyDiv = document.getElementById('mobile-money-fields');
        const bankDiv = document.getElementById('bank-fields');
        const mobileProvider = document.getElementById('mobile-provider');
        const currencySelect = document.getElementById('currency-select');
        const currencyHint = document.getElementById('currency-hint');

        let choicesInstance = new Choices(currencySelect, {
            searchEnabled: true,
            shouldSort: true,
            placeholder: true,
            placeholderValue: 'Select a currency',
            itemSelectText: '',
            noResultsText: 'No currency found',
        });

        function setCurrencyValue(value) {
            choicesInstance.setChoiceByValue(value);
            currencySelect.value = value;
        }

        function setCurrencyEnabled(enabled) {
            if (enabled) {
                choicesInstance.enable();
                currencySelect.disabled = false;
                currencyHint.innerText = 'You can manually select currency.';
            } else {
                choicesInstance.disable();
                currencySelect.disabled = true;
                currencyHint.innerText = 'Currency is auto‑set from provider.';
            }
        }

        function updateCurrencyFromProvider() {
            const selected = mobileProvider.options[mobileProvider.selectedIndex];
            const currency = selected?.getAttribute('data-currency');
            if (currency && typeSelect.value === 'mobile_money') {
                setCurrencyValue(currency);
                currencyHint.innerText = 'Currency auto‑set from provider.';
            } else if (typeSelect.value !== 'mobile_money') {
                // nothing
            } else {
                setCurrencyValue('');
                currencyHint.innerText = 'Please select a provider to auto‑set currency.';
            }
        }

        function toggleFields() {
            const type = typeSelect.value;
            mobileMoneyDiv.style.display = type === 'mobile_money' ? 'block' : 'none';
            bankDiv.style.display = type === 'bank' ? 'block' : 'none';

            if (type === 'mobile_money') {
                setCurrencyEnabled(false);
                if (mobileProvider.value) updateCurrencyFromProvider();
                else {
                    setCurrencyValue('');
                    currencyHint.innerText = 'Please select a provider to auto‑set currency.';
                }
            } else {
                setCurrencyEnabled(true);
                if (!currencySelect.value) {
                    currencyHint.innerText = 'You can manually select currency.';
                } else {
                    currencyHint.innerText = 'Manually selected currency.';
                }
            }
        }

        toggleFields();
        if (typeSelect.value === 'mobile_money' && mobileProvider.value) updateCurrencyFromProvider();

        mobileProvider.addEventListener('change', function() {
            if (typeSelect.value === 'mobile_money') updateCurrencyFromProvider();
        });
        typeSelect.addEventListener('change', toggleFields);
    });
</script>
@endif
@endsection