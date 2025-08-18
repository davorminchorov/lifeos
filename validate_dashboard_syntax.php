<?php
// Comprehensive syntax validation for dashboard.blade.php

$file = 'resources/views/dashboard.blade.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

echo "=== Dashboard Syntax Validation ===\n\n";

$issues = [];

// 1. Check for unclosed brackets/parentheses
$openBrackets = ['[' => 0, '(' => 0, '{' => 0];
$inScript = false;
$inPhp = false;

foreach ($lines as $lineNum => $line) {
    // Track if we're in script or PHP context
    if (strpos($line, '<script>') !== false) $inScript = true;
    if (strpos($line, '</script>') !== false) $inScript = false;
    if (strpos($line, '{{') !== false || strpos($line, '{!!') !== false) $inPhp = true;
    if (strpos($line, '}}') !== false || strpos($line, '!!}') !== false) $inPhp = false;

    // Count brackets in JavaScript/PHP contexts
    if ($inScript || $inPhp) {
        $openBrackets['['] += substr_count($line, '[') - substr_count($line, ']');
        $openBrackets['('] += substr_count($line, '(') - substr_count($line, ')');
        $openBrackets['{'] += substr_count($line, '{') - substr_count($line, '}');
    }
}

foreach ($openBrackets as $bracket => $count) {
    if ($count !== 0) {
        $issues[] = "Unmatched '$bracket' brackets detected (difference: $count)";
    }
}

// 2. Check for malformed Blade directives
$bladeDirectives = ['@if', '@endif', '@foreach', '@endforeach', '@section', '@endsection', '@push', '@endpush'];
$openDirectives = [];

foreach ($lines as $lineNum => $line) {
    foreach ($bladeDirectives as $directive) {
        if (strpos($line, $directive) !== false) {
            if (strpos($directive, 'end') === 1) {
                // This is a closing directive
                $openDirective = str_replace('end', '', $directive);
                if (!empty($openDirectives) && end($openDirectives) === $openDirective) {
                    array_pop($openDirectives);
                } else {
                    $issues[] = "Unexpected closing directive '$directive' at line " . ($lineNum + 1);
                }
            } else {
                // This is an opening directive
                $openDirectives[] = $directive;
            }
        }
    }
}

if (!empty($openDirectives)) {
    $issues[] = "Unclosed Blade directives: " . implode(', ', $openDirectives);
}

// 3. Check for problematic PHP expressions
foreach ($lines as $lineNum => $line) {
    // Check for invalid PHP syntax patterns
    if (preg_match('/\{\{\s*[^}]*\$[^}]*[^;\s]\s*\}\}/', $line) &&
        !preg_match('/\?\?/', $line) &&
        !preg_match('/\-\>/', $line)) {
        // Complex expression without proper operators
        $issues[] = "Potentially problematic PHP expression at line " . ($lineNum + 1) . ": " . trim($line);
    }
}

// 4. Check for JavaScript syntax issues
$inScriptBlock = false;
foreach ($lines as $lineNum => $line) {
    if (strpos($line, '<script>') !== false) {
        $inScriptBlock = true;
        continue;
    }
    if (strpos($line, '</script>') !== false) {
        $inScriptBlock = false;
        continue;
    }

    if ($inScriptBlock) {
        // Check for common JavaScript syntax errors
        if (preg_match('/,\s*[}\]]/', $line)) {
            $issues[] = "Trailing comma before closing bracket/brace at line " . ($lineNum + 1);
        }
        if (preg_match('/[^\s:]\s*{/', $line) && !preg_match('/(function|if|for|while|switch)\s*\(.*\)\s*{/', $line)) {
            // Potential missing semicolon before object literal
            $prevLine = isset($lines[$lineNum - 1]) ? trim($lines[$lineNum - 1]) : '';
            if (!empty($prevLine) && !preg_match('/[;,{}\[\]]$/', $prevLine)) {
                $issues[] = "Potential missing semicolon before line " . ($lineNum + 1);
            }
        }
    }
}

// 5. Check for quote mismatches
foreach ($lines as $lineNum => $line) {
    // Count single and double quotes (basic check)
    $singleQuotes = substr_count($line, "'") - substr_count($line, "\\'");
    $doubleQuotes = substr_count($line, '"') - substr_count($line, '\\"');

    if ($singleQuotes % 2 !== 0) {
        $issues[] = "Unmatched single quotes at line " . ($lineNum + 1);
    }
    if ($doubleQuotes % 2 !== 0) {
        $issues[] = "Unmatched double quotes at line " . ($lineNum + 1);
    }
}

// Report results
if (empty($issues)) {
    echo "✓ No syntax issues detected!\n";
    echo "✓ All brackets and parentheses are properly matched\n";
    echo "✓ All Blade directives are properly closed\n";
    echo "✓ No JavaScript syntax errors found\n";
    echo "✓ Quote matching looks correct\n";
    echo "\nThe dashboard file appears to be syntactically correct.\n";
    echo "The persistent issue might be:\n";
    echo "1. A runtime PHP error (undefined variables)\n";
    echo "2. A missing dependency or route\n";
    echo "3. A server configuration issue\n";
    echo "4. An error in the controller or data preparation\n";
} else {
    echo "Issues found:\n";
    foreach ($issues as $issue) {
        echo "❌ $issue\n";
    }
}

echo "\n=== End Validation ===\n";
