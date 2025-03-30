# LifeOS Development Guidelines

## Technology Stack

- **Frontend**: React 19 (SPA), Tailwind CSS 4
- **UI Framework**: Tailwind CSS 4 with utility-first approach
- **Backend**: Laravel (PHP)
- **Database**: MySQL
- **Containerization**: Docker with Laravel Sail
- **Build Tools**: Vite

## Architecture Overview

LifeOS uses Laravel for the backend with Inertia.js to connect it to a React frontend. This provides a SPA-like experience while leveraging Laravel's robust backend capabilities.

## Directory Structure

- `app/` - Laravel backend code
- `resources/js/` - React components and frontend logic
  - `resources/js/components/` - Reusable UI components
  - `resources/js/layouts/` - Page layouts
  - `resources/js/pages/` - Page components
  - `resources/js/{Module}/` - Module-specific components (e.g., Dashboard, Authentication)
- `resources/css/` - Global CSS and Tailwind configuration
- `public/build/` - Compiled assets (managed by Vite)
- `docs/` - Documentation

## Coding Standards

### General Principles

1. **Clarity over cleverness**: Write clear, readable code over clever one-liners
2. **Consistency**: Follow established patterns in the codebase
3. **Composition**: Build complex UIs from simpler components
4. **Type safety**: Use TypeScript interfaces and proper typing

### React Component Guidelines

1. **Component Organization**:
   - Use functional components with hooks
   - Organize imports logically (React, external libraries, internal imports)
   - Define interfaces and types at the top
   - Keep components focused on a single responsibility

2. **Props Handling**:
   - Use destructuring for props
   - Provide default values where appropriate
   - Document complex props with JSDoc comments

3. **Naming Conventions**:
   - PascalCase for component files and component names
   - camelCase for variables, functions, and instances
   - Use descriptive, intention-revealing names

4. **Component Structure**:
   ```tsx
   import React from 'react';
   import { ExternalDependency } from 'external-lib';
   import { InternalComponent } from '../../components';

   interface ComponentProps {
     // Define props here
   }

   export function ComponentName({ prop1, prop2 }: ComponentProps) {
     // Hooks at the top
     const [state, setState] = useState();

     // Event handlers and other functions
     function handleEvent() {
       // Implementation
     }

     // Return JSX
     return (
       <div>
         {/* Component content */}
       </div>
     );
   }
   ```

### Tailwind CSS Usage

1. **Class Organization**:
   - Group classes by functionality (layout, spacing, appearance)
   - Use consistent ordering across components
   - Consider extracting common patterns to component classes

2. **Responsive Design**:
   - Use Tailwind's responsive prefixes consistently
   - Test layouts at all standard breakpoints
   - Consider mobile-first approach

3. **Dark Mode**:
   - Support dark mode with the `dark:` prefix
   - Ensure sufficient contrast in both modes
   - Test all components in both light and dark mode

### Laravel/Backend Guidelines

1. **Controller Organization**:
   - Keep controllers RESTful
   - Move business logic to services and models
   - Use form requests for validation

2. **Database & Models**:
   - Use migrations for all database changes
   - Define relationships in models
   - Use Eloquent features appropriately

3. **API Design**:
   - Consistent response formats
   - Proper HTTP status codes
   - Thorough input validation

## Development Workflow

### Environment Setup

1. Clone the repository
2. Run `composer install` to install PHP dependencies
3. Run `npm install` to install JavaScript dependencies
4. Copy `.env.example` to `.env` and configure environment variables
5. Run `./vendor/bin/sail up -d` to start Docker containers
6. Run `./vendor/bin/sail artisan migrate` to set up the database
7. Run `./vendor/bin/sail npm run dev` to start the Vite development server

### Development Process

1. **Feature Development**:
   - Create a new branch from `main`
   - Implement the feature
   - Write tests
   - Submit a pull request

2. **Code Review**:
   - All code changes must be reviewed
   - Address feedback promptly
   - Maintain a constructive review process

### Building & Deployment

1. **Local Building**:
   - `./vendor/bin/sail npm run build` generates production assets

2. **Optimization**:
   - `./vendor/bin/sail artisan optimize` for production optimization
   - `./vendor/bin/sail artisan optimize:clear` to clear cache during development

## Testing Guidelines

1. **Frontend Testing**:
   - Unit tests for utility functions
   - Component tests for UI components
   - End-to-end tests for critical user flows

2. **Backend Testing**:
   - Feature tests for API endpoints
   - Unit tests for complex logic
   - Database tests for Eloquent models

## Performance Considerations

1. **Asset Optimization**:
   - Minimize bundle size
   - Optimize images and media
   - Consider code splitting for large modules

2. **Rendering Performance**:
   - Avoid unnecessary re-renders
   - Use memoization where appropriate
   - Monitor component render performance

3. **Backend Performance**:
   - Optimize database queries
   - Use caching strategically
   - Monitor API response times

## Accessibility Standards

1. **Semantic HTML**:
   - Use appropriate HTML elements
   - Maintain proper heading hierarchy
   - Include proper landmarks

2. **Keyboard Navigation**:
   - Ensure all interactive elements are keyboard accessible
   - Maintain logical tab order
   - Provide keyboard shortcuts for power users

3. **Screen Readers**:
   - Add aria attributes where needed
   - Include alt text for images
   - Test with screen readers periodically

## Security Best Practices

1. **Input Validation**:
   - Validate all user input
   - Sanitize data before rendering
   - Use Laravel's built-in validation

2. **Authentication**:
   - Use Laravel Sanctum for API authentication
   - Implement proper session management
   - Follow security best practices for passwords

3. **Data Protection**:
   - Encrypt sensitive data
   - Follow data protection regulations
   - Limit access to sensitive information

## Documentation Standards

1. **Code Documentation**:
   - Comment complex logic
   - Use JSDoc for functions and interfaces
   - Keep documentation up-to-date

2. **Project Documentation**:
   - Maintain this guide and design system docs
   - Document significant architectural decisions
   - Include setup instructions for new developers

## Troubleshooting Common Issues

1. **Vite/Asset Issues**:
   - Check `.env` configuration for Vite
   - Verify correct URLs in `app.blade.php`
   - Clear caches with `optimize:clear`

2. **Docker/Sail Issues**:
   - Restart containers: `./vendor/bin/sail down && ./vendor/bin/sail up -d`
   - Check container logs: `./vendor/bin/sail logs`
   - Rebuild if needed: `./vendor/bin/sail build --no-cache` 
