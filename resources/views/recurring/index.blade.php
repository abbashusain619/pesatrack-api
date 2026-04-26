@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Recurring Transactions</h1>
        <a href="{{ route('recurring.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">+ New Template</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    @if($templates->isEmpty())
        <p>No recurring templates yet.</p>
    @else
        <table class="min-w-full bg-white dark:bg-gray-800 shadow rounded-lg">
            <thead>
                <tr><th>Description</th><th>Account</th><th>Amount</th><th>Frequency</th><th>Next Date</th><th>Active</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach($templates as $t)
                <tr>
                    <td>{{ $t->description ?? $t->reference ?? '—' }}</td>
                    <td>{{ $t->account->name }}</td>
                    <td>{{ number_format($t->amount, 2) }}</td>
                    <td>{{ ucfirst($t->frequency) }}</td>
                    <td>{{ $t->next_date->format('Y-m-d') }}</td>
                    <td>{{ $t->is_active ? 'Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('recurring.edit', $t) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('recurring.destroy', $t) }}" method="POST" class="inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:underline ml-2">Delete</button></form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection