@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8 max-w-md">
        <h1 class="text-2xl font-bold mb-4">Add Account</h1>
        <form method="POST" action="{{ route('accounts.store') }}" class="bg-white dark:bg-gray-600 p-6 rounded-lg shadow">
            @csrf
            <div class="mb-4">
                <label class="block mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="mb-4">
                <label class="block mb-1">Type</label>
                <select name="type" id="account-type" class="w-full border rounded px-3 py-2" required>
                    <option value="mobile_money" {{ old('type') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                    <option value="bank" {{ old('type') == 'bank' ? 'selected' : '' }}>Bank</option>
                    <option value="cash" {{ old('type') == 'cash' ? 'selected' : '' }}>Cash</option>
                </select>
            </div>

            <!-- Mobile Money fields (hidden by default) -->
            <div id="mobile-money-fields" style="display: {{ old('type') == 'mobile_money' ? 'block' : 'none' }};">
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
                    @error('mobile_provider')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="block mb-1">Phone Number</label>
                    <input type="tel" name="phone_number" value="{{ old('phone_number') }}" placeholder="e.g., 2557XXXXXXXX" class="w-full border rounded px-3 py-2">
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

            <div class="mb-4">
                <label class="block mb-1">Currency</label>
                <input type="text" name="currency" id="currency" value="{{ old('currency') }}" placeholder="USD, TZS, KES" class="w-full border rounded px-3 py-2" required>
                <p class="text-xs text-gray-500 mt-1" id="currency-hint"></p>
            </div>
            <div class="mb-4">
                <label class="block mb-1">Initial Balance (optional)</label>
                <input type="number" step="0.01" name="balance" value="{{ old('balance') }}" class="w-full border rounded px-3 py-2">
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
            <a href="{{ route('accounts.index') }}" class="ml-2 text-gray-600">Cancel</a>
        </form>
    </div>

    <script>
        const typeSelect = document.getElementById('account-type');
        const mobileMoneyDiv = document.getElementById('mobile-money-fields');
        const bankDiv = document.getElementById('bank-fields');
        const mobileProvider = document.getElementById('mobile-provider');
        const currencyInput = document.getElementById('currency');
        const currencyHint = document.getElementById('currency-hint');

        function toggleFields() {
            const type = typeSelect.value;
            mobileMoneyDiv.style.display = type === 'mobile_money' ? 'block' : 'none';
            bankDiv.style.display = type === 'bank' ? 'block' : 'none';

            // If not mobile money, make currency editable and clear hint
            if (type !== 'mobile_money') {
                currencyInput.readOnly = false;
                currencyHint.innerText = 'You can manually set currency.';
            } else {
                currencyInput.readOnly = true;
                currencyHint.innerText = 'Currency is auto-set from provider.';
                // Trigger provider change to set initial currency
                if (mobileProvider) updateCurrencyFromProvider();
            }
        }

        function updateCurrencyFromProvider() {
            const selectedOption = mobileProvider.options[mobileProvider.selectedIndex];
            const currency = selectedOption.getAttribute('data-currency');
            if (currency && typeSelect.value === 'mobile_money') {
                currencyInput.value = currency;
                currencyHint.innerText = 'Currency auto-set from provider.';
            } else if (typeSelect.value !== 'mobile_money') {
                // Do nothing, manual mode
            } else {
                // No provider selected
                currencyInput.value = '';
                currencyHint.innerText = 'Please select a provider to auto-set currency.';
            }
        }

        if (mobileProvider) {
            mobileProvider.addEventListener('change', function() {
                if (typeSelect.value === 'mobile_money') {
                    updateCurrencyFromProvider();
                }
            });
        }

        typeSelect.addEventListener('change', toggleFields);
        // Run on load to set correct visibility and initial currency if mobile money pre-selected
        toggleFields();
        if (typeSelect.value === 'mobile_money' && mobileProvider) {
            updateCurrencyFromProvider();
        }
    </script>
@endsection