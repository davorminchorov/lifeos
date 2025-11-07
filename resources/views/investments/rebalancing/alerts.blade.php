@extends('layouts.app')

@section('title', 'Rebalancing Alerts - LifeOS')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-semibold">Portfolio Rebalancing Alerts</h2>
                        <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-1">Stay informed about your portfolio allocation and performance</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('investments.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)]">
                            ← Back to Investments
                        </a>
                        <button onclick="generateRebalancingRecommendations()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)]">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Get Recommendations
                        </button>
                    </div>
                </div>

                <!-- Alert Summary -->
                @if(count($alerts) > 0)
                    @php
                        $highAlerts = collect($alerts)->where('severity', 'high')->count();
                        $mediumAlerts = collect($alerts)->where('severity', 'medium')->count();
                        $lowAlerts = collect($alerts)->where('severity', 'low')->count();
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">High Priority</dt>
                                        <dd class="text-2xl font-bold text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $highAlerts }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-yellow-600 dark:text-[color:var(--color-warning-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Medium Priority</dt>
                                        <dd class="text-2xl font-bold text-yellow-600 dark:text-[color:var(--color-warning-400)]">{{ $mediumAlerts }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Low Priority</dt>
                                        <dd class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $lowAlerts }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alerts List -->
                    <div class="space-y-4">
                        @foreach($alerts as $alert)
                            <div class="border-l-4 {{ $alert['severity'] === 'high' ? 'border-[color:var(--color-danger-500)] bg-red-50 dark:bg-red-900/20' : ($alert['severity'] === 'medium' ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20' : 'border-blue-500 bg-blue-50 dark:bg-blue-900/20') }} p-6 rounded-r-lg">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        @if($alert['severity'] === 'high')
                                            <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                        @elseif($alert['severity'] === 'medium')
                                            <svg class="h-6 w-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                        @else
                                            <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="text-lg font-medium {{ $alert['severity'] === 'high' ? 'text-red-800 dark:text-red-200' : ($alert['severity'] === 'medium' ? 'text-yellow-800 dark:text-yellow-200' : 'text-blue-800 dark:text-blue-200') }}">
                                                {{ ucfirst(str_replace('_', ' ', $alert['type'])) }}
                                            </h3>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $alert['severity'] === 'high' ? 'bg-[color:var(--color-danger-50)] text-[color:var(--color-danger-600)] dark:bg-[color:var(--color-danger-900)] dark:text-[color:var(--color-danger-200)]' : ($alert['severity'] === 'medium' ? 'bg-[color:var(--color-warning-50)] text-[color:var(--color-warning-600)] dark:bg-[color:var(--color-warning-900)] dark:text-[color:var(--color-warning-200)]' : 'bg-[color:var(--color-info-50)] text-[color:var(--color-info-600)] dark:bg-[color:var(--color-info-900)] dark:text-[color:var(--color-info-200)]') }}">
                                                {{ ucfirst($alert['severity']) }} Priority
                                            </span>
                                        </div>
                                        <p class="text-sm {{ $alert['severity'] === 'high' ? 'text-red-700 dark:text-red-300' : ($alert['severity'] === 'medium' ? 'text-yellow-700 dark:text-yellow-300' : 'text-blue-700 dark:text-blue-300') }} mb-3">
                                            {{ $alert['message'] }}
                                        </p>
                                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] rounded-lg p-4 border {{ $alert['severity'] === 'high' ? 'border-red-200 dark:border-red-700' : ($alert['severity'] === 'medium' ? 'border-yellow-200 dark:border-yellow-700' : 'border-blue-200 dark:border-blue-700') }}">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Recommendation:</h4>
                                            <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">{{ $alert['recommendation'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- No Alerts State -->
                    <div class="text-center py-16">
                        <svg class="mx-auto h-16 w-16 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">All Clear!</h3>
                        <p class="mt-2 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Your portfolio looks well-balanced. No rebalancing alerts at this time.</p>
                        <div class="mt-6">
                            <button onclick="generateRebalancingRecommendations()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)]">
                                Generate Custom Recommendations
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Rebalancing Tips -->
                <div class="mt-12 bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Portfolio Rebalancing Guidelines</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">When to Rebalance</h4>
                            <ul class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] space-y-1">
                                <li>• Asset allocation drifts 5% or more from target</li>
                                <li>• Quarterly or semi-annual schedule</li>
                                <li>• Major life events or goal changes</li>
                                <li>• Significant market movements</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Best Practices</h4>
                            <ul class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] space-y-1">
                                <li>• Consider tax implications of selling</li>
                                <li>• Use new contributions to rebalance first</li>
                                <li>• Rebalance in tax-advantaged accounts when possible</li>
                                <li>• Don't over-rebalance due to minor fluctuations</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rebalancing Recommendations Modal -->
<div id="recommendationsModal" class="hidden fixed inset-0 bg-[color:var(--color-primary-600)] bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)]">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Target Allocation for Recommendations</h3>
            <form id="recommendationsForm" method="POST" action="{{ route('investments.rebalancing.recommendations') }}">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="allocationInputs">
                        <div>
                            <label for="stocks_percentage" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Stocks (%)</label>
                            <input type="number" id="stocks_percentage" name="target_allocation[0][percentage]" min="0" max="100" step="0.1" value="60" class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            <input type="hidden" name="target_allocation[0][type]" value="stock">
                        </div>

                        <div>
                            <label for="bonds_percentage" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Bonds (%)</label>
                            <input type="number" id="bonds_percentage" name="target_allocation[1][percentage]" min="0" max="100" step="0.1" value="30" class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            <input type="hidden" name="target_allocation[1][type]" value="bond">
                        </div>

                        <div>
                            <label for="cash_percentage" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Cash (%)</label>
                            <input type="number" id="cash_percentage" name="target_allocation[2][percentage]" min="0" max="100" step="0.1" value="10" class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            <input type="hidden" name="target_allocation[2][type]" value="cash">
                        </div>
                    </div>

                    <div class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                        <p>Note: Total allocation should equal 100%. Adjust percentages based on your risk tolerance and investment goals.</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeRecommendationsModal()" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] rounded-md hover:bg-gray-400 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Generate Recommendations
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function generateRebalancingRecommendations() {
    document.getElementById('recommendationsModal').classList.remove('hidden');
}

function closeRecommendationsModal() {
    document.getElementById('recommendationsModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('recommendationsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRecommendationsModal();
    }
});

// Validate total allocation on form submit
document.getElementById('recommendationsForm').addEventListener('submit', function(e) {
    const stocksInput = document.getElementById('stocks_percentage');
    const bondsInput = document.getElementById('bonds_percentage');
    const cashInput = document.getElementById('cash_percentage');

    const total = parseFloat(stocksInput.value || 0) + parseFloat(bondsInput.value || 0) + parseFloat(cashInput.value || 0);

    if (Math.abs(total - 100) > 0.1) {
        e.preventDefault();
        alert('Total allocation must equal 100%. Current total: ' + total.toFixed(1) + '%');
        return false;
    }
});
</script>
@endsection
