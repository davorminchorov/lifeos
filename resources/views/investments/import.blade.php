@extends('layouts.app')

@section('title', 'Import Investments - LifeOS')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Import Investments
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Upload a CSV export from your broker. The file will be validated and processed in the background queue named <span class="font-mono">imports</span>.
            </p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 sm:gap-2 flex-shrink-0">
            <a href="{{ route('investments.index') }}" class="w-full sm:w-auto inline-flex justify-center items-center bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-6 py-3 sm:px-4 sm:py-2 rounded-lg sm:rounded-md text-base sm:text-sm font-medium transition-colors duration-200 touch-manipulation">
                Back to Investments
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="rounded-md border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)]">
        <div class="p-4">
            <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Import Investments from CSV</h2>
            <p class="mt-1 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Upload a CSV export from your broker. The file will be validated and processed in the background queue named <span class="font-mono">imports</span>.</p>

            @if ($errors->has('file'))
                <p class="mt-3 text-sm text-[color:var(--color-danger-600)]">{{ $errors->first('file') }}</p>
            @endif

            <form class="mt-4 flex flex-col sm:flex-row items-start sm:items-center gap-3" method="POST" action="{{ route('investments.import') }}" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" accept=".csv,text/csv" class="block w-full text-sm file:mr-4 file:rounded-md file:border-0 file:bg-[color:var(--color-primary-200)] file:px-4 file:py-2 file:text-sm file:font-medium file:text-[color:var(--color-primary-700)] hover:file:bg-[color:var(--color-primary-300)] dark:file:bg-[color:var(--color-dark-300)] dark:hover:file:bg-[color:var(--color-dark-400)]" required>
                <div class="flex gap-3">
                    <button type="submit" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">Start Import</button>
                    <a href="{{ route('investments.index') }}" class="text-sm inline-flex items-center bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-4 py-2 rounded-md font-medium transition-colors duration-200">Cancel</a>
                </div>
            </form>

            <p class="mt-2 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Max file size 10MB. Supported types: CSV/TXT.</p>
        </div>
    </div>
@endsection
