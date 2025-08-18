@extends('layouts.app')

@section('title', 'Application Settings - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Application Settings
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Customize your LifeOS experience and interface preferences
            </p>
        </div>
        <a href="{{ route('settings.index') }}" class="bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
            Back to Settings
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        <!-- Theme & Appearance -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            Theme & Appearance
                        </h3>
                        <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                            Choose your preferred theme and visual settings
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-3">
                            Theme Preference
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Light Theme -->
                            <div class="border-2 border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-lg p-4 cursor-pointer hover:border-[color:var(--color-accent-500)] transition-colors duration-200" onclick="setTheme('light')">
                                <div class="flex items-center mb-2">
                                    <div class="w-4 h-4 bg-white border border-gray-300 rounded-full mr-2"></div>
                                    <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Light Mode</span>
                                </div>
                                <div class="h-16 bg-gradient-to-r from-blue-100 to-blue-200 rounded border border-gray-200"></div>
                                <p class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-2">Clean and bright interface</p>
                            </div>

                            <!-- Dark Theme -->
                            <div class="border-2 border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-lg p-4 cursor-pointer hover:border-[color:var(--color-accent-500)] transition-colors duration-200" onclick="setTheme('dark')">
                                <div class="flex items-center mb-2">
                                    <div class="w-4 h-4 bg-gray-800 border border-gray-600 rounded-full mr-2"></div>
                                    <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Dark Mode</span>
                                </div>
                                <div class="h-16 bg-gradient-to-r from-gray-800 to-gray-900 rounded border border-gray-700"></div>
                                <p class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-2">Easy on the eyes</p>
                            </div>

                            <!-- System Theme -->
                            <div class="border-2 border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-lg p-4 cursor-pointer hover:border-[color:var(--color-accent-500)] transition-colors duration-200" onclick="setTheme('system')">
                                <div class="flex items-center mb-2">
                                    <div class="w-4 h-4 bg-gradient-to-r from-white to-gray-800 border border-gray-400 rounded-full mr-2"></div>
                                    <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">System</span>
                                </div>
                                <div class="h-16 bg-gradient-to-r from-blue-100 via-gray-400 to-gray-800 rounded border border-gray-400"></div>
                                <p class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-2">Match system preferences</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-md p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div class="text-sm">
                                <p class="text-blue-700 dark:text-blue-300 font-medium">Theme Changes</p>
                                <p class="mt-1 text-blue-600 dark:text-blue-400">Theme changes are applied immediately and saved automatically. Your preference will be remembered across sessions.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Display Preferences -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            Display Preferences
                        </h3>
                        <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                            Customize how information is displayed
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="date_format" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                Date Format
                            </label>
                            <select name="date_format" id="date_format" class="mt-1 block w-full border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                <option value="m/d/Y">MM/DD/YYYY ({{ date('m/d/Y') }})</option>
                                <option value="d/m/Y">DD/MM/YYYY ({{ date('d/m/Y') }})</option>
                                <option value="Y-m-d">YYYY-MM-DD ({{ date('Y-m-d') }})</option>
                                <option value="F j, Y">Month Day, Year ({{ date('F j, Y') }})</option>
                            </select>
                        </div>

                        <div>
                            <label for="currency_format" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                Currency Format
                            </label>
                            <select name="currency_format" id="currency_format" class="mt-1 block w-full border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                <option value="USD">USD ($1,234.56)</option>
                                <option value="EUR">EUR (€1,234.56)</option>
                                <option value="GBP">GBP (£1,234.56)</option>
                                <option value="JPY">JPY (¥1,235)</option>
                                <option value="CAD">CAD (C$1,234.56)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="items_per_page" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                Items Per Page
                            </label>
                            <select name="items_per_page" id="items_per_page" class="mt-1 block w-full border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                <option value="10">10 items</option>
                                <option value="25">25 items</option>
                                <option value="50">50 items</option>
                                <option value="100">100 items</option>
                            </select>
                        </div>

                        <div>
                            <label for="timezone" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                Timezone
                            </label>
                            <select name="timezone" id="timezone" class="mt-1 block w-full border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                <option value="America/New_York">Eastern Time (UTC-5)</option>
                                <option value="America/Chicago">Central Time (UTC-6)</option>
                                <option value="America/Denver">Mountain Time (UTC-7)</option>
                                <option value="America/Los_Angeles">Pacific Time (UTC-8)</option>
                                <option value="UTC">UTC (UTC+0)</option>
                                <option value="Europe/London">London (UTC+0)</option>
                                <option value="Europe/Paris">Paris (UTC+1)</option>
                                <option value="Asia/Tokyo">Tokyo (UTC+9)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="button" onclick="saveDisplayPreferences()" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 shadow-sm">
                        Save Preferences
                    </button>
                </div>
            </div>
        </div>

        <!-- Dashboard Customization -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            Dashboard Customization
                        </h3>
                        <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                            Choose which widgets to display on your dashboard
                        </p>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox" checked class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Quick Stats Overview</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" checked class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Recent Notifications</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" checked class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Subscription Summary</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" checked class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Upcoming Renewals</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Investment Performance Chart</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Monthly Expense Breakdown</span>
                    </label>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="button" onclick="saveDashboardPreferences()" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 shadow-sm">
                        Save Dashboard Layout
                    </button>
                </div>
            </div>
        </div>

        <!-- Accessibility -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            Accessibility Options
                        </h3>
                        <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                            Enhance usability and accessibility
                        </p>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">High contrast mode</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Larger text size</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Reduce motion effects</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" checked class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Keyboard navigation support</span>
                    </label>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="button" onclick="saveAccessibilityPreferences()" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 shadow-sm">
                        Save Accessibility Settings
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="flex flex-wrap gap-4 justify-center">
            <a href="{{ route('settings.account') }}" class="bg-[color:var(--color-primary-300)] hover:bg-[color:var(--color-primary-400)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Account Settings
            </a>
            <a href="{{ route('notifications.preferences') }}" class="bg-[color:var(--color-primary-300)] hover:bg-[color:var(--color-primary-400)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Notifications
            </a>
            <a href="{{ route('dashboard') }}" class="bg-[color:var(--color-primary-300)] hover:bg-[color:var(--color-primary-400)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Back to Dashboard
            </a>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    function setTheme(theme) {
        if (theme === 'system') {
            localStorage.removeItem('color-theme');
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        } else if (theme === 'dark') {
            localStorage.setItem('color-theme', 'dark');
            document.documentElement.classList.add('dark');
        } else {
            localStorage.setItem('color-theme', 'light');
            document.documentElement.classList.remove('dark');
        }

        // Update theme toggle icons
        const darkIcon = document.getElementById('theme-toggle-dark-icon');
        const lightIcon = document.getElementById('theme-toggle-light-icon');
        if (document.documentElement.classList.contains('dark')) {
            darkIcon.classList.add('hidden');
            lightIcon.classList.remove('hidden');
        } else {
            darkIcon.classList.remove('hidden');
            lightIcon.classList.add('hidden');
        }

        showNotification('Theme updated successfully!');
    }

    function saveDisplayPreferences() {
        const preferences = {
            date_format: document.getElementById('date_format').value,
            currency_format: document.getElementById('currency_format').value,
            items_per_page: document.getElementById('items_per_page').value,
            timezone: document.getElementById('timezone').value
        };

        // In a real application, you would send this to the server
        localStorage.setItem('display_preferences', JSON.stringify(preferences));
        showNotification('Display preferences saved successfully!');
    }

    function saveDashboardPreferences() {
        // In a real application, you would collect checkbox states and send to server
        showNotification('Dashboard layout saved successfully!');
    }

    function saveAccessibilityPreferences() {
        // In a real application, you would collect checkbox states and send to server
        showNotification('Accessibility settings saved successfully!');
    }

    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-green-500 text-white p-4 rounded-md shadow-lg z-50';
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Load saved preferences on page load
    document.addEventListener('DOMContentLoaded', function() {
        const savedPreferences = localStorage.getItem('display_preferences');
        if (savedPreferences) {
            const prefs = JSON.parse(savedPreferences);
            document.getElementById('date_format').value = prefs.date_format || 'm/d/Y';
            document.getElementById('currency_format').value = prefs.currency_format || 'USD';
            document.getElementById('items_per_page').value = prefs.items_per_page || '25';
            document.getElementById('timezone').value = prefs.timezone || 'America/New_York';
        }
    });
</script>
@endpush
