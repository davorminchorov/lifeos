# LifeOS Frontend Development Guidelines

## Code Organization

### Component Structure

```tsx
// Example component structure
import React from 'react'
import { Button } from '../../ui/Button'
import { useMyFeatureStore } from '../store/useMyFeatureStore'
import { useMyFeatureQuery } from '../api/queries'
import type { MyFeatureItem } from '../types'

interface FeatureComponentProps {
  id: string
  onAction: (item: MyFeatureItem) => void
}

export function FeatureComponent({ id, onAction }: FeatureComponentProps) {
  // Hooks at the top
  const { filter, setFilter } = useMyFeatureStore()
  const { data, isLoading } = useMyFeatureQuery(id)
  
  // Event handlers next
  const handleAction = () => {
    if (data) {
      onAction(data)
    }
  }
  
  // Return JSX
  return (
    <div className="p-4">
      {isLoading ? (
        <div>Loading...</div>
      ) : (
        <Button onClick={handleAction}>
          Perform Action
        </Button>
      )}
    </div>
  )
}
```

### Naming Conventions

- **Files**: PascalCase for components (`Button.tsx`), camelCase for utilities (`formatDate.ts`)
- **Components**: PascalCase (`SubscriptionCard`)
- **Hooks**: camelCase with `use` prefix (`useSubscriptionStore`)
- **Types/Interfaces**: PascalCase (`SubscriptionFormData`)
- **Functions**: camelCase (`formatCurrency`)

## State Management

### Zustand Store Organization

```tsx
// Example store setup
import { create } from 'zustand'
import { immer } from 'zustand/middleware/immer'

interface FeatureState {
  // State
  items: Item[]
  filter: FilterCriteria
  
  // Commands
  addItem: (item: ItemData) => void
  updateItem: (id: string, data: Partial<ItemData>) => void
  removeItem: (id: string) => void
  setFilter: (filter: Partial<FilterCriteria>) => void
}

export const useFeatureStore = create<FeatureState>()(
  immer((set) => ({
    // Initial state
    items: [],
    filter: { status: 'active', sortBy: 'date' },
    
    // Commands
    addItem: (itemData) => set((state) => {
      // Create new item from data
      const newItem = { 
        id: crypto.randomUUID(),
        ...itemData,
        createdAt: new Date().toISOString()
      }
      // Add to state
      state.items.push(newItem)
    }),
    
    updateItem: (id, data) => set((state) => {
      const index = state.items.findIndex(item => item.id === id)
      if (index !== -1) {
        state.items[index] = { ...state.items[index], ...data }
      }
    }),
    
    removeItem: (id) => set((state) => {
      state.items = state.items.filter(item => item.id !== id)
    }),
    
    setFilter: (filter) => set((state) => {
      state.filter = { ...state.filter, ...filter }
    })
  }))
)
```

## API Integration

### React Query Pattern

```tsx
// Example API hooks
import { useQuery, useMutation, QueryClient } from '@tanstack/react-query'
import type { Feature } from '../types'

// Query keys
const featureKeys = {
  all: ['feature'] as const,
  lists: () => [...featureKeys.all, 'list'] as const,
  list: (filters: Record<string, any>) => [...featureKeys.lists(), filters] as const,
  details: () => [...featureKeys.all, 'detail'] as const,
  detail: (id: string) => [...featureKeys.details(), id] as const,
}

// Fetch list
export function useFeatureList(filters: Record<string, any>) {
  return useQuery({
    queryKey: featureKeys.list(filters),
    queryFn: async () => {
      const response = await fetch(`/api/features?${new URLSearchParams(filters)}`)
      if (!response.ok) throw new Error('Failed to fetch features')
      return response.json()
    }
  })
}

// Create mutation
export function useCreateFeature(queryClient: QueryClient) {
  return useMutation({
    mutationFn: async (data: FeatureCreateData) => {
      const response = await fetch('/api/features', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      })
      if (!response.ok) throw new Error('Failed to create feature')
      return response.json()
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: featureKeys.lists() })
    }
  })
}
```

## Form Implementation

### React Hook Form Pattern

```tsx
// Example form component
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { Button } from '../../ui/Button'
import { Input } from '../../ui/Input'

// Define schema
const formSchema = z.object({
  name: z.string().min(3, 'Name must be at least 3 characters'),
  amount: z.number().positive('Amount must be positive'),
  date: z.string().regex(/^\d{4}-\d{2}-\d{2}$/, 'Use YYYY-MM-DD format')
})

type FormData = z.infer<typeof formSchema>

interface FeatureFormProps {
  onSubmit: (data: FormData) => void
  defaultValues?: Partial<FormData>
}

export function FeatureForm({ onSubmit, defaultValues }: FeatureFormProps) {
  const { 
    register, 
    handleSubmit, 
    formState: { errors, isSubmitting } 
  } = useForm<FormData>({
    resolver: zodResolver(formSchema),
    defaultValues: {
      name: '',
      amount: 0,
      date: new Date().toISOString().split('T')[0],
      ...defaultValues
    }
  })
  
  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
      <div>
        <Input
          label="Name"
          {...register('name')}
          error={errors.name?.message}
        />
      </div>
      
      <div>
        <Input
          label="Amount"
          type="number"
          step="0.01"
          {...register('amount', { valueAsNumber: true })}
          error={errors.amount?.message}
        />
      </div>
      
      <div>
        <Input
          label="Date"
          type="date"
          {...register('date')}
          error={errors.date?.message}
        />
      </div>
      
      <Button 
        type="submit" 
        isLoading={isSubmitting}
        fullWidth
      >
        Submit
      </Button>
    </form>
  )
}
``` 
