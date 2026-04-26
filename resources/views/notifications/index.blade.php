@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Notifications</h1>
        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Mark all as read</button>
        </form>
    </div>
    @if($notifications->isEmpty())
        <p>No notifications.</p>
    @else
        <div class="space-y-3">
            @foreach($notifications as $notif)
                <div class="bg-white dark:bg-gray-800 p-4 rounded shadow {{ $notif->read_at ? 'opacity-75' : 'border-l-4 border-blue-500' }}">
                    <div class="flex justify-between">
                        <p>{{ $notif->data['message'] }}</p>
                        @if(!$notif->read_at)
                            <form action="{{ route('notifications.mark-read', $notif->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-blue-600 text-sm">Mark read</button>
                            </form>
                        @endif
                    </div>
                    <small class="text-gray-500">{{ $notif->created_at->diffForHumans() }}</small>
                </div>
            @endforeach
        </div>
        {{ $notifications->links() }}
    @endif
</div>
@endsection