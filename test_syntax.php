<?php
// Test script to check PHP syntax in dashboard.blade.php
$output = shell_exec('php -l resources/views/dashboard.blade.php 2>&1');
echo $output;
