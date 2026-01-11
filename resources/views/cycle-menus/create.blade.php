@extends('layouts.app')

@section('title', 'New Cycle Menu - LifeOS')

@section('header')
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">New Cycle Menu</h1>
        <x-button href="{{ route('cycle-menus.index') }}" variant="secondary">Back to list</x-button>
    </div>
@endsection

@section('content')
    <div class="max-w-3xl">
        <form method="POST" action="{{ route('cycle-menus.store') }}" class="space-y-6">
            @csrf

            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 space-y-4">
                    <x-form.input
                        name="name"
                        label="Name"
                        type="text"
                        :required="true"
                        :value="old('name')"
                    />

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-form.input
                            name="starts_on"
                            label="Starts On"
                            type="date"
                            :value="old('starts_on')"
                        />

                        <x-form.input
                            name="cycle_length_days"
                            label="Cycle Length (days)"
                            type="number"
                            :required="true"
                            :min="1"
                            :max="365"
                            :value="old('cycle_length_days', 7)"
                        />

                        <x-form.checkbox
                            name="is_active"
                            label="Active"
                            :checked="old('is_active')"
                            containerClass="flex items-center mt-6"
                        />
                    </div>

                    <x-form.input
                        name="notes"
                        label="Notes"
                        type="textarea"
                        :rows="4"
                        :value="old('notes')"
                    />
                </div>
                <div class="px-4 py-3 sm:px-6 border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] flex justify-end">
                    <x-button type="submit" variant="primary">Create</x-button>
                </div>
            </div>
        </form>
    </div>
@endsection
