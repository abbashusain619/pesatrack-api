@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Categories</h1>
        <a href="{{ route('categories.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">+ New Category</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Color</th>
                    <th class="px-6 py-3 text-left">Icon</th>
                    <th class="px-6 py-3 text-left">Type</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $cat)
                <tr class="border-t">
                    <td class="px-6 py-4">{{ $cat->name }}</td>
                    <td class="px-6 py-4">
                        <span style="background-color: {{ $cat->color }};" class="inline-block w-6 h-6 rounded-full"></span>
                        {{ $cat->color }}
                    </td>
                    <td class="px-6 py-4">{{ $cat->icon ?? '—' }}</td>
                    <td class="px-6 py-4">
                        @if($cat->is_system)
                            <span class="text-xs bg-gray-200 px-2 py-1 rounded">System</span>
                        @else
                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Custom</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if(!$cat->is_system || $cat->user_id)
                            <a href="{{ route('categories.edit', $cat) }}" class="text-blue-600 hover:underline mr-2">Edit</a>
                            <form action="{{ route('categories.destroy', $cat) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category? Transactions will become uncategorised.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        @else
                            <span class="text-gray-400">System (read‑only)</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection