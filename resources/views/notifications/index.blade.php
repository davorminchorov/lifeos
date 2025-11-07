@extends('layouts.app')

@section('title', 'Notifications - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Notifications
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Stay updated with important events and reminders
            </p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('notifications.preferences') }}" class="bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Preferences
            </a>
            @if($unreadCount > 0)
                <button onclick="markAllAsRead()" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 shadow-sm">
                    Mark All Read
                </button>
            @endif
        </div>
    </div>
@endsection

@section('content')
    <!-- Notification Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-[color:var(--color-accent-500)] rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">
                                Total Notifications
                            </dt>
                            <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                {{ $notifications->total() }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-[color:var(--color-warning-500)] rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.464 0L4.348 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">
                                Unread
                            </dt>
                            <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                {{ $unreadCount }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-[color:var(--color-success-500)] rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">
                                Read
                            </dt>
                            <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                {{ $notifications->total() - $unreadCount }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
        <div class="px-4 py-5 sm:p-6">
            @if($notifications->count() > 0)
                <div class="space-y-4">
                    @foreach($notifications as $notification)
                        <div class="flex items-start p-4 rounded-lg border {{ $notification->read_at ? 'bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-200)]' : 'bg-[color:var(--color-accent-50)] dark:bg-[color:var(--color-accent-900)] border-[color:var(--color-accent-200)] dark:border-[color:var(--color-accent-700)]' }}"
                             id="notification-{{ $notification->id }}">
                            <div class="flex-shrink-0 mt-1">
                                @if($notification->data['type'] ?? '' === 'subscription_renewal')
                                    <div class="w-8 h-8 bg-[color:var(--color-info-500)] rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    </div>
                                @elseif($notification->data['type'] ?? '' === 'contract_expiration')
                                    <div class="w-8 h-8 bg-[color:var(--color-danger-500)] rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.464 0L4.348 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-8 h-8 bg-[color:var(--color-accent-500)] rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <div class="ml-4 flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                            {{ $notification->data['title'] ?? 'Notification' }}
                                        </p>
                                        <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-1">
                                            {{ $notification->data['message'] ?? '' }}
                                        </p>
                                        <p class="text-xs text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)] mt-2">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                    </div>

                                    <div class="flex items-center space-x-2 ml-4">
                                        @if(isset($notification->data['action_url']))
                                            <a href="{{ $notification->data['action_url'] }}"
                                               onclick="markAsRead('{{ $notification->id }}')"
                                               class="text-[color:var(--color-accent-500)] hover:text-[color:var(--color-accent-600)] text-xs font-medium">
                                                View
                                            </a>
                                        @endif

                                        @if(!$notification->read_at)
                                            <button onclick="markAsRead('{{ $notification->id }}')"
                                                    class="text-[color:var(--color-primary-500)] hover:text-[color:var(--color-primary-600)] text-xs">
                                                Mark as Read
                                            </button>
                                        @endif

                                        <button onclick="deleteNotification('{{ $notification->id }}')"
                                                class="text-[color:var(--color-danger-500)] hover:text-[color:var(--color-danger-600)] text-xs">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">No notifications</h3>
                    <p class="mt-1 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">You're all caught up! No notifications to display.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function markAsRead(notificationId) {
        fetch(`/notifications/${notificationId}/mark-as-read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the notification appearance
                const notificationEl = document.getElementById(`notification-${notificationId}`);
                if (notificationEl) {
                    notificationEl.className = notificationEl.className.replace(
                        'bg-[color:var(--color-accent-50)] dark:bg-[color:var(--color-accent-900)] border-[color:var(--color-accent-200)] dark:border-[color:var(--color-accent-700)]',
                        'bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-200)]'
                    );

                    // Remove the "Mark as Read" button
                    const markAsReadBtn = notificationEl.querySelector('button[onclick*="markAsRead"]');
                    if (markAsReadBtn) {
                        markAsReadBtn.remove();
                    }
                }

                // Update unread count if displayed somewhere
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to mark notification as read');
        });
    }

    function markAllAsRead() {
        fetch('/notifications/mark-all-as-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to mark all notifications as read');
        });
    }

    function deleteNotification(notificationId) {
        if (confirm('Are you sure you want to delete this notification?')) {
            fetch(`/notifications/${notificationId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById(`notification-${notificationId}`).remove();
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete notification');
            });
        }
    }
</script>
@endpush
