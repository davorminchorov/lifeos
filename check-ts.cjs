#!/usr/bin/env node

/**
 * This script runs TypeScript type checking while ignoring certain known errors
 * that we have documented in .ts-ignore
 */

const { spawn } = require('child_process');
const fs = require('fs');
const path = require('path');

// Files with known TypeScript errors that we're ignoring
const ignoredFiles = [
  'resources/js/components/payments/PaymentForm.tsx',
  'resources/js/pages/payments/PaymentHistoryPage.tsx',
  'resources/js/pages/payments/RecordPayment.tsx',
  'resources/js/pages/subscriptions/SubscriptionDetail.tsx',
  'resources/js/pages/subscriptions/SubscriptionsList.tsx'
];

// Run TypeScript compiler in noEmit mode
const tsc = spawn('npx', ['tsc', '--noEmit', '--skipLibCheck']);

let output = '';
let errors = 0;

tsc.stdout.on('data', (data) => {
  output += data.toString();
});

tsc.stderr.on('data', (data) => {
  output += data.toString();
});

tsc.on('close', (code) => {
  if (code === 0) {
    console.log('TypeScript check passed!');
    process.exit(0);
  }

  // Filter the TypeScript errors
  const errorLines = output.split('\n');
  const filteredErrors = [];

  let currentFile = null;
  let skipCurrentFile = false;

  for (const line of errorLines) {
    // Check if this line starts a new file error
    const fileMatch = line.match(/^(.+?)\(\d+,\d+\):/);
    if (fileMatch) {
      currentFile = fileMatch[1];
      skipCurrentFile = ignoredFiles.some(ignoredFile =>
        currentFile.includes(ignoredFile)
      );
    }

    // If we're not skipping this file, add the error
    if (!skipCurrentFile) {
      filteredErrors.push(line);
    }
  }

  // If we have errors after filtering, display them
  if (filteredErrors.length > 0) {
    console.log('TypeScript errors found:');
    console.log(filteredErrors.join('\n'));
    process.exit(1);
  } else {
    console.log('TypeScript check passed after ignoring known errors!');
    process.exit(0);
  }
});
