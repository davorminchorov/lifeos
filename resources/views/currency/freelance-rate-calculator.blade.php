@extends('layouts.app')

@section('title', 'Freelance Hourly Rate Calculator')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Freelance Hourly Rate Calculator
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Calculate your ideal freelance hourly rate based on your desired income and expenses.
            </p>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Input Form -->
        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] shadow-sm rounded-lg p-6">
            <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                Your Information
            </h2>

            <!-- Work Type Toggle -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-3">
                    Work Type
                </label>
                <div class="flex gap-3">
                    <button type="button" id="part-time-btn" onclick="setWorkType('part-time')" class="flex-1 px-4 py-3 text-sm font-medium rounded-lg border-2 transition-all duration-200 border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] hover:border-[color:var(--color-accent-500)]">
                        <div class="font-semibold">Part-Time</div>
                        <div class="text-xs mt-1">20-30 hours/week</div>
                    </button>
                    <button type="button" id="full-time-btn" onclick="setWorkType('full-time')" class="flex-1 px-4 py-3 text-sm font-medium rounded-lg border-2 transition-all duration-200 bg-[color:var(--color-accent-500)] border-[color:var(--color-accent-500)] text-white">
                        <div class="font-semibold">Full-Time</div>
                        <div class="text-xs mt-1">40 hours/week</div>
                    </button>
                </div>
            </div>

            <form id="rate-calculator-form" class="space-y-4">
                <!-- Currency Selection -->
                <div>
                    <label for="currency" class="block text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-1">
                        Currency
                    </label>
                    <select id="currency" class="w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-lg bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] focus:ring-2 focus:ring-[color:var(--color-accent-500)] focus:border-transparent" onchange="calculateRates()">
                        <option value="MKD" data-symbol="MKD">MKD - Macedonian Denar</option>
                        <option value="USD" data-symbol="$" selected>USD - US Dollar</option>
                        <option value="EUR" data-symbol="€">EUR - Euro</option>
                        <option value="GBP" data-symbol="£">GBP - British Pound</option>
                        <option value="CAD" data-symbol="C$">CAD - Canadian Dollar</option>
                        <option value="AUD" data-symbol="A$">AUD - Australian Dollar</option>
                        <option value="JPY" data-symbol="¥">JPY - Japanese Yen</option>
                        <option value="CHF" data-symbol="CHF">CHF - Swiss Franc</option>
                        <option value="RSD" data-symbol="RSD">RSD - Serbian Dinar</option>
                        <option value="BGN" data-symbol="лв">BGN - Bulgarian Lev</option>
                    </select>
                </div>

                <!-- Desired Annual Income -->
                <div>
                    <label for="annual-income" class="block text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-1">
                        Desired Annual Income
                    </label>
                    <input type="number" id="annual-income" value="60000" min="0" step="1000" class="w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-lg bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] focus:ring-2 focus:ring-[color:var(--color-accent-500)] focus:border-transparent" oninput="calculateRates()">
                </div>

                <!-- Working Hours per Week -->
                <div>
                    <label for="hours-per-week" class="block text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-1">
                        Working Hours per Week
                    </label>
                    <input type="number" id="hours-per-week" value="40" min="1" max="80" step="1" class="w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-lg bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] focus:ring-2 focus:ring-[color:var(--color-accent-500)] focus:border-transparent" oninput="calculateRates()">
                </div>

                <!-- Working Weeks per Year -->
                <div>
                    <label for="weeks-per-year" class="block text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-1">
                        Working Weeks per Year
                    </label>
                    <input type="number" id="weeks-per-year" value="48" min="1" max="52" step="1" class="w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-lg bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] focus:ring-2 focus:ring-[color:var(--color-accent-500)] focus:border-transparent" oninput="calculateRates()">
                    <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">
                        52 weeks minus vacation time (typically 2-4 weeks)
                    </p>
                </div>

                <!-- Business Expenses -->
                <div>
                    <label for="expenses-percentage" class="block text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-1">
                        Business Expenses (%)
                    </label>
                    <input type="number" id="expenses-percentage" value="20" min="0" max="100" step="1" class="w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-lg bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] focus:ring-2 focus:ring-[color:var(--color-accent-500)] focus:border-transparent" oninput="calculateRates()">
                    <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">
                        Software, equipment, insurance, etc. (typically 15-30%)
                    </p>
                </div>

                <!-- Profit Margin -->
                <div>
                    <label for="profit-margin" class="block text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-1">
                        Profit Margin (%)
                    </label>
                    <input type="number" id="profit-margin" value="15" min="0" max="100" step="1" class="w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-lg bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] focus:ring-2 focus:ring-[color:var(--color-accent-500)] focus:border-transparent" oninput="calculateRates()">
                    <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">
                        Your business profit (typically 10-20%)
                    </p>
                </div>

                <!-- Tax Rate -->
                <div>
                    <label for="tax-rate" class="block text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-1">
                        Tax Rate (%)
                    </label>
                    <input type="number" id="tax-rate" value="25" min="0" max="100" step="1" class="w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-lg bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] focus:ring-2 focus:ring-[color:var(--color-accent-500)] focus:border-transparent" oninput="calculateRates()">
                    <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">
                        Your estimated tax rate (varies by location)
                    </p>
                </div>
            </form>
        </div>

        <!-- Results -->
        <div class="space-y-4">
            <!-- Hourly Rate Card -->
            <div class="bg-gradient-to-br from-[color:var(--color-accent-500)] to-[color:var(--color-accent-600)] shadow-lg rounded-lg p-6 text-white">
                <h3 class="text-sm font-medium opacity-90 mb-2">Recommended Hourly Rate</h3>
                <div class="text-4xl font-bold" id="hourly-rate">$0.00</div>
                <p class="text-sm opacity-90 mt-2">Per hour</p>
            </div>

            <!-- Weekly Rate Card -->
            <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-1">Weekly Income</h3>
                        <div class="text-2xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]" id="weekly-income">$0.00</div>
                    </div>
                    <svg class="w-12 h-12 text-[color:var(--color-accent-500)] opacity-20" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>

            <!-- Monthly Rate Card -->
            <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-1">Monthly Income</h3>
                        <div class="text-2xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]" id="monthly-income">$0.00</div>
                    </div>
                    <svg class="w-12 h-12 text-[color:var(--color-accent-500)] opacity-20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>

            <!-- Yearly Rate Card -->
            <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-1">Yearly Income</h3>
                        <div class="text-2xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]" id="yearly-income">$0.00</div>
                    </div>
                    <svg class="w-12 h-12 text-[color:var(--color-accent-500)] opacity-20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>

            <!-- MKD Conversion Card -->
            <div id="mkd-conversion-card" class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] border border-[color:var(--color-success-200)] dark:border-[color:var(--color-success-500)] shadow-sm rounded-lg p-6" style="display: none;">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h3 class="text-sm font-medium text-[color:var(--color-success-700)] dark:text-[color:var(--color-success-400)] mb-1">MKD Conversion</h3>
                        <div class="text-xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]" id="mkd-hourly-rate">MKD 0.00</div>
                        <p class="text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-1">Hourly rate in Macedonian Denar</p>
                        <div class="mt-3 space-y-1 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                            <div class="flex justify-between">
                                <span class="text-xs">Weekly:</span>
                                <span class="font-medium" id="mkd-weekly-income">MKD 0.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs">Monthly:</span>
                                <span class="font-medium" id="mkd-monthly-income">MKD 0.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs">Yearly:</span>
                                <span class="font-medium" id="mkd-yearly-income">MKD 0.00</span>
                            </div>
                        </div>
                        <p class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)] mt-2 italic" id="mkd-rate-info">Exchange rate: 1 USD = 0.00 MKD</p>
                    </div>
                    <svg class="w-12 h-12 text-[color:var(--color-success-500)] opacity-20 flex-shrink-0 ml-3" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>

            <!-- Rate Breakdown -->
            <div class="bg-[color:var(--color-info-50)] dark:bg-[color:var(--color-info-900)] border border-[color:var(--color-info-200)] dark:border-[color:var(--color-info-800)] rounded-lg p-6">
                <h3 class="text-sm font-medium text-[color:var(--color-info-800)] dark:text-[color:var(--color-info-200)] mb-3">Rate Breakdown</h3>
                <div class="space-y-2 text-sm text-[color:var(--color-info-700)] dark:text-[color:var(--color-info-300)]">
                    <div class="flex justify-between">
                        <span>Base hourly rate:</span>
                        <span id="base-rate" class="font-medium">$0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>+ Business expenses (<span id="expenses-display">0</span>%):</span>
                        <span id="expenses-amount" class="font-medium">$0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>+ Profit margin (<span id="profit-display">0</span>%):</span>
                        <span id="profit-amount" class="font-medium">$0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>+ Taxes (<span id="tax-display">0</span>%):</span>
                        <span id="tax-amount" class="font-medium">$0.00</span>
                    </div>
                    <div class="pt-2 border-t border-[color:var(--color-info-200)] dark:border-[color:var(--color-info-800)] flex justify-between font-semibold">
                        <span>Total hourly rate:</span>
                        <span id="total-rate">$0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Information Box -->
    <div class="mt-6 bg-[color:var(--color-info-50)] dark:bg-[color:var(--color-info-900)] border border-[color:var(--color-info-200)] dark:border-[color:var(--color-info-800)] rounded-lg p-6">
        <div class="flex">
            <svg class="w-5 h-5 text-[color:var(--color-info-400)] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-[color:var(--color-info-800)] dark:text-[color:var(--color-info-200)]">How to Use This Calculator</h3>
                <div class="mt-1 text-sm text-[color:var(--color-info-700)] dark:text-[color:var(--color-info-300)]">
                    <p class="mb-2">This calculator helps you determine a sustainable freelance hourly rate by accounting for:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li><span class="font-medium">Desired Income:</span> What you want to take home annually</li>
                        <li><span class="font-medium">Business Expenses:</span> Costs like software, equipment, and insurance</li>
                        <li><span class="font-medium">Profit Margin:</span> Additional buffer for business growth and savings</li>
                        <li><span class="font-medium">Taxes:</span> Self-employment and income taxes</li>
                    </ul>
                    <p class="mt-3">Adjust the inputs to find the right rate for your situation. The calculator updates in real-time.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let currentWorkType = 'full-time';

    // Hardcoded exchange rates to MKD (approximate values)
    const exchangeRatesToMKD = {
        'USD': 56.50,
        'EUR': 61.50,
        'GBP': 72.00,
        'CAD': 41.50,
        'AUD': 37.00,
        'JPY': 0.38,
        'CHF': 64.00,
        'RSD': 0.52,
        'BGN': 31.50,
        'MKD': 1.00
    };

    function setWorkType(type) {
        currentWorkType = type;

        const partTimeBtn = document.getElementById('part-time-btn');
        const fullTimeBtn = document.getElementById('full-time-btn');
        const hoursInput = document.getElementById('hours-per-week');

        if (type === 'part-time') {
            // Style part-time as active
            partTimeBtn.classList.add('bg-[color:var(--color-accent-500)]', 'border-[color:var(--color-accent-500)]', 'text-white');
            partTimeBtn.classList.remove('border-[color:var(--color-primary-300)]', 'dark:border-[color:var(--color-dark-300)]', 'text-[color:var(--color-primary-600)]', 'dark:text-[color:var(--color-dark-500)]');

            // Style full-time as inactive
            fullTimeBtn.classList.remove('bg-[color:var(--color-accent-500)]', 'border-[color:var(--color-accent-500)]', 'text-white');
            fullTimeBtn.classList.add('border-[color:var(--color-primary-300)]', 'dark:border-[color:var(--color-dark-300)]', 'text-[color:var(--color-primary-600)]', 'dark:text-[color:var(--color-dark-500)]');

            hoursInput.value = 25;
        } else {
            // Style full-time as active
            fullTimeBtn.classList.add('bg-[color:var(--color-accent-500)]', 'border-[color:var(--color-accent-500)]', 'text-white');
            fullTimeBtn.classList.remove('border-[color:var(--color-primary-300)]', 'dark:border-[color:var(--color-dark-300)]', 'text-[color:var(--color-primary-600)]', 'dark:text-[color:var(--color-dark-500)]');

            // Style part-time as inactive
            partTimeBtn.classList.remove('bg-[color:var(--color-accent-500)]', 'border-[color:var(--color-accent-500)]', 'text-white');
            partTimeBtn.classList.add('border-[color:var(--color-primary-300)]', 'dark:border-[color:var(--color-dark-300)]', 'text-[color:var(--color-primary-600)]', 'dark:text-[color:var(--color-dark-500)]');

            hoursInput.value = 40;
        }

        calculateRates();
    }

    function getCurrencySymbol() {
        const currencySelect = document.getElementById('currency');
        const selectedOption = currencySelect.options[currencySelect.selectedIndex];
        return selectedOption.getAttribute('data-symbol');
    }

    function getCurrencyCode() {
        return document.getElementById('currency').value;
    }

    function formatCurrency(amount, symbol, decimals = 2) {
        return symbol + ' ' + amount.toFixed(decimals);
    }

    function getExchangeRate(fromCurrency, toCurrency) {
        // Return 1 if same currency
        if (fromCurrency === toCurrency) {
            return 1.0;
        }

        // Return hardcoded exchange rate to MKD
        if (toCurrency === 'MKD' && exchangeRatesToMKD[fromCurrency]) {
            return exchangeRatesToMKD[fromCurrency];
        }

        // If converting from MKD to another currency
        if (fromCurrency === 'MKD' && exchangeRatesToMKD[toCurrency]) {
            return 1.0 / exchangeRatesToMKD[toCurrency];
        }

        return null;
    }

    function calculateRates() {
        // Get input values
        const annualIncome = parseFloat(document.getElementById('annual-income').value) || 0;
        const hoursPerWeek = parseFloat(document.getElementById('hours-per-week').value) || 0;
        const weeksPerYear = parseFloat(document.getElementById('weeks-per-year').value) || 0;
        const expensesPercentage = parseFloat(document.getElementById('expenses-percentage').value) || 0;
        const profitMargin = parseFloat(document.getElementById('profit-margin').value) || 0;
        const taxRate = parseFloat(document.getElementById('tax-rate').value) || 0;

        // Get currency info
        const currencySymbol = getCurrencySymbol();
        const currencyCode = getCurrencyCode();

        // Calculate total billable hours per year
        const totalHoursPerYear = hoursPerWeek * weeksPerYear;

        if (totalHoursPerYear === 0) {
            resetDisplay(currencySymbol);
            return;
        }

        // Calculate base hourly rate (desired income / total hours)
        const baseHourlyRate = annualIncome / totalHoursPerYear;

        // Calculate additional costs per hour
        const expensesPerHour = baseHourlyRate * (expensesPercentage / 100);
        const profitPerHour = baseHourlyRate * (profitMargin / 100);

        // Calculate subtotal before taxes
        const subtotal = baseHourlyRate + expensesPerHour + profitPerHour;

        // Calculate taxes on the subtotal
        const taxPerHour = subtotal * (taxRate / 100);

        // Calculate final hourly rate
        const finalHourlyRate = subtotal + taxPerHour;

        // Calculate weekly, monthly, and yearly incomes
        const weeklyIncome = finalHourlyRate * hoursPerWeek;
        const monthlyIncome = (finalHourlyRate * hoursPerWeek * weeksPerYear) / 12;
        const yearlyIncome = finalHourlyRate * totalHoursPerYear;

        // Update display with selected currency
        document.getElementById('hourly-rate').textContent = formatCurrency(finalHourlyRate, currencySymbol);
        document.getElementById('weekly-income').textContent = formatCurrency(weeklyIncome, currencySymbol);
        document.getElementById('monthly-income').textContent = formatCurrency(monthlyIncome, currencySymbol);
        document.getElementById('yearly-income').textContent = formatCurrency(yearlyIncome, currencySymbol);

        // Update breakdown
        document.getElementById('base-rate').textContent = formatCurrency(baseHourlyRate, currencySymbol);
        document.getElementById('expenses-display').textContent = expensesPercentage.toFixed(0);
        document.getElementById('expenses-amount').textContent = formatCurrency(expensesPerHour, currencySymbol);
        document.getElementById('profit-display').textContent = profitMargin.toFixed(0);
        document.getElementById('profit-amount').textContent = formatCurrency(profitPerHour, currencySymbol);
        document.getElementById('tax-display').textContent = taxRate.toFixed(0);
        document.getElementById('tax-amount').textContent = formatCurrency(taxPerHour, currencySymbol);
        document.getElementById('total-rate').textContent = formatCurrency(finalHourlyRate, currencySymbol);

        // Handle MKD conversion
        const mkdCard = document.getElementById('mkd-conversion-card');
        if (currencyCode !== 'MKD') {
            // Get exchange rate to MKD
            const rate = getExchangeRate(currencyCode, 'MKD');

            if (rate) {
                // Convert all amounts to MKD
                const mkdHourlyRate = finalHourlyRate * rate;
                const mkdWeeklyIncome = weeklyIncome * rate;
                const mkdMonthlyIncome = monthlyIncome * rate;
                const mkdYearlyIncome = yearlyIncome * rate;

                // Update MKD display
                document.getElementById('mkd-hourly-rate').textContent = formatCurrency(mkdHourlyRate, 'MKD');
                document.getElementById('mkd-weekly-income').textContent = formatCurrency(mkdWeeklyIncome, 'MKD');
                document.getElementById('mkd-monthly-income').textContent = formatCurrency(mkdMonthlyIncome, 'MKD');
                document.getElementById('mkd-yearly-income').textContent = formatCurrency(mkdYearlyIncome, 'MKD');
                document.getElementById('mkd-rate-info').textContent = `Exchange rate: 1 ${currencyCode} = ${rate.toFixed(2)} MKD`;

                // Show MKD card
                mkdCard.style.display = 'block';
            } else {
                mkdCard.style.display = 'none';
            }
        } else {
            // Hide MKD card when MKD is selected
            mkdCard.style.display = 'none';
        }
    }

    function resetDisplay(symbol = '$') {
        document.getElementById('hourly-rate').textContent = symbol + ' 0.00';
        document.getElementById('weekly-income').textContent = symbol + ' 0.00';
        document.getElementById('monthly-income').textContent = symbol + ' 0.00';
        document.getElementById('yearly-income').textContent = symbol + ' 0.00';
        document.getElementById('base-rate').textContent = symbol + ' 0.00';
        document.getElementById('expenses-amount').textContent = symbol + ' 0.00';
        document.getElementById('profit-amount').textContent = symbol + ' 0.00';
        document.getElementById('tax-amount').textContent = symbol + ' 0.00';
        document.getElementById('total-rate').textContent = symbol + ' 0.00';

        // Hide MKD card
        document.getElementById('mkd-conversion-card').style.display = 'none';
    }

    // Calculate on page load
    document.addEventListener('DOMContentLoaded', function() {
        calculateRates();
    });
</script>
@endpush
