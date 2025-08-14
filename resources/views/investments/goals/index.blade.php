@extends('layouts.app')

@section('title', 'Investment Goals - LifeOS')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-semibold">Investment Goals</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">Track and manage your investment objectives</p>
                    </div>
                    <button onclick="openGoalModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium">
                        Add Goal
                    </button>
                </div>

                <!-- Goals List -->
                @if($goals->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($goals as $goal)
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-lg font-semibold">{{ $goal['title'] ?? 'Untitled Goal' }}</h3>
                                    <div class="flex space-x-2">
                                        <button onclick="editGoal({{ json_encode($goal) }})" class="text-indigo-600 hover:text-indigo-800">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <form method="POST" action="{{ route('investments.goals.destroy', $goal['id'] ?? 0) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this goal?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                @if(isset($goal['description']))
                                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">{{ $goal['description'] }}</p>
                                @endif

                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Target Amount</span>
                                        <span class="font-medium">${{ number_format($goal['target_amount'] ?? 0, 2) }}</span>
                                    </div>

                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Current Progress</span>
                                        <span class="font-medium">${{ number_format($goal['current_progress'] ?? 0, 2) }}</span>
                                    </div>

                                    @if(isset($goal['target_date']))
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Target Date</span>
                                            <span class="text-sm">{{ date('M j, Y', strtotime($goal['target_date'])) }}</span>
                                        </div>
                                    @endif

                                    <!-- Progress Bar -->
                                    @php
                                        $progressPercentage = ($goal['target_amount'] ?? 0) > 0 ? min(100, (($goal['current_progress'] ?? 0) / $goal['target_amount']) * 100) : 0;
                                    @endphp
                                    <div class="mt-4">
                                        <div class="flex justify-between text-sm mb-1">
                                            <span>Progress</span>
                                            <span>{{ number_format($progressPercentage, 1) }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $progressPercentage }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No investment goals</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating your first investment goal.</p>
                        <div class="mt-6">
                            <button onclick="openGoalModal()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                Add Goal
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Goal Modal -->
<div id="goalModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4" id="modalTitle">Add Investment Goal</h3>
            <form id="goalForm" method="POST" action="{{ route('investments.goals.store') }}">
                @csrf
                <input type="hidden" id="goalId" name="goal_id">
                <input type="hidden" id="formMethod" name="_method" value="POST">

                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Goal Title</label>
                        <input type="text" id="title" name="title" required class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label for="target_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Target Amount ($)</label>
                        <input type="number" id="target_amount" name="target_amount" step="0.01" min="0" required class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label for="target_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Target Date (Optional)</label>
                        <input type="date" id="target_date" name="target_date" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description (Optional)</label>
                        <textarea id="description" name="description" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"></textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeGoalModal()" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-400 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Save Goal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openGoalModal() {
    document.getElementById('goalModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Add Investment Goal';
    document.getElementById('goalForm').action = '{{ route("investments.goals.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('goalForm').reset();
    document.getElementById('goalId').value = '';
}

function editGoal(goal) {
    document.getElementById('goalModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Edit Investment Goal';
    document.getElementById('goalForm').action = `/investments/goals/${goal.id}/update`;
    document.getElementById('formMethod').value = 'PATCH';
    document.getElementById('goalId').value = goal.id;
    document.getElementById('title').value = goal.title || '';
    document.getElementById('target_amount').value = goal.target_amount || '';
    document.getElementById('target_date').value = goal.target_date || '';
    document.getElementById('description').value = goal.description || '';
}

function closeGoalModal() {
    document.getElementById('goalModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('goalModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeGoalModal();
    }
});
</script>
@endsection
