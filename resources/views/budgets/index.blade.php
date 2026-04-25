@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Budgets</h1>
        <a href="{{ route('budgets.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ Add Budget</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    @if($budgets->isEmpty())
        <div class="bg-gray-100 p-4 rounded text-center">No budgets yet. Create one to start tracking your spending limits.</div>
    @else
        <div class="bg-white dark:bg-gray-400 rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-blue-100 dark:bg-blue-700">
                    <tr>
                        <th class="px-6 py-3 text-left">Category</th>
                        <th class="px-6 py-3 text-left">Period</th>
                        <th class="px-6 py-3 text-right">Budget</th>
                        <th class="px-6 py-3 text-right">Spent</th>
                        <th class="px-6 py-3 text-right">Remaining</th>
                        <th class="px-6 py-3 text-center">Progress</th>
                        <th class="px-6 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($budgets as $budget)
                        @php
                            $spent = $budget->spentAmount();
                            $remaining = $budget->amount - $spent;
                            $percentage = $budget->amount > 0 ? ($spent / $budget->amount) * 100 : 0;
                            $barColor = $percentage < 80 ? 'bg-green-500' : ($percentage < 100 ? 'bg-yellow-500' : 'bg-red-500');
                        @endphp
                        <tr class="border-t">
                            <td class="px-6 py-4">{{ $budget->category->name }}</td>
                            <td class="px-6 py-4">{{ ucfirst($budget->period) }}</td>
                            <td class="px-6 py-4 text-right">{{ number_format($budget->amount, 2) }}</td>
                            <td class="px-6 py-4 text-right">{{ number_format($spent, 2) }}</td>
                            <td class="px-6 py-4 text-right">{{ number_format($remaining, 2) }}</td>
                            <td class="px-6 py-4">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="rounded-full h-2" style="width: {{ min($percentage, 100) }}%; background-color: 
                                                {{ $percentage < 80 ? '#10b981' : ($percentage < 100 ? '#eab308' : '#ef4444') }};">
                                    </div>
                                </div>
                                <span class="text-xs">{{ round($percentage, 1) }}%</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('budgets.edit', $budget) }}" class="text-blue-600 hover:underline mr-2">Edit</a>
                                <form action="{{ route('budgets.destroy', $budget) }}" method="POST" class="inline" onsubmit="return confirm('Delete this budget?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection