@extends('layouts.guest')

@section('content')
<div class="container mx-auto px-4 py-12 max-w-md">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-4">Complete Your Profile</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-4">Please select your country to continue.</p>

        <form method="POST" action="{{ route('profile.complete') }}">
            @csrf
            <div class="mb-4">
                <label class="block mb-1">Country</label>
                <select name="country" class="w-full border rounded px-3 py-2" required>
                    <option value="">-- Select Country --</option>
                    @foreach($countries as $code => $name)
                        <option value="{{ $code }}" {{ old('country') == $code ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('country')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded w-full">Save & Continue</button>
        </form>
    </div>
</div>
@endsection