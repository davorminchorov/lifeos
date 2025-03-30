import '../css/app.css';

import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';

// Import all components with specific type definition
const pages: Record<string, () => Promise<any>> = {
  'Authentication/UI/Login': () => import('./Authentication/UI/Login'),
  'Dashboard/UI/Dashboard': () => import('./Dashboard/UI/Dashboard'),
};

createInertiaApp({
  title: (title) => `${title} - LifeOS`,
  resolve: (name) => {
    const page = pages[name];
    if (!page) {
      console.error(`Page not found: ${name}`);
      return Promise.resolve({
        default: () => <div>Component Not Found: {name}</div>
      });
    }
    return page();
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
