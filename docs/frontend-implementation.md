# LifeOS Frontend Implementation Guide

## Getting Started

### Initial Setup

1. **Install Dependencies**:
   ```bash
   npm install zustand react-hook-form zod @hookform/resolvers immer @tanstack/react-query tailwindcss@latest postcss autoprefixer
   npm install -D typescript @types/react @types/node vite eslint prettier eslint-plugin-react-hooks
   ```

2. **Configure TypeScript**:
   Create a `tsconfig.json` file with strict type checking enabled.

3. **Configure Tailwind CSS**:
   Create a `tailwind.config.js` file with design system colors and other customizations.

4. **Configure Vite**:
   Set up Vite for optimal React development.

### Directory Setup

Create the required directories for the vertical slice architecture:

```bash
mkdir -p resources/js/{ui,lib,utils,types,auth,dashboard,subscriptions,bills,investments,jobs,expenses}
mkdir -p resources/js/ui/{Button,Card,Input,Modal,Select,Table}
mkdir -p resources/js/auth/{components,hooks,store,api,types,utils,routes}
# Repeat for other feature slices
```

## Implementation Steps

### 1. Create Base UI Components

Start by implementing the core UI components in the `ui/` directory:

- Button component
- Card component
- Input with validation
- Modal/Dialog components
- Form components
- Layout components

### 2. Set Up Core Libraries

Configure the essential libraries in the `lib/` directory:

- Zustand for state management
- React Query for API calls
- React Router for routing

### 3. Implement Authentication

Build the complete authentication flow:

1. **Login Form**:
   - Create login form with validation
   - Connect to authentication API

2. **Auth Store**:
   - Set up Zustand store for auth state
   - Implement login/logout actions

3. **Protected Routes**:
   - Create route protection components
   - Set up redirection for unauthenticated users

### 4. Feature Implementation

For each feature slice:

1. **Data Types**:
   - Define TypeScript interfaces
   - Create validation schemas

2. **API Integration**:
   - Implement React Query hooks
   - Set up CRUD operations

3. **State Management**:
   - Create feature-specific Zustand store
   - Implement actions and state transitions

4. **UI Components**:
   - Build feature-specific UI components
   - Connect components to store and API

5. **Routes**:
   - Define feature routes
   - Create page components

## Example Feature Implementation

### Subscriptions Feature

1. **Types**:
   ```typescript
   // subscriptions/types/index.ts
   export interface Subscription {
     id: string
     name: string
     amount: number
     billingCycle: 'monthly' | 'yearly' | 'weekly'
     nextBillingDate: string
     category: string
     status: 'active' | 'cancelled'
   }
   
   export interface SubscriptionFormData {
     name: string
     amount: number
     billingCycle: 'monthly' | 'yearly' | 'weekly'
     startDate: string
     category: string
   }
   ```

2. **API**:
   ```typescript
   // subscriptions/api/index.ts
   import { useQuery, useMutation, QueryClient } from '@tanstack/react-query'
   import type { Subscription, SubscriptionFormData } from '../types'
   
   // Query hooks for CRUD operations
   // ...
   ```

3. **Store**:
   ```typescript
   // subscriptions/store/useSubscriptionStore.ts
   import { create } from 'zustand'
   import { immer } from 'zustand/middleware/immer'
   
   // Feature-specific state and actions
   // ...
   ```

4. **Components**:
   ```typescript
   // subscriptions/components/SubscriptionList.tsx
   import React from 'react'
   import { useSubscriptions } from '../api'
   import { useSubscriptionStore } from '../store/useSubscriptionStore'
   import type { Subscription } from '../types'
   
   // Feature-specific UI components
   // ...
   ```

5. **Routes**:
   ```typescript
   // subscriptions/routes/index.tsx
   import React from 'react'
   import { Route } from 'react-router-dom'
   import { SubscriptionsPage } from '../components/SubscriptionsPage'
   import { SubscriptionDetailPage } from '../components/SubscriptionDetailPage'
   
   // Feature routes
   // ...
   ```

## Bringing It All Together

In the main entry file, assemble the application:

```typescript
// app.tsx
import React from 'react'
import { QueryClientProvider } from '@tanstack/react-query'
import { BrowserRouter, Routes, Route } from 'react-router-dom'
import { queryClient } from './lib/react-query'
import { AuthProvider } from './auth/components/AuthProvider'
import { ProtectedRoute } from './auth/components/ProtectedRoute'

// Import feature routes
import { authRoutes } from './auth/routes'
import { dashboardRoutes } from './dashboard/routes'
import { subscriptionRoutes } from './subscriptions/routes'
// ... other feature routes

export function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <AuthProvider>
        <BrowserRouter>
          <Routes>
            {/* Public routes */}
            {authRoutes}
            
            {/* Protected routes */}
            <Route element={<ProtectedRoute />}>
              {dashboardRoutes}
              {subscriptionRoutes}
              {/* ... other feature routes */}
            </Route>
          </Routes>
        </BrowserRouter>
      </AuthProvider>
    </QueryClientProvider>
  )
} 
