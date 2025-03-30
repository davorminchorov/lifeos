# LifeOS Frontend Architecture

## Vertical Slice Architecture

LifeOS frontend uses a vertical slice architecture that organizes code by business capability rather than technical concerns. Each feature is a fully independent vertical slice containing everything needed for that feature to function.

### Key Principles

1. **Feature-Based Organization**: Code is organized by business features instead of technical layers
2. **Encapsulation**: Each feature contains all its components, state, API calls, and utilities
3. **Independence**: Features can be developed, tested, and deployed independently
4. **Single Responsibility**: Each slice is responsible for a single business capability

### Structure

```
resources/js/
├── ui/                      # Core UI components
├── lib/                     # Library configurations
├── utils/                   # Essential utilities
├── types/                   # Global types
├── auth/                    # Authentication slice
├── dashboard/               # Dashboard slice
├── subscriptions/           # Subscription management slice
├── bills/                   # Utility bills slice 
└── [other feature slices]   # Other business domains
```

### Slice Structure

Each feature slice follows this structure:

```
feature/
├── components/          # UI components specific to the feature
├── hooks/               # Custom hooks for feature functionality
├── store/               # State management for the feature
├── api/                 # API interaction for the feature
├── types/               # TypeScript types for the feature
├── utils/               # Utilities specific to the feature
└── routes/              # Routes for the feature
```

## State Management

State is managed using Zustand with a focus on event sourcing:

1. **Feature-Specific Stores**: Each feature has its own Zustand store
2. **Command/Event Pattern**: State changes via commands that emit events
3. **Immutability**: State is never directly mutated, only through event application
4. **Persistence**: Critical state persisted in localStorage where appropriate

## Data Fetching

React Query handles all API communication:

1. **Feature-Specific Queries**: Queries and mutations defined within feature slices
2. **Caching**: Intelligent caching with configurable stale times
3. **Optimistic Updates**: Used for responsive UI during mutations
4. **Prefetching**: Data prefetching for improved UX

## Form Handling

Forms are built with React Hook Form:

1. **Validation**: Zod schemas for type-safe validation
2. **Performance**: Minimized re-renders for optimal performance
3. **Reusability**: Form components encapsulated within features 
