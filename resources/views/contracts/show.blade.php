@extends('layouts.app')

@section('title', $contract->title . ' - Contracts - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-4">
                    <li>
                        <div>
                            <a href="{{ route('contracts.index') }}" class="text-gray-400 hover:text-gray-500">
                                <svg class="flex-shrink-0 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0L3.586 10l4.707-4.707a1 1 0 011.414 1.414L6.414 10l3.293 3.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                                </svg>
                                <span class="sr-only">Back</span>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <a href="{{ route('contracts.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Contracts</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-gray-500 dark:text-gray-400">{{ $contract->title }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                {{ $contract->title }}
            </h1>
            <div class="mt-1 flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium
                    @if($contract->status === 'active') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                    @elseif($contract->status === 'expired') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                    @elseif($contract->status === 'terminated') bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100
                    @else bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100 @endif">
                    {{ ucfirst($contract->status) }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                    {{ ucfirst(str_replace('_', ' ', $contract->contract_type)) }}
                </span>
            </div>
        </div>
        <div class="flex space-x-3">
            @if($contract->status === 'active')
                <button type="button" onclick="document.getElementById('terminate-modal').classList.remove('hidden')"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Terminate
                </button>
                <button type="button" onclick="document.getElementById('renew-modal').classList.remove('hidden')"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Renew
                </button>
            @endif
            <a href="{{ route('contracts.edit', $contract) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Edit Contract
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Contract Details -->
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Contract Information</h3>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700">
                    <dl>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Counterparty</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $contract->counterparty }}</dd>
                        </div>
                        <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $contract->start_date->format('F j, Y') }}</dd>
                        </div>
                        @if($contract->end_date)
                            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">End Date</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                                    {{ $contract->end_date->format('F j, Y') }}
                                    @if($contract->days_until_expiration !== null)
                                        @if($contract->days_until_expiration < 0)
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                                Expired {{ abs($contract->days_until_expiration) }} days ago
                                            </span>
                                        @elseif($contract->days_until_expiration <= 30)
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-100">
                                                {{ $contract->days_until_expiration }} days remaining
                                            </span>
                                        @endif
                                    @endif
                                </dd>
                            </div>
                        @endif
                        @if($contract->contract_value)
                            <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Contract Value</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">${{ number_format($contract->contract_value, 2) }}</dd>
                            </div>
                        @endif
                        @if($contract->payment_terms)
                            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment Terms</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $contract->payment_terms }}</dd>
                            </div>
                        @endif
                        @if($contract->notice_period_days)
                            <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notice Period</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                                    {{ $contract->notice_period_days }} days
                                    @if($contract->notice_deadline)
                                        <span class="ml-2 text-gray-500 dark:text-gray-400">
                                            (Notice by {{ $contract->notice_deadline->format('M j, Y') }})
                                        </span>
                                    @endif
                                </dd>
                            </div>
                        @endif
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Auto Renewal</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                                @if($contract->auto_renewal)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                        Enabled
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100">
                                        Disabled
                                    </span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Contract Terms -->
            @if($contract->key_obligations || $contract->penalties || $contract->termination_clauses)
                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Contract Terms</h3>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700">
                        <dl>
                            @if($contract->key_obligations)
                                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Key Obligations</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2 whitespace-pre-line">{{ $contract->key_obligations }}</dd>
                                </div>
                            @endif
                            @if($contract->penalties)
                                <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Penalties</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2 whitespace-pre-line">{{ $contract->penalties }}</dd>
                                </div>
                            @endif
                            @if($contract->termination_clauses)
                                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Termination Clauses</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2 whitespace-pre-line">{{ $contract->termination_clauses }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            @endif

            <!-- Notes -->
            @if($contract->notes)
                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Notes</h3>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white whitespace-pre-line">{{ $contract->notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Performance Rating -->
            @if($contract->performance_rating)
                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Performance</h3>
                        <div class="mt-4">
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="h-5 w-5 {{ $i <= $contract->performance_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                @endfor
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ $contract->performance_rating }}/5</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Documents -->
            @if($contract->document_attachments && count($contract->document_attachments) > 0)
                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Documents</h3>
                        <div class="mt-4 space-y-2">
                            @foreach($contract->document_attachments as $document)
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-sm text-gray-900 dark:text-white">{{ basename($document) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Renewal History -->
            @if($contract->renewal_history && count($contract->renewal_history) > 0)
                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Renewal History</h3>
                        <div class="mt-4 space-y-3">
                            @foreach($contract->renewal_history as $renewal)
                                <div class="border-l-2 border-indigo-200 pl-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ ucfirst($renewal['action'] ?? 'Renewed') }}
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($renewal['date'])->format('M j, Y') }}
                                        </span>
                                    </div>
                                    @if(isset($renewal['previous_end_date']) && isset($renewal['new_end_date']))
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                            Extended from {{ \Carbon\Carbon::parse($renewal['previous_end_date'])->format('M j, Y') }}
                                            to {{ \Carbon\Carbon::parse($renewal['new_end_date'])->format('M j, Y') }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Amendments -->
            @if($contract->amendments && count($contract->amendments) > 0)
                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Amendments</h3>
                        <div class="mt-4 space-y-3">
                            @foreach($contract->amendments as $amendment)
                                <div class="border-l-2 border-yellow-200 pl-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($amendment['date'])->format('M j, Y') }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-900 dark:text-white mt-1">
                                        {{ $amendment['description'] ?? $amendment['change'] ?? 'Amendment made' }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Quick Actions</h3>
                    <div class="mt-4 space-y-2">
                        <button type="button" onclick="document.getElementById('amendment-modal').classList.remove('hidden')"
                                class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md">
                            Add Amendment
                        </button>
                        @if($contract->status === 'active')
                            <button type="button" onclick="document.getElementById('terminate-modal').classList.remove('hidden')"
                                    class="w-full text-left px-3 py-2 text-sm text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900 rounded-md">
                                Terminate Contract
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terminate Modal -->
    <div id="terminate-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Terminate Contract</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Are you sure you want to terminate this contract? This action cannot be undone.</p>
                <form method="POST" action="{{ route('contracts.terminate', $contract) }}" class="mt-4">
                    @csrf
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('terminate-modal').classList.add('hidden')"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md">
                            Terminate Contract
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Renew Modal -->
    <div id="renew-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Renew Contract</h3>
                <form method="POST" action="{{ route('contracts.renew', $contract) }}" class="mt-4">
                    @csrf
                    <div class="mb-4">
                        <label for="new_end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New End Date</label>
                        <input type="date" name="new_end_date" id="new_end_date" required
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('renew-modal').classList.add('hidden')"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md">
                            Renew Contract
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Amendment Modal -->
    <div id="amendment-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Add Amendment</h3>
                <form method="POST" action="{{ route('contracts.add-amendment', $contract) }}" class="mt-4">
                    @csrf
                    <div class="mb-4">
                        <label for="amendment_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amendment Description</label>
                        <textarea name="amendment_description" id="amendment_description" rows="3" required
                                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Describe the changes made to the contract..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('amendment-modal').classList.add('hidden')"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md">
                            Add Amendment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
