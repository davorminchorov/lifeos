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
                    <x-form.input
                        name="name"
                        id="name"
                        label="Name *"
                        :value="old('name', $menu->name)"
                        required
                    />

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-form.input
                                name="starts_on"
                                id="starts_on"
                                label="Starts On"
                                type="date"
                                :value="old('starts_on', optional($menu->starts_on)->format('Y-m-d'))"
                            />
                        </div>

                        <div>
                            <x-form.input
                                name="cycle_length_days"
                                id="cycle_length_days"
                                label="Cycle Length (days) *"
                                type="number"
                                min="1"
                                max="365"
                                :value="old('cycle_length_days', $menu->cycle_length_days)"
                                required
                            />
                        </div>

                        <div class="flex items-center mt-6">
                            <x-form.checkbox
                                name="is_active"
                                id="is_active"
                                label="Active"
                                value="1"
                                :checked="old('is_active', $menu->is_active)"
                            />
                        </div>
                    </div>

                    <x-form.input
                        name="notes"
                        id="notes"
                        label="Notes"
                        type="textarea"
                        rows="4"
                        :value="old('notes', $menu->notes)"
                    />
                </div>
                <div class="px-4 py-3 sm:px-6 border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] flex justify-end">
                    <x-button type="submit" variant="primary">Save</x-button>
                </div>
            </div>
        </form>
    </div>
@endsection
