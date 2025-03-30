import '../css/app.css';

import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import Login from './Authentication/UI/Login';
import Dashboard from './Dashboard/UI/Dashboard';

// Define component map with proper TypeScript typing
const components: Record<string, React.ComponentType<any>> = {
  'Authentication/UI/Login': Login,
  'Dashboard/UI/Dashboard': Dashboard,
};

createInertiaApp({
  title: (title) => `${title} - LifeOS`,
  resolve: (name) => {
    // Use directly imported components
    if (name in components) {
      return Promise.resolve(components[name]);
    }

    console.error(`Page not found: ${name}`);
    return Promise.resolve({
      default: () => <div>Component Not Found: {name}</div>
    });
  },
  setup({ el, App, props }) {
    const root = createRoot(el);
    root.render(
      <React.StrictMode>
        <App {...props} />
      </React.StrictMode>
    );
  },
});
