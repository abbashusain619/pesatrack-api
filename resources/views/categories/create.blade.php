@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-md">
    <h1 class="text-2xl font-bold mb-4">Create Category</h1>
    <form method="POST" action="{{ route('categories.store') }}" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
        @csrf
        <div class="mb-4">
            <label class="block mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1">Color (hex)</label>
            <input type="color" name="color" value="{{ old('color', '#3B82F6') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block mb-1">Icon (emoji or text)</label>
            <input type="text" name="icon" value="{{ old('icon') }}" placeholder="🍔" class="w-full border rounded px-3 py-2">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
        <a href="{{ route('categories.index') }}" class="ml-2 text-gray-600">Cancel</a>
    </form>
</div>
@endsection