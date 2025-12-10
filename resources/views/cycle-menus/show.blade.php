@extends('layouts.app')

@section('title', $menu->name . ' - Cycle Menu - LifeOS')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $menu->name }}</h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Cycle length: {{ $menu->cycle_length_days }} days
                @if ($menu->starts_on)
                    • Starts {{ $menu->starts_on->format('Y-m-d') }}
                @endif
                @if ($menu->is_active)
                    • <span class="inline-flex items-center rounded-full bg-[color:var(--color-success-100)] text-[color:var(--color-success-700)] px-2 py-1 text-xs align-middle">Active</span>
                @else
                    • <span class="inline-flex items-center rounded-full bg-[color:var(--color-primary-200)] text-[color:var(--color-primary-600)] px-2 py-1 text-xs align-middle">Inactive</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            @can('update', $menu)
                <a href="{{ route('cycle-menus.edit', $menu) }}" class="bg-[color:var(--color-primary-500)] hover:bg-[color:var(--color-primary-600)] text-white px-4 py-2 rounded-md text-sm font-medium">Edit Menu</a>
            @endcan
            <a href="{{ route('cycle-menus.index') }}" class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] hover:underline">Back to list</a>
        </div>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <div class="mb-4 rounded-md bg-[color:var(--color-accent-50)] text-[color:var(--color-accent-700)] px-4 py-3">{{ session('status') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-{{ min(4, max(1, $menu->cycle_length_days)) }} gap-6">
        @for ($i = 0; $i < $menu->cycle_length_days; $i++)
            @php($day = $daysByIndex->get($i))
            <div class="h-full rounded-lg ring-1 ring-[color:var(--color-primary-300)]/60 dark:ring-[color:var(--color-dark-300)] bg-[color:var(--color-primary-100)]/60 dark:bg-[color:var(--color-dark-200)] shadow-sm flex flex-col">
                <div class="px-4 py-3 sm:px-6 border-b border-[color:var(--color-primary-300)]/60 dark:border-[color:var(--color-dark-300)] flex items-center justify-between">
                    <div class="font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Day {{ $i + 1 }}</div>
                </div>

                <div class="p-4 space-y-5">
                    {{-- Day Notes --}}
                    @if ($day)
                        @can('update', $day)
                            <form method="POST" action="{{ route('cycle-menu-days.update', $day) }}" class="space-y-2">
                                @csrf
                                @method('PUT')
                                <label class="block text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]" for="notes_{{ $day->id }}">Notes</label>
                                <textarea id="notes_{{ $day->id }}" name="notes" rows="3" class="block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">{{ old('notes', $day->notes) }}</textarea>
                                <div class="flex justify-end pt-1">
                                    <button type="submit" class="text-xs bg-[color:var(--color-primary-500)] hover:bg-[color:var(--color-primary-600)] text-white px-3 py-1.5 rounded-md">Save Notes</button>
                                </div>
                            </form>
                        @endcan
                    @endif

                    {{-- Add Item --}}
                    @if ($day)
                        @can('create', \App\Models\CycleMenuItem::class)
                            <form method="POST" action="{{ route('cycle-menu-items.store') }}" class="space-y-3">
                                @csrf
                                <input type="hidden" name="cycle_menu_day_id" value="{{ $day->id }}">
                                <div>
                                    <label class="block text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]" for="title_{{ $i }}">Add Item</label>
                                    <input id="title_{{ $i }}" name="title" type="text" placeholder="e.g., Oatmeal with berries" required class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                </div>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="col-span-1">
                                        <label class="block text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]" for="meal_type_{{ $i }}">Type</label>
                                        <select id="meal_type_{{ $i }}" name="meal_type" class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                            @foreach (\App\Enums\MealType::cases() as $type)
                                                <option value="{{ $type->value }}">{{ ucfirst($type->value) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-1">
                                        <label class="block text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]" for="time_{{ $i }}">Time</label>
                                        <input id="time_{{ $i }}" name="time_of_day" type="time" class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                    </div>
                                    <div class="col-span-1">
                                        <label class="block text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]" for="qty_{{ $i }}">Quantity</label>
                                        <input id="qty_{{ $i }}" name="quantity" type="text" placeholder="e.g., 1 serving" class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-3 py-1.5 rounded-md text-sm">Add</button>
                                </div>
                            </form>
                        @endcan
                    @endif

                    {{-- Items List --}}
                    <div class="space-y-2">
                        <div class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Items</div>
                        @if ($day && $day->items->count())
                            {{-- Reorder form --}}
                            <form method="POST" action="{{ route('cycle-menu-items.reorder') }}" class="space-y-2" id="reorder_form_{{ $i }}">
                                @csrf
                                @foreach ($day->items as $idx => $item)
                                    <div class="flex items-start gap-3 bg-[color:var(--color-primary-200)]/60 dark:bg-[color:var(--color-dark-100)] rounded-md px-3 py-2">
                                        <div class="shrink-0">
                                            <input type="hidden" name="orders[{{ $idx }}][id]" value="{{ $item->id }}">
                                            <label class="sr-only" for="pos_{{ $item->id }}">Position</label>
                                            @can('update', $item)
                                                <input id="pos_{{ $item->id }}" name="orders[{{ $idx }}][position]" type="number" min="0" value="{{ $item->position }}" class="w-16 rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)]">
                                            @else
                                                <input id="pos_{{ $item->id }}" type="number" value="{{ $item->position }}" class="w-16 rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)]" disabled>
                                            @endcan
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-[color:var(--color-primary-800)] dark:text-[color:var(--color-dark-600)]">{{ $item->title }}</div>
                                            <div class="text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                                {{ ucfirst($item->meal_type->value) }}
                                                @if ($item->time_of_day)
                                                    • {{ \Illuminate\Support\Carbon::createFromFormat('H:i:s', $item->time_of_day)->format('H:i') }}
                                                @endif
                                                @if ($item->quantity)
                                                    • {{ $item->quantity }}
                                                @endif
                                            </div>
                                        </div>
                                        @can('delete', $item)
                                            <button type="submit" form="delete_item_{{ $item->id }}" class="text-[color:var(--color-danger-600)] hover:underline text-xs" onclick="return confirm('Remove this item?');">Remove</button>
                                        @endcan
                                    </div>
                                @endforeach
                                @php($firstItem = $day->items->first())
                                @if ($firstItem)
                                    @can('update', $firstItem)
                                        <div class="flex justify-end">
                                            <button type="submit" class="text-xs bg-[color:var(--color-primary-500)] hover:bg-[color:var(--color-primary-600)] text-white px-3 py-1 rounded-md">Save Order</button>
                                        </div>
                                    @endcan
                                @endif
                            </form>
                            {{-- Separate delete forms to avoid nested forms --}}
                            @foreach ($day->items as $item)
                                @can('delete', $item)
                                    <form method="POST" action="{{ route('cycle-menu-items.destroy', $item) }}" id="delete_item_{{ $item->id }}" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endcan
                            @endforeach
                        @else
                            <div class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">No items yet.</div>
                        @endif
                    </div>
                </div>
            </div>
        @endfor
    </div>
@endsection
