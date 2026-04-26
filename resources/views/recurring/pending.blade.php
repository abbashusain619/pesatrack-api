@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Pending Transactions</h1>
    @if(session('success')) <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ session('error') }}</div> @endif

    @if($pending->isEmpty())
        <p>No pending transactions.</p>
    @else
        <table class="min-w-full bg-white dark:bg-gray-800 shadow rounded-lg">
            <thead><tr><th>Date</th><th>Account</th><th>Category</th><th>Description</th><th>Amount</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($pending as $tx)
                <tr>
                    <td>{{ $tx->transaction_date->format('Y-m-d') }}</td>
                    <td>{{ $tx->account->name }}</td>
                    <td>{{ $tx->category ? $tx->category->name : '-' }}</td>
                    <td>{{ $tx->description ?? $tx->reference ?? '-' }}</td>
                    <td class="{{ $tx->type == 'income' ? 'text-green-600' : 'text-red-600' }}">{{ number_format($tx->amount, 2) }}</td>
                    <td>
                        <form action="{{ route('recurring.confirm', $tx) }}" method="POST" class="inline">
                            @csrf @method('PATCH') <button type="submit" class="bg-green-500 text-white px-2 py-1 rounded">Confirm</button>
                        </form>
                        <form action="{{ route('recurring.reject', $tx) }}" method="POST" class="inline ml-2">
                            @csrf @method('PATCH') <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded">Reject</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection