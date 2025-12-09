@extends('layouts.app')

@section('title', 'Edit Cycle Menu - LifeOS')

@section('header')
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Edit Cycle Menu</h1>
        <div class="flex items-center gap-3">
            <a href="{{ route('cycle-menus.show', $menu) }}" class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] hover:underline">View</a>
            <a href="{{ route('cycle-menus.index') }}" class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] hover:underline">Back to list</a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-3xl">
        <form method="POST" action="{{ route('cycle-menus.update', $menu) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Name *</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $menu->name) }}" required class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                        @error('name')
                            <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="starts_on" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Starts On</label>
                            <input id="starts_on" name="starts_on" type="date" value="{{ old('starts_on', optional($menu->starts_on)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                            @error('starts_on')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cycle_length_days" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Cycle Length (days) *</label>
                            <input id="cycle_length_days" name="cycle_length_days" type="number" min="1" max="365" value="{{ old('cycle_length_days', $menu->cycle_length_days) }}" required class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                            @error('cycle_length_days')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center mt-6">
                            <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $menu->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-[color:var(--color-primary-300)] text-[color:var(--color-accent-600)] focus:ring-[color:var(--color-accent-500)]">
                            <label for="is_active" class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Active</label>
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Notes</label>
                        <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">{{ old('notes', $menu->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="px-4 py-3 sm:px-6 border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] flex justify-end">
                    <button type="submit" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium">Save</button>
                </div>
            </div>
        </form>
    </div>
@endsection
