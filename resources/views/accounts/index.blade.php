@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Accounts</h1>
    <a href="{{ route('accounts.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded mb-4 inline-block">+ Add Account</a>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Type</th>
                    <th class="px-6 py-3 text-left">Currency</th>
                    <th class="px-6 py-3 text-right">Balance</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($accounts as $account)
                <tr class="border-t">
                    <td class="px-6 py-4">{{ $account->name }}</td>
                    <td class="px-6 py-4">{{ ucfirst($account->type) }}</td>
                    <td class="px-6 py-4">{{ $account->currency }}</td>
                    <td class="px-6 py-4 text-right">{{ number_format($account->balance, 2) }}</td>
                    <td class="px-6 py-4 text-center">
                        <a href="{{ route('accounts.edit', $account) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('Delete this account?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection