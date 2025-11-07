<?php

// Test script to verify Carbon timestamp parsing fix
echo "Testing Carbon timestamp parsing fix:\n\n";

// Simulate the problematic timestamp
$timestamp = 1756037133;

echo "Original timestamp: $timestamp\n";

// Test the old approach that was causing the error
try {
    echo "Testing Carbon::parse() (old approach):\n";
    $parsed = \Carbon\Carbon::parse($timestamp);
    echo 'Success: '.$parsed->format('M j, Y g:i A')."\n";
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}

echo "\n";

// Test the new approach that should work
try {
    echo "Testing Carbon::createFromTimestamp() (new approach):\n";
    $parsed = \Carbon\Carbon::createFromTimestamp($timestamp);
    echo 'Success: '.$parsed->format('M j, Y g:i A')."\n";
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}

echo "\nTest completed. The new approach should work correctly.\n";
