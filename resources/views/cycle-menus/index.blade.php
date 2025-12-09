@extends('layouts.app')

@section('title', 'Cycle Menus - LifeOS')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Cycle Menus</h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Plan meals in repeating cycles and see each day’s items.</p>
        </div>
        @can('create', App\Models\CycleMenu::class)
            <a href="{{ route('cycle-menus.create') }}" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium">New Cycle Menu</a>
        @endcan
    </div>
@endsection

@section('content')
    @if (session('status'))
        <div class="mb-4 rounded-md bg-[color:var(--color-accent-50)] text-[color:var(--color-accent-700)] px-4 py-3">{{ session('status') }}</div>
    @endif

    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wider text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Starts On</th>
                            <th class="px-4 py-3">Length</th>
                            <th class="px-4 py-3">Active</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                        @forelse ($menus as $menu)
                            <tr class="text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                <td class="px-4 py-3 font-medium">
                                    <a href="{{ route('cycle-menus.show', $menu) }}" class="hover:underline">{{ $menu->name }}</a>
                                </td>
                                <td class="px-4 py-3">{{ optional($menu->starts_on)->format('Y-m-d') ?: '—' }}</td>
                                <td class="px-4 py-3">{{ $menu->cycle_length_days }} days</td>
                                <td class="px-4 py-3">
                                    @if ($menu->is_active)
                                        <span class="inline-flex items-center rounded-full bg-[color:var(--color-success-100)] text-[color:var(--color-success-700)] px-2 py-1 text-xs">Active</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-[color:var(--color-primary-200)] text-[color:var(--color-primary-600)] px-2 py-1 text-xs">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        @can('update', $menu)
                                            <a href="{{ route('cycle-menus.edit', $menu) }}" class="text-[color:var(--color-accent-600)] hover:underline">Edit</a>
                                        @endcan
                                        @can('delete', $menu)
                                            <form method="POST" action="{{ route('cycle-menus.destroy', $menu) }}" onsubmit="return confirm('Delete this cycle menu?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-[color:var(--color-danger-600)] hover:underline">Delete</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">No cycle menus yet. Create one to get started.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="px-4 py-3 border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            {{ $menus->links() }}
        </div>
    </div>
@endsection
