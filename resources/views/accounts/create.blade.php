@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-md">
    <h1 class="text-2xl font-bold mb-4">Add Account</h1>

    <form method="POST" action="{{ route('accounts.store') }}" class="bg-white dark:bg-gray-600 p-6 rounded-lg shadow">
        @csrf

        <!-- Account Name -->
        <div class="mb-4">
            <label class="block mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2" required>
            @error('name') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
        </div>

        <!-- Account Type -->
        <div class="mb-4">
            <label class="block mb-1">Type</label>
            <select name="type" id="account-type" class="w-full border rounded px-3 py-2" required>
                <option value="mobile_money" {{ old('type') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                <option value="bank" {{ old('type') == 'bank' ? 'selected' : '' }}>Bank</option>
                <option value="cash" {{ old('type') == 'cash' ? 'selected' : '' }}>Cash</option>
            </select>
            @error('type') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
        </div>

        <!-- Mobile Money fields -->
        <div id="mobile-money-fields">
            <div class="mb-4">
                <label class="block mb-1">Provider</label>
                <select name="mobile_provider" id="mobile-provider" class="w-full border rounded px-3 py-2">
                    <option value="">-- Select Provider --</option>
                    <option value="mpesa_tz" data-currency="TZS" {{ old('mobile_provider') == 'mpesa_tz' ? 'selected' : '' }}>M-Pesa (Tanzania)</option>
                    <option value="mpesa_ke" data-currency="KES" {{ old('mobile_provider') == 'mpesa_ke' ? 'selected' : '' }}>M-Pesa (Kenya)</option>
                    <option value="tigo_pesa" data-currency="TZS" {{ old('mobile_provider') == 'tigo_pesa' ? 'selected' : '' }}>Tigo Pesa (Tanzania)</option>
                    <option value="airtel_tz" data-currency="TZS" {{ old('mobile_provider') == 'airtel_tz' ? 'selected' : '' }}>Airtel Money (Tanzania)</option>
                    <option value="airtel_ke" data-currency="KES" {{ old('mobile_provider') == 'airtel_ke' ? 'selected' : '' }}>Airtel Money (Kenya)</option>
                    <option value="mtn_mobile_money" data-currency="UGX" {{ old('mobile_provider') == 'mtn_mobile_money' ? 'selected' : '' }}>MTN Mobile Money (Uganda)</option>
                    <option value="halopesa" data-currency="TZS" {{ old('mobile_provider') == 'halopesa' ? 'selected' : '' }}>HaloPesa (Tanzania)</option>
                    <option value="azampesa" data-currency="TZS" {{ old('mobile_provider') == 'azampesa' ? 'selected' : '' }}>Azam Pesa (Tanzania)</option>
                    <option value="vodafone_cash" data-currency="GHS" {{ old('mobile_provider') == 'vodafone_cash' ? 'selected' : '' }}>Vodafone Cash (Ghana)</option>
                </select>
                @error('mobile_provider') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="mb-4">
                <label class="block mb-1">Phone Number</label>
                <input type="tel" name="phone_number" value="{{ old('phone_number') }}" placeholder="e.g., 2557XXXXXXXX" class="w-full border rounded px-3 py-2">
                @error('phone_number') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        <!-- Bank fields -->
        <div id="bank-fields">
            <div class="mb-4">
                <label class="block mb-1">Bank Account Number</label>
                <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" class="w-full border rounded px-3 py-2">
                @error('bank_account_number') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="mb-4">
                <label class="block mb-1">Bank Code</label>
                <input type="text" name="bank_code" value="{{ old('bank_code') }}" placeholder="e.g., CRDB, NMB" class="w-full border rounded px-3 py-2">
                @error('bank_code') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        <!-- Currency field (searchable dropdown) -->
        <div class="mb-4">
            <label class="block mb-1">Currency</label>
            <select name="currency" id="currency-select" required>
                <option value="">Select a currency</option>
                <option value="TZS" {{ old('currency') == 'TZS' ? 'selected' : '' }}>TZS – Tanzanian Shilling</option>
                <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD – US Dollar</option>
                <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR – Euro</option>
                <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP – British Pound</option>
                <option value="KES" {{ old('currency') == 'KES' ? 'selected' : '' }}>KES – Kenyan Shilling</option>
                <option value="UGX" {{ old('currency') == 'UGX' ? 'selected' : '' }}>UGX – Ugandan Shilling</option>
                <option value="GHS" {{ old('currency') == 'GHS' ? 'selected' : '' }}>GHS – Ghanaian Cedi</option>
            </select>
            <p class="text-xs text-gray-500 mt-1" id="currency-hint"></p>
            @error('currency') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
        </div>

        <!-- Initial Balance – shown only for cash accounts -->
        <div id="balance-field">
            <div class="mb-4">
                <label class="block mb-1">Initial Balance (optional)</label>
                <input type="number" step="0.01" name="balance" value="{{ old('balance') }}" class="w-full border rounded px-3 py-2">
                <p class="text-xs text-gray-500 mt-1" id="balance-hint"></p>
            </div>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
        <a href="{{ route('accounts.index') }}" class="ml-2 text-gray-600">Cancel</a>
    </form>
</div>

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
        const balanceDiv = document.getElementById('balance-field');
        const balanceHint = document.getElementById('balance-hint');

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
                if (mobileProvider.value) {
                    updateCurrencyFromProvider();
                } else {
                    setCurrencyValue('');
                    currencyHint.innerText = 'Please select a provider to auto‑set currency.';
                }
                balanceDiv.style.display = 'none';
                balanceHint.innerText = '';
            } else if (type === 'bank') {
                // Bank accounts: currency is auto-detected from account number by the API, not manually set
                setCurrencyEnabled(false);
                setCurrencyValue('');
                currencyHint.innerText = 'Currency will be auto‑detected from your bank account number.';
                balanceDiv.style.display = 'none';
                balanceHint.innerText = '';
            } else {
                // Cash account
                setCurrencyEnabled(true);
                balanceDiv.style.display = 'block';
                balanceHint.innerText = 'You can set an initial balance for this cash account.';
                if (!currencySelect.value) {
                    currencyHint.innerText = 'You can manually select currency.';
                } else {
                    currencyHint.innerText = 'Manually selected currency.';
                }
            }
        }

        // Initial setup
        toggleFields();

        if (typeSelect.value === 'mobile_money' && mobileProvider.value) {
            updateCurrencyFromProvider();
        } else if (typeSelect.value !== 'mobile_money' && '{{ old('currency') }}') {
            setCurrencyValue('{{ old('currency') }}');
        }

        mobileProvider.addEventListener('change', function() {
            if (typeSelect.value === 'mobile_money') updateCurrencyFromProvider();
        });
        typeSelect.addEventListener('change', toggleFields);
    });
</script>
@endsection