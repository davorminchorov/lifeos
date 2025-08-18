<?php
// Test script to check for comma-related syntax errors in dashboard.blade.php

$file = 'resources/views/dashboard.blade.php';
$content = file_get_contents($file);

// Check for remaining @json usages that could cause comma errors
$jsonMatches = [];
preg_match_all('/@json\([^)]*\)/', $content, $jsonMatches);

if (count($jsonMatches[0]) > 0) {
    echo "Warning: Found " . count($jsonMatches[0]) . " remaining @json usages:\n";
    foreach ($jsonMatches[0] as $match) {
        echo "- " . $match . "\n";
    }
} else {
    echo "✓ No problematic @json usages found\n";
}

// Check for potential trailing comma issues in JavaScript arrays/objects
$lines = explode("\n", $content);
$issues = [];

foreach ($lines as $lineNum => $line) {
    // Check for trailing commas before closing brackets/braces in JavaScript context
    if (preg_match('/,\s*[}\]]/', $line) && strpos($line, '<script>') !== false ||
        (isset($lines[$lineNum-10]) && strpos(implode('', array_slice($lines, max(0, $lineNum-10), 10)), '<script>') !== false)) {
        $issues[] = "Line " . ($lineNum + 1) . ": Potential trailing comma issue - " . trim($line);
    }
}

if (empty($issues)) {
    echo "✓ No comma-related syntax issues detected\n";
    echo "✓ Dashboard blade file should now be free of comma syntax errors\n";
} else {
    echo "Potential comma issues found:\n";
    foreach ($issues as $issue) {
        echo "- $issue\n";
    }
}

echo "\nSyntax check completed.\n";
