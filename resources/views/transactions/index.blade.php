@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Transactions</h1>
    <a href="{{ route('transactions.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded mb-4 inline-block">+ Add Transaction</a>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left">Date</th>
                    <th class="px-6 py-3 text-left">Account</th>
                    <th class="px-6 py-3 text-left">Category</th>
                    <th class="px-6 py-3 text-left">Type</th>
                    <th class="px-6 py-3 text-left">Description</th>
                    <th class="px-6 py-3 text-right">Amount</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $tx)
                <tr class="border-t">
                    <td class="px-6 py-4">{{ $tx->transaction_date->format('Y-m-d') }}</td>
                    <td class="px-6 py-4">{{ $tx->account->name }}</td>
                    <td class="px-6 py-4">
                        @if($tx->category)
                            <span style="background-color: {{ $tx->category->color }}22; color: {{ $tx->category->color }};" 
                                  class="px-2 py-1 rounded-full text-xs font-semibold">
                                {{ $tx->category->icon ?? '' }} {{ $tx->category->name }}
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">Uncategorised</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">{{ ucfirst($tx->type) }}</td>
                    <td class="px-6 py-4">{{ $tx->description ?? '—' }}</td>
                    <td class="px-6 py-4 text-right {{ $tx->type == 'income' ? 'text-green-600' : 'text-red-600' }}">
                        {{ $tx->type == 'income' ? '+' : '-' }}{{ number_format($tx->amount, 2) }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a href="{{ route('transactions.edit', $tx) }}" class="text-blue-600 hover:underline mr-2">Edit</a>
                        <form action="{{ route('transactions.destroy', $tx) }}" method="POST" class="inline" onsubmit="return confirm('Delete this transaction?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection