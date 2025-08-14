#!/usr/bin/env php
<?php

/**
 * Script to update all Blade templates with design system colors
 * This replaces gray/indigo color classes with design system CSS variables
 */

$replacements = [
    // Background colors
    'bg-white' => 'bg-[color:var(--color-primary-100)]',
    'dark:bg-gray-800' => 'dark:bg-[color:var(--color-dark-200)]',
    'dark:bg-gray-900' => 'dark:bg-[color:var(--color-dark-200)]',
    'bg-gray-50' => 'bg-[color:var(--color-primary-200)]',
    'dark:bg-gray-700' => 'dark:bg-[color:var(--color-dark-300)]',
    'bg-gray-100' => 'bg-[color:var(--color-primary-200)]',
    'bg-gray-300' => 'bg-[color:var(--color-primary-300)]',

    // Text colors
    'text-gray-900' => 'text-[color:var(--color-primary-700)]',
    'dark:text-white' => 'dark:text-[color:var(--color-dark-600)]',
    'text-gray-700' => 'text-[color:var(--color-primary-700)]',
    'dark:text-gray-300' => 'dark:text-[color:var(--color-dark-600)]',
    'text-gray-600' => 'text-[color:var(--color-primary-600)]',
    'dark:text-gray-400' => 'dark:text-[color:var(--color-dark-500)]',
    'text-gray-500' => 'text-[color:var(--color-primary-500)]',

    // Border colors
    'border-gray-300' => 'border-[color:var(--color-primary-300)]',
    'dark:border-gray-700' => 'dark:border-[color:var(--color-dark-300)]',
    'border-gray-200' => 'border-[color:var(--color-primary-300)]',
    'divide-gray-200' => 'divide-[color:var(--color-primary-300)]',
    'divide-gray-700' => 'divide-[color:var(--color-dark-300)]',

    // Hover states
    'hover:bg-gray-50' => 'hover:bg-[color:var(--color-primary-200)]',
    'dark:hover:bg-gray-700' => 'dark:hover:bg-[color:var(--color-dark-300)]',
    'hover:bg-gray-400' => 'hover:bg-[color:var(--color-primary-400)]',

    // Accent colors (indigo to red)
    'bg-indigo-600' => 'bg-[color:var(--color-accent-500)]',
    'hover:bg-indigo-700' => 'hover:bg-[color:var(--color-accent-600)]',
    'focus:border-indigo-500' => 'focus:border-[color:var(--color-accent-500)]',
    'focus:ring-indigo-500' => 'focus:ring-[color:var(--color-accent-500)]',
    'text-indigo-600' => 'text-[color:var(--color-accent-600)]',

    // Status colors - maintain semantic colors but update patterns
    'bg-green-100' => 'bg-[color:var(--color-success-50)]',
    'text-green-800' => 'text-[color:var(--color-success-600)]',
    'dark:bg-green-900' => 'dark:bg-[color:var(--color-success-600)]',
    'dark:text-green-200' => 'dark:text-[color:var(--color-success-50)]',

    'bg-red-100' => 'bg-[color:var(--color-danger-50)]',
    'text-red-800' => 'text-[color:var(--color-danger-600)]',
    'dark:bg-red-900' => 'dark:bg-[color:var(--color-danger-600)]',
    'dark:text-red-200' => 'dark:text-[color:var(--color-danger-50)]',

    'bg-yellow-100' => 'bg-[color:var(--color-warning-50)]',
    'text-yellow-800' => 'text-[color:var(--color-warning-600)]',
    'dark:bg-yellow-900' => 'dark:bg-[color:var(--color-warning-600)]',
    'dark:text-yellow-200' => 'dark:text-[color:var(--color-warning-50)]',

    'bg-blue-100' => 'bg-[color:var(--color-info-50)]',
    'text-blue-800' => 'text-[color:var(--color-info-600)]',
    'dark:bg-blue-900' => 'dark:bg-[color:var(--color-info-600)]',
    'dark:text-blue-200' => 'dark:text-[color:var(--color-info-50)]',

    'text-orange-600' => 'text-[color:var(--color-warning-600)]',
    'dark:text-orange-400' => 'dark:text-[color:var(--color-warning-500)]',
];

function updateFile($filePath, $replacements) {
    if (!file_exists($filePath)) {
        return false;
    }

    $content = file_get_contents($filePath);
    $originalContent = $content;

    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }

    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        echo "Updated: $filePath\n";
        return true;
    }

    return false;
}

function findBladeFiles($directory) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory)
    );

    $bladeFiles = [];
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php' &&
            strpos($file->getFilename(), '.blade.php') !== false) {
            $bladeFiles[] = $file->getPathname();
        }
    }

    return $bladeFiles;
}

// Main execution
$viewsDirectory = 'resources/views';
$bladeFiles = findBladeFiles($viewsDirectory);

echo "Found " . count($bladeFiles) . " Blade template files\n";
echo "Updating design system colors...\n\n";

$updatedFiles = 0;
foreach ($bladeFiles as $file) {
    if (updateFile($file, $replacements)) {
        $updatedFiles++;
    }
}

echo "\nCompleted! Updated $updatedFiles files with design system colors.\n";
