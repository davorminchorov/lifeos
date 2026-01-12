@extends('layouts.app')

@section('title', 'Macedonian Holidays - LifeOS')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Macedonian Holidays</h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">National and Orthodox holidays celebrated in North Macedonia</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wider text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                            <th class="px-4 py-3">Holiday Name</th>
                            <th class="px-4 py-3">Name in Macedonian</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Description</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                        @foreach ($nationalHolidays as $holiday)
                            <tr class="text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                <td class="px-4 py-3 font-medium">{{ $holiday['name'] }}</td>
                                <td class="px-4 py-3">{{ $holiday['name_mk'] }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $holiday['date'] }}</td>
                                <td class="px-4 py-3">
                                    @if ($holiday['type'] === 'orthodox')
                                        <span class="inline-flex items-center rounded-full bg-[color:var(--color-info-100)] text-[color:var(--color-info-700)] px-2 py-1 text-xs">Orthodox</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-[color:var(--color-accent-100)] text-[color:var(--color-accent-700)] px-2 py-1 text-xs">National</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $holiday['description'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-6 bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-3">About Macedonian Holidays</h2>
            <div class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] space-y-2">
                <p>North Macedonia celebrates a rich mix of national and religious holidays that reflect its diverse cultural heritage.</p>
                <p><strong>National Holidays</strong> commemorate important historical events such as independence, the Ilinden Uprising, and significant cultural figures.</p>
                <p><strong>Orthodox Holidays</strong> follow the Julian calendar and include major Christian celebrations observed by the Macedonian Orthodox Church.</p>
                <p class="text-sm italic">Note: Orthodox Easter is a movable feast and its date varies each year based on the lunar calendar.</p>
            </div>
        </div>
    </div>
@endsection
