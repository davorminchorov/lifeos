@extends('layouts.app')

@section('title', 'Notification Preferences - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Notification Preferences
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Customize how you receive notifications for important events
            </p>
        </div>
        <a href="{{ route('notifications.index') }}" class="bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
            Back to Notifications
        </a>
    </div>
@endsection

@section('content')
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
        <div class="px-4 py-5 sm:p-6">
            <form id="preferences-form">
                @csrf
                <div class="space-y-8">
                    <!-- Subscription Renewals -->
                    <div class="border-b border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] pb-8">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-[color:var(--color-info-500)] rounded-full flex items-center justify-center mr-4">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    Subscription Renewals
                                </h3>
                                <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                    Get notified about upcoming subscription renewals
                                </p>
                            </div>
                        </div>

                        @php $pref = $preferences['subscription_renewal'] ?? new \App\Models\UserNotificationPreference(['email_enabled' => true, 'database_enabled' => true, 'push_enabled' => false, 'settings' => ['days_before' => [7, 3, 1, 0]]]) @endphp

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-3">
                                    Notification Channels
                                </h4>
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="preferences[subscription_renewal][email_enabled]" value="1"
                                               {{ $pref->email_enabled ? 'checked' : '' }}
                                               class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Email notifications</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="preferences[subscription_renewal][database_enabled]" value="1"
                                               {{ $pref->database_enabled ? 'checked' : '' }}
                                               class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">In-app notifications</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="preferences[subscription_renewal][push_enabled]" value="1"
                                               {{ $pref->push_enabled ? 'checked' : '' }}
                                               class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Push notifications</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-3">
                                    Notification Timing (days before renewal)
                                </h4>
                                <div class="grid grid-cols-2 gap-2">
                                    @php $days = $pref->getNotificationDays() @endphp
                                    @foreach([30, 14, 7, 3, 1, 0] as $day)
                                        <label class="flex items-center">
                                            <input type="checkbox" name="preferences[subscription_renewal][days_before][]" value="{{ $day }}"
                                                   {{ in_array($day, $days) ? 'checked' : '' }}
                                                   class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                            <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                {{ $day === 0 ? 'On renewal day' : $day . ' days before' }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contract Expirations -->
                    <div class="border-b border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] pb-8">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-[color:var(--color-danger-500)] rounded-full flex items-center justify-center mr-4">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    Contract Expirations
                                </h3>
                                <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                    Get alerts about expiring contracts
                                </p>
                            </div>
                        </div>

                        @php $pref = $preferences['contract_expiration'] ?? new \App\Models\UserNotificationPreference(['email_enabled' => true, 'database_enabled' => true, 'push_enabled' => false, 'settings' => ['days_before' => [30, 7, 1]]]) @endphp

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-3">
                                    Notification Channels
                                </h4>
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="preferences[contract_expiration][email_enabled]" value="1"
                                               {{ $pref->email_enabled ? 'checked' : '' }}
                                               class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Email notifications</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="preferences[contract_expiration][database_enabled]" value="1"
                                               {{ $pref->database_enabled ? 'checked' : '' }}
                                               class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">In-app notifications</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="preferences[contract_expiration][push_enabled]" value="1"
                                               {{ $pref->push_enabled ? 'checked' : '' }}
                                               class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Push notifications</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-3">
                                    Notification Timing (days before expiration)
                                </h4>
                                <div class="grid grid-cols-2 gap-2">
                                    @php $days = $pref->getNotificationDays() @endphp
                                    @foreach([60, 30, 14, 7, 3, 1] as $day)
                                        <label class="flex items-center">
                                            <input type="checkbox" name="preferences[contract_expiration][days_before][]" value="{{ $day }}"
                                                   {{ in_array($day, $days) ? 'checked' : '' }}
                                                   class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                            <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                {{ $day }} days before
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Warranty Expirations -->
                    <div class="border-b border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] pb-8">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-[color:var(--color-warning-500)] rounded-full flex items-center justify-center mr-4">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    Warranty Expirations
                                </h3>
                                <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                    Get reminded about expiring warranties
                                </p>
                            </div>
                        </div>

                        @php $pref = $preferences['warranty_expiration'] ?? new \App\Models\UserNotificationPreference(['email_enabled' => true, 'database_enabled' => true, 'push_enabled' => false, 'settings' => ['days_before' => [30, 7, 1]]]) @endphp

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-3">
                                    Notification Channels
                                </h4>
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="preferences[warranty_expiration][email_enabled]" value="1"
                                               {{ $pref->email_enabled ? 'checked' : '' }}
                                               class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Email notifications</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="preferences[warranty_expiration][database_enabled]" value="1"
                                               {{ $pref->database_enabled ? 'checked' : '' }}
                                               class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">In-app notifications</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="preferences[warranty_expiration][push_enabled]" value="1"
                                               {{ $pref->push_enabled ? 'checked' : '' }}
                                               class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Push notifications</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-3">
                                    Notification Timing (days before expiration)
                                </h4>
                                <div class="grid grid-cols-2 gap-2">
                                    @php $days = $pref->getNotificationDays() @endphp
                                    @foreach([60, 30, 14, 7, 3, 1] as $day)
                                        <label class="flex items-center">
                                            <input type="checkbox" name="preferences[warranty_expiration][days_before][]" value="{{ $day }}"
                                                   {{ in_array($day, $days) ? 'checked' : '' }}
                                                   class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                            <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                {{ $day }} days before
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Utility Bill Due Dates -->
                    <div class="border-b border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] pb-8">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-[color:var(--color-success-500)] rounded-full flex items-center justify-center mr-4">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    Utility Bill Due Dates
                                </h3>
                                <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                    Get reminded about upcoming bill payments
                                </p>
                            </div>
                        </div>

                        @php $pref = $preferences['utility_bill_due'] ?? new \App\Models\UserNotificationPreference(['email_enabled' => true, 'database_enabled' => true, 'push_enabled' => false, 'settings' => ['days_before' => [7, 3, 1, 0]]]) @endphp

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-3">
                                    Notification Channels
                                </h4>
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="preferences[utility_bill_due][email_enabled]" value="1"
                                               {{ $pref->email_enabled ? 'checked' : '' }}
                                               class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Email notifications</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="preferences[utility_bill_due][database_enabled]" value="1"
                                               {{ $pref->database_enabled ? 'checked' : '' }}
                                               class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">In-app notifications</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="preferences[utility_bill_due][push_enabled]" value="1"
                                               {{ $pref->push_enabled ? 'checked' : '' }}
                                               class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Push notifications</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-3">
                                    Notification Timing (days before due date)
                                </h4>
                                <div class="grid grid-cols-2 gap-2">
                                    @php $days = $pref->getNotificationDays() @endphp
                                    @foreach([14, 7, 5, 3, 1, 0] as $day)
                                        <label class="flex items-center">
                                            <input type="checkbox" name="preferences[utility_bill_due][days_before][]" value="{{ $day }}"
                                                   {{ in_array($day, $days) ? 'checked' : '' }}
                                                   class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                            <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                {{ $day === 0 ? 'On due date' : $day . ' days before' }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end pt-6">
                    <button type="submit" id="save-btn" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-6 py-2 rounded-md text-sm font-medium transition-colors duration-200 shadow-sm">
                        Save Preferences
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('preferences-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const preferences = {};

        // Parse form data into preferences object
        for (let [key, value] of formData.entries()) {
            const matches = key.match(/preferences\[([^\]]+)\]\[([^\]]+)\](\[\])?/);
            if (matches) {
                const [, type, setting, isArray] = matches;

                if (!preferences[type]) {
                    preferences[type] = {};
                }

                if (isArray) {
                    if (!preferences[type][setting]) {
                        preferences[type][setting] = [];
                    }
                    preferences[type][setting].push(value);
                } else {
                    preferences[type][setting] = value === '1';
                }
            }
        }

        const saveBtn = document.getElementById('save-btn');
        const originalText = saveBtn.textContent;
        saveBtn.textContent = 'Saving...';
        saveBtn.disabled = true;

        fetch('{{ route("notifications.preferences.update") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ preferences })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const successDiv = document.createElement('div');
                successDiv.className = 'fixed top-4 right-4 bg-[color:var(--color-success-500)] text-white p-4 rounded-md shadow-lg z-50';
                successDiv.textContent = data.message;
                document.body.appendChild(successDiv);

                setTimeout(() => {
                    successDiv.remove();
                }, 3000);
            } else {
                throw new Error(data.message || 'Failed to update preferences');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to save preferences. Please try again.');
        })
        .finally(() => {
            saveBtn.textContent = originalText;
            saveBtn.disabled = false;
        });
    });
</script>
@endpush
