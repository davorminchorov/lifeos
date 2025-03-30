import React from 'react';
import { Head } from '@inertiajs/react';

export default function Test() {
  return (
    <>
      <Head title="Test Page" />
      <div className="p-6">
        <h1 className="text-2xl font-bold mb-4">Test Page</h1>
        <p>If you can see this, Inertia is working properly!</p>
      </div>
    </>
  );
}
