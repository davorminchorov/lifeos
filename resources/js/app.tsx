import './bootstrap';
import '../css/app.css';

import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';

// Import all pages with specific type definition
const pages: Record<string, () => Promise<any>> = {
  'Test': () => import('./Pages/Test'),
  'Authentication/UI/Simple': () => import('./Authentication/UI/Simple'),
  'Authentication/UI/Login': () => import('./Authentication/UI/Login'),
  'Dashboard/UI/Dashboard': () => import('./Dashboard/UI/Dashboard'),
  'Authentication/UI/NewTest': () => import('./Authentication/UI/NewTest'),
};

// Add some debug info to the window object
if (typeof window !== 'undefined') {
  (window as any).inertiaDebug = {
    pages,
    checkComponent: (name: string) => {
      console.log(`Checking for component: ${name}`);
      const page = pages[name];
      console.log(`Component found: ${!!page}`);
      return !!page;
    }
  };
}

createInertiaApp({
  title: (title) => `${title} - LifeOS`,
  resolve: (name) => {
    console.log(`Resolving component: ${name}`);
    const page = pages[name];
    if (!page) {
      console.error(`Page not found: ${name}`);
      console.error('Available pages:', Object.keys(pages));
      // Return a fallback component to avoid crashes
      return Promise.resolve({
        default: () => <div style={{padding: '2rem', color: 'red'}}>
          <h1>Component Not Found</h1>
          <p>The component "{name}" could not be found.</p>
          <p>Available components:</p>
          <ul>
            {Object.keys(pages).map(page => (
              <li key={page}>{page}</li>
            ))}
          </ul>
        </div>
      });
    }
    return page().catch(error => {
      console.error(`Error loading component ${name}:`, error);
      throw error;
    });
  },
  setup({ el, App, props }) {
    console.log('Setting up Inertia app with props:', props);
    try {
      const root = createRoot(el);
      root.render(<App {...props} />);
      console.log('App successfully rendered');
    } catch (error) {
      console.error('Error rendering app:', error);
    }
  },
});
