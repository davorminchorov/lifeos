# LifeOS Coding Standards

This document outlines the coding standards for the LifeOS project, covering both backend (PHP/Laravel) and frontend (React/TypeScript) development. Following these standards ensures code consistency, maintainability, and quality across the codebase.

## Table of Contents

- [Backend Standards](#backend-standards)
  - [PHP & Laravel Conventions](#php--laravel-conventions)
  - [Event Sourcing Patterns](#event-sourcing-patterns)
  - [Database & Queries](#database--queries)
  - [Testing](#backend-testing)
- [Frontend Standards](#frontend-standards)
  - [React & TypeScript](#react--typescript)
  - [State Management](#state-management)
  - [Styling](#styling-with-tailwind)
  - [Testing](#frontend-testing)
- [Shared Standards](#shared-standards)
  - [Code Quality Tools](#code-quality-tools)
  - [Documentation](#documentation)
  - [Git Workflow](#git-workflow)

## Backend Standards

### PHP & Laravel Conventions

#### PHP Version & Features

- Target PHP 8.2+ for development
- Use PHP 8.x features where appropriate:
  - Typed properties
  - Union types
  - Named arguments for improved readability
  - Constructor property promotion
  - Match expressions over switch statements
  - Attributes for metadata

```php
// ✅ Good
public function createSubscription(
    string $id, 
    string $userId, 
    string $name, 
    float $amount, 
    string $billingCycle
): Subscription {
    // Implementation
}

// ❌ Bad
public function createSubscription($id, $userId, $name, $amount, $billingCycle) {
    // Implementation without types
}
```

#### Code Structure

- Follow PSR-12 coding style
- Use strict typing with `declare(strict_types=1)` at the top of each file
- Organize namespaces following Vertical Slices pattern:

```
App\Domain\{BoundedContext}\{Component}        // Domain logic
App\Application\{BoundedContext}\{Commands|Queries}  // Application services
App\Infrastructure\{BoundedContext}\{Component}      // Infrastructure concerns
App\Interface\{Api|Console|Web}\{Controllers}        // User interfaces
```

Example file structure:

```
app/
  Domain/
    Subscriptions/
      Events/
      Exceptions/
      SubscriptionAggregate.php
  Application/
    Subscriptions/
      Commands/
        CreateSubscriptionCommand.php
      CommandHandlers/
        CreateSubscriptionHandler.php
      Queries/
        GetSubscriptionsQuery.php
  Infrastructure/
    Subscriptions/
      Projections/
        SubscriptionProjector.php
  Interface/
    Api/
      SubscriptionController.php
```

#### Naming Conventions

- **Classes**: PascalCase
  - `SubscriptionAggregate`, `CreateSubscriptionCommand`
- **Methods**: camelCase
  - `createSubscription()`, `getActiveSubscriptions()`
- **Variables**: camelCase
  - `$billingCycle`, `$nextBillingDate`
- **Constants**: SCREAMING_SNAKE_CASE
  - `DEFAULT_BILLING_CYCLE`, `MAX_SUBSCRIPTIONS`
- **Events**: Past tense verbs
  - `SubscriptionCreated`, `SubscriptionCancelled`
- **Commands**: Imperative verbs
  - `CreateSubscription`, `CancelSubscription`
- **Queries**: Noun or adjective prefixes
  - `GetActiveSubscriptions`, `FindSubscriptionById`

### Event Sourcing Patterns

#### Aggregates

- Each aggregate in its own class extending `AggregateRoot`
- Public methods for handling commands
- Protected methods for applying events
- Validate business rules before recording events
- Include command IDs in event metadata for correlation

```php
class SubscriptionAggregate extends AggregateRoot
{
    // State properties
    private string $id;
    private string $userId;
    private string $name;
    private float $amount;
    private string $billingCycle;
    private string $status = 'active';
    
    // Command method
    public function createSubscription(
        string $id,
        string $userId,
        string $name,
        float $amount,
        string $billingCycle,
        ?string $commandId = null
    ): self {
        // Business rule validation
        if ($amount <= 0) {
            throw new InvalidAmountException("Amount must be positive");
        }
        
        // Create the event
        $event = new SubscriptionCreated($id, $userId, $name, $amount, $billingCycle);
        
        // Add command ID to metadata if provided
        if ($commandId) {
            $event->metadata['commandId'] = $commandId;
        }
        
        // Record the event
        $this->recordThat($event);
        
        return $this;
    }
    
    // Apply method - updates internal state based on the event
    protected function applySubscriptionCreated(SubscriptionCreated $event): void
    {
        $this->id = $event->subscriptionId;
        $this->userId = $event->userId;
        $this->name = $event->name;
        $this->amount = $event->amount;
        $this->billingCycle = $event->billingCycle;
    }
}
```

#### Events

- Immutable data structures with public properties
- Clear naming indicating what happened (past tense)
- Version events explicitly when schema changes
- Include user ID for security auditing when applicable

```php
// Initial version
class SubscriptionCreatedV1 implements ShouldBeStored
{
    public function __construct(
        public string $subscriptionId,
        public string $name,
        public float $amount,
        public string $billingCycle
    ) {
    }
}

// Updated version with additional fields
class SubscriptionCreatedV2 implements ShouldBeStored
{
    public function __construct(
        public string $subscriptionId,
        public string $userId, // New field
        public string $name,
        public float $amount,
        public string $billingCycle,
        public ?string $startDate = null // New field
    ) {
    }
}
```

#### Projections

- One projector class per read model
- Method naming convention: `on{EventName}`
- Include proper error handling and logging
- Design for idempotency (can handle the same event multiple times)

```php
class SubscriptionProjector extends Projector
{
    public function onSubscriptionCreated(SubscriptionCreated $event): void
    {
        try {
            DB::table('subscriptions')->insert([
                'id' => $event->subscriptionId,
                'user_id' => $event->userId,
                'name' => $event->name,
                'amount' => $event->amount,
                'billing_cycle' => $event->billingCycle,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to project SubscriptionCreated', [
                'event' => $event,
                'exception' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
```

#### Command Handlers

- Single responsibility principle (one command per handler)
- Proper validation before executing business logic
- Return types that indicate success/failure
- Consistent error handling approach

```php
class CreateSubscriptionHandler
{
    public function __construct(
        private StoredEventRepository $eventRepository
    ) {}
    
    public function handle(CreateSubscriptionCommand $command): void
    {
        // Additional validation if needed
        $this->validateCommand($command);
        
        // Execute business logic via aggregate
        SubscriptionAggregate::retrieve($command->subscriptionId)
            ->createSubscription(
                $command->subscriptionId,
                $command->userId,
                $command->name,
                $command->amount,
                $command->billingCycle,
                $command->commandId
            )
            ->persist();
    }
    
    private function validateCommand(CreateSubscriptionCommand $command): void
    {
        // Additional validation logic
    }
}
```

### Database & Queries

#### Eloquent Usage

- Prefer query builders over raw SQL
- Define relationships in models
- Use type-safe attribute casting
- Avoid N+1 query problems with eager loading

```php
// ✅ Good
$subscriptions = Subscription::with('payments')
    ->where('user_id', $userId)
    ->where('status', 'active')
    ->get();

// ❌ Bad - N+1
$subscriptions = Subscription::where('user_id', $userId)->get();
foreach ($subscriptions as $subscription) {
    $payments = $subscription->payments; // Separate query for each subscription
}
```

#### Migrations

- Descriptive, timestamped names
- Always include both up and down methods
- Use appropriate column types and constraints
- Include indexes for commonly queried fields

```php
// Migration file: 2023_01_15_create_subscriptions_table.php
public function up(): void
{
    Schema::create('subscriptions', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->uuid('user_id');
        $table->string('name');
        $table->decimal('amount', 10, 2);
        $table->string('billing_cycle');
        $table->string('status');
        $table->date('next_billing_date')->nullable();
        $table->timestamps();
        
        $table->index('user_id');
        $table->index(['status', 'next_billing_date']);
    });
}

public function down(): void
{
    Schema::dropIfExists('subscriptions');
}
```

#### Event Store

- Follow schema defined in documentation
- Include proper indexing for event retrieval
- Consider partitioning for large event stores

```php
// Event store migration
Schema::create('stored_events', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->uuid('aggregate_uuid')->nullable()->index();
    $table->unsignedBigInteger('aggregate_version')->nullable();
    $table->string('event_class');
    $table->jsonb('event_properties');
    $table->jsonb('meta_data');
    $table->timestamp('created_at');
    
    $table->index(['aggregate_uuid', 'aggregate_version']);
    $table->index('event_class');
    $table->index('created_at');
});
```

### Backend Testing

#### Test Organization

- Unit tests for aggregates and domain logic
- Integration tests for projections
- Feature tests for API endpoints
- End-to-end tests for critical user flows

```
tests/
  Unit/
    Domain/
      Subscriptions/
        SubscriptionAggregateTest.php
  Integration/
    Projections/
      SubscriptionProjectorTest.php
  Feature/
    Api/
      SubscriptionControllerTest.php
  EndToEnd/
    SubscriptionWorkflowTest.php
```

#### Naming Conventions

- Test classes: `{Subject}Test`
- Test methods: `test_it_should_{expected_behavior}`

```php
class SubscriptionAggregateTest extends TestCase
{
    public function test_it_should_create_subscription_with_valid_data(): void
    {
        // Test implementation
    }
    
    public function test_it_should_throw_exception_when_amount_is_negative(): void
    {
        // Test implementation
    }
}
```

#### Test Environment

- Use in-memory SQLite for unit tests
- Use test MySQL/PostgreSQL for integration tests
- Separate test event store for event sourcing tests

## Frontend Standards

### React & TypeScript

#### Project Structure

- Feature-based organization matching backend slices
- Shared components in a common directory
- Consistent file naming conventions

```
src/
  features/
    subscriptions/
      components/
        SubscriptionList.tsx
        SubscriptionForm.tsx
        SubscriptionCard.tsx
      hooks/
        useSubscription.ts
      api/
        subscriptionApi.ts
      types/
        subscription.types.ts
      utils/
        subscriptionUtils.ts
  components/            # Shared components
    ui/
      Button/
        Button.tsx
        Button.test.tsx
    layout/
      Card/
      Grid/
  hooks/                 # Shared hooks
    useForm.ts
    useAuth.ts
  utils/                 # Shared utilities
    formatters.ts
    validators.ts
```

#### Component Conventions

- Functional components with hooks
- Props interfaces defined with TypeScript
- Default exports for components, named exports for utilities

```tsx
// SubscriptionCard.tsx
import React from 'react';
import { formatCurrency } from '@/utils/formatters';

interface SubscriptionCardProps {
  id: string;
  name: string;
  amount: number;
  billingCycle: string;
  onRenew?: () => void;
  onCancel?: () => void;
}

const SubscriptionCard: React.FC<SubscriptionCardProps> = ({
  id,
  name,
  amount,
  billingCycle,
  onRenew,
  onCancel
}) => {
  const handleRenew = () => {
    if (onRenew) onRenew();
  };
  
  const handleCancel = () => {
    if (onCancel) onCancel();
  };
  
  return (
    <div className="rounded-lg border p-4">
      <h3 className="text-lg font-semibold">{name}</h3>
      <p className="text-gray-600">{formatCurrency(amount)} / {billingCycle}</p>
      <div className="mt-4 flex space-x-2">
        <button onClick={handleRenew} className="btn-primary">Renew</button>
        <button onClick={handleCancel} className="btn-destructive">Cancel</button>
      </div>
    </div>
  );
};

export default SubscriptionCard;
```

#### Naming Conventions

- **Components**: PascalCase (`SubscriptionCard`)
- **Hooks**: camelCase with `use` prefix (`useSubscriptions`)
- **Files**: Match the component/hook name (`SubscriptionCard.tsx`)
- **Interfaces/Types**: PascalCase with descriptive names (`SubscriptionFormProps`)
- **Event Handlers**: `handle{Event}` (`handleSubmit`)

#### TypeScript Usage

- Strongly typed props and state
- Define interfaces for API responses
- Use type guards for conditional rendering
- Avoid `any` type; use `unknown` when necessary

```tsx
// ✅ Good
interface Subscription {
  id: string;
  name: string;
  amount: number;
  billingCycle: string;
  status: 'active' | 'cancelled' | 'pending';
}

// ✅ Good - Type guard
function isActiveSubscription(subscription: Subscription): boolean {
  return subscription.status === 'active';
}

// ❌ Bad - Using any
function processSubscription(subscription: any) {
  // Implementation using any
}
```

### State Management

#### Local State

- Use React's built-in state management when possible
- Leverage context for shared state within a feature
- Custom hooks for reusable state logic

```tsx
// Custom hook for subscription state
function useSubscription(subscriptionId: string) {
  const [subscription, setSubscription] = useState<Subscription | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<Error | null>(null);
  
  useEffect(() => {
    async function fetchSubscription() {
      setIsLoading(true);
      try {
        const data = await subscriptionApi.getById(subscriptionId);
        setSubscription(data);
        setError(null);
      } catch (err) {
        setError(err instanceof Error ? err : new Error('Unknown error'));
      } finally {
        setIsLoading(false);
      }
    }
    
    fetchSubscription();
  }, [subscriptionId]);
  
  return { subscription, isLoading, error };
}
```

#### Global State

- Consider a simple global state solution like Zustand
- Organize state by domain (subscriptions, expenses, etc.)
- Type all state and actions

```tsx
// Zustand store example
import { create } from 'zustand';

interface SubscriptionState {
  subscriptions: Subscription[];
  isLoading: boolean;
  error: Error | null;
  fetchSubscriptions: () => Promise<void>;
  addSubscription: (subscription: Subscription) => void;
  removeSubscription: (id: string) => void;
}

const useSubscriptionStore = create<SubscriptionState>((set, get) => ({
  subscriptions: [],
  isLoading: false,
  error: null,
  
  fetchSubscriptions: async () => {
    set({ isLoading: true });
    try {
      const subscriptions = await subscriptionApi.getAll();
      set({ subscriptions, error: null });
    } catch (err) {
      set({ error: err instanceof Error ? err : new Error('Unknown error') });
    } finally {
      set({ isLoading: false });
    }
  },
  
  addSubscription: (subscription) => {
    set(state => ({
      subscriptions: [...state.subscriptions, subscription]
    }));
  },
  
  removeSubscription: (id) => {
    set(state => ({
      subscriptions: state.subscriptions.filter(sub => sub.id !== id)
    }));
  }
}));
```

#### API Integration

- Use React Query for data fetching and caching
- Implement optimistic updates for better UX
- Handle loading and error states consistently

```tsx
// React Query example
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';

// Query hook
export function useSubscriptions() {
  return useQuery({
    queryKey: ['subscriptions'],
    queryFn: () => subscriptionApi.getAll()
  });
}

// Mutation with optimistic update
export function useCreateSubscription() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (newSubscription: NewSubscription) => 
      subscriptionApi.create(newSubscription),
    
    // When mutate is called:
    onMutate: async (newSubscription) => {
      // Cancel outgoing refetches
      await queryClient.cancelQueries({ queryKey: ['subscriptions'] });
      
      // Snapshot the previous value
      const previousSubscriptions = queryClient.getQueryData(['subscriptions']);
      
      // Optimistically update to the new value
      queryClient.setQueryData(['subscriptions'], (old: Subscription[] = []) => [
        ...old,
        { 
          id: `temp-${Date.now()}`, 
          ...newSubscription,
          status: 'pending'
        }
      ]);
      
      // Return context with snapshot
      return { previousSubscriptions };
    },
    
    // If the mutation fails, use the context we returned above
    onError: (err, variables, context) => {
      queryClient.setQueryData(
        ['subscriptions'], 
        context?.previousSubscriptions
      );
    },
    
    // Always refetch after error or success:
    onSettled: () => {
      queryClient.invalidateQueries({ queryKey: ['subscriptions'] });
    },
  });
}
```

### Styling with Tailwind

#### Tailwind Usage

- Follow utility-first approach
- Create reusable component patterns in the design system
- Organize classes in a consistent order:
  1. Layout (display, position)
  2. Box model (width, height, margin, padding)
  3. Typography
  4. Visual (colors, backgrounds)
  5. Interactive (hover, focus)

```tsx
// ✅ Good - Organized Tailwind classes
<div className="
  flex items-center justify-between
  w-full p-4 mb-4
  text-sm font-medium
  bg-white border border-gray-200 rounded-lg
  hover:bg-gray-50 focus:ring-2
">
  {/* Content */}
</div>

// ❌ Bad - Disorganized classes
<div className="
  text-sm border bg-white rounded-lg p-4 flex hover:bg-gray-50 mb-4 items-center w-full font-medium justify-between focus:ring-2 border-gray-200
">
  {/* Content */}
</div>
```

#### Theme Configuration

- Extend Tailwind with custom colors from design system
- Define custom component classes for repeated patterns
- Use CSS variables for theme values when appropriate

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        // Primary colors
        'teal': {
          DEFAULT: '#0F766E',
          50: '#F0FDFA',
          // ...other shades
        },
        'slate-blue': {
          DEFAULT: '#1E293B',
          // ...other shades
        },
        // Accent colors
        'sunrise': {
          DEFAULT: '#F97316',
          // ...other shades
        },
        // ...other colors
      },
    },
  },
  plugins: [
    // Custom components
    plugin(function({ addComponents }) {
      const components = {
        '.btn-primary': {
          backgroundColor: '#0F766E',
          color: 'white',
          padding: '0.5rem 1rem',
          borderRadius: '0.375rem',
          fontWeight: '500',
          '&:hover': {
            backgroundColor: '#115E59',
          },
        },
        // ...other components
      }
      addComponents(components)
    }),
  ],
}
```

#### Responsive Design

- Mobile-first approach to all components
- Consistent breakpoint usage
- Test all components across breakpoints

```tsx
// ✅ Good - Mobile-first approach
<div className="
  flex flex-col items-center
  sm:flex-row sm:justify-between
">
  {/* Content */}
</div>

// ❌ Bad - Desktop-first requiring overrides
<div className="
  flex flex-row justify-between
  xs:flex-col xs:items-center
">
  {/* Content */}
</div>
```

### Frontend Testing

#### Component Testing

- Use React Testing Library for component tests
- Focus on user behavior rather than implementation details
- Test accessibility and keyboard navigation

```tsx
// Button.test.tsx
import { render, screen, fireEvent } from '@testing-library/react';
import Button from './Button';

describe('Button', () => {
  test('renders with correct text', () => {
    render(<Button>Click me</Button>);
    expect(screen.getByRole('button', { name: /click me/i })).toBeInTheDocument();
  });
  
  test('calls onClick when clicked', () => {
    const handleClick = jest.fn();
    render(<Button onClick={handleClick}>Click me</Button>);
    fireEvent.click(screen.getByRole('button'));
    expect(handleClick).toHaveBeenCalledTimes(1);
  });
  
  test('can be disabled', () => {
    render(<Button disabled>Click me</Button>);
    expect(screen.getByRole('button')).toBeDisabled();
  });
});
```

#### Test Organization

- Co-locate tests with components (`Button.test.tsx`)
- Group tests logically with `describe` blocks
- Use clear test descriptions

#### Mocking

- Mock API responses consistently
- Use MSW (Mock Service Worker) for API mocking
- Provide type-safe mock data

```tsx
// mocks/handlers.ts
import { rest } from 'msw';

export const handlers = [
  rest.get('/api/subscriptions', (req, res, ctx) => {
    return res(
      ctx.status(200),
      ctx.json([
        {
          id: '1',
          name: 'Netflix',
          amount: 14.99,
          billingCycle: 'monthly',
          status: 'active'
        },
        // More mock data
      ])
    );
  }),
  
  rest.post('/api/subscriptions', (req, res, ctx) => {
    const { name } = req.body as any;
    return res(
      ctx.status(201),
      ctx.json({
        id: '123e4567-e89b-12d3-a456-426614174000',
        name,
        // Other fields from the request
        status: 'active',
        createdAt: new Date().toISOString()
      })
    );
  })
];
```

## Shared Standards

### Code Quality Tools

#### Linting

- ESLint for JavaScript/TypeScript
- PHP_CodeSniffer for PHP
- Strict rules with minimal exceptions

ESLint configuration:

```js
// .eslintrc.js
module.exports = {
  root: true,
  extends: [
    'eslint:recommended',
    'plugin:react/recommended',
    'plugin:react-hooks/recommended',
    'plugin:@typescript-eslint/recommended',
    'prettier'
  ],
  parser: '@typescript-eslint/parser',
  parserOptions: {
    ecmaVersion: 2020,
    sourceType: 'module',
    ecmaFeatures: {
      jsx: true
    }
  },
  rules: {
    // Custom rules
    'react/react-in-jsx-scope': 'off',
    'react/prop-types': 'off',
    '@typescript-eslint/explicit-module-boundary-types': 'off',
    '@typescript-eslint/no-explicit-any': 'error',
    'no-console': ['warn', { allow: ['warn', 'error'] }]
  },
  settings: {
    react: {
      version: 'detect'
    }
  }
};
```

PHP_CodeSniffer configuration:

```xml
<!-- phpcs.xml -->
<?xml version="1.0"?>
<ruleset name="LifeOS">
    <description>LifeOS Coding Standards</description>

    <file>app</file>
    <file>tests</file>

    <arg name="colors"/>
    <arg value="p"/>

    <rule ref="PSR12"/>
    
    <!-- Custom rules -->
    <rule ref="Generic.Files.LineLength">
        <properties>
            <property name="lineLimit" value="120"/>
        </properties>
    </rule>
</ruleset>
```

#### Formatting

- Prettier for JavaScript/TypeScript/CSS
- PHP-CS-Fixer for PHP
- Enforce via pre-commit hooks and CI

Prettier configuration:

```json
// .prettierrc
{
  "singleQuote": true,
  "trailingComma": "es5",
  "tabWidth": 2,
  "semi": true,
  "printWidth": 80,
  "bracketSpacing": true,
  "jsxBracketSameLine": false
}
```

PHP-CS-Fixer configuration:

```php
// .php-cs-fixer.php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->notName('*.blade.php');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'not_operator_with_successor_space' => true,
        'trailing_comma_in_multiline' => true,
        'phpdoc_scalar' => true,
        'unary_operator_spaces' => true,
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
        ],
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_var_without_name' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => true,
        ]
    ])
    ->setFinder($finder);
```

#### Static Analysis

- PHPStan for PHP code
- TypeScript strict mode
- SonarQube for overall code quality metrics

PHPStan configuration:

```neon
# phpstan.neon
parameters:
    level: 8
    paths:
        - app
    excludePaths:
        - app/Http/Middleware/Authenticate.php
    checkMissingIterableValueType: false
```

### Documentation

#### Code Comments

- Use PHPDoc for PHP and JSDoc for JavaScript/TypeScript
- Document non-obvious logic with inline comments
- Include examples for complex functions

PHP Example:

```php
/**
 * Calculate the next billing date based on the current date and billing cycle.
 *
 * @param string $currentDate The current billing date in Y-m-d format
 * @param string $billingCycle The billing cycle (monthly, quarterly, yearly)
 * @return string The next billing date in Y-m-d format
 *
 * @throws InvalidArgumentException If the billing cycle is not supported
 */
public function calculateNextBillingDate(string $currentDate, string $billingCycle): string
{
    $date = Carbon::parse($currentDate);
    
    return match($billingCycle) {
        'monthly' => $date->addMonth()->toDateString(),
        'quarterly' => $date->addMonths(3)->toDateString(),
        'yearly' => $date->addYear()->toDateString(),
        default => throw new \InvalidArgumentException("Unsupported billing cycle: {$billingCycle}"),
    };
}
```

TypeScript Example:

```typescript
/**
 * Format a monetary amount with the appropriate currency symbol and decimals.
 * 
 * @param amount - The amount to format
 * @param currency - The currency code (default: 'USD')
 * @param locale - The locale to use for formatting (default: 'en-US')
 * @returns The formatted currency string
 * 
 * @example
 * ```
 * formatCurrency(10.5) // '$10.50'
 * formatCurrency(10.5, 'EUR', 'de-DE') // '10,50 €'
 * ```
 */
export function formatCurrency(
  amount: number, 
  currency: string = 'USD', 
  locale: string = 'en-US'
): string {
  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency,
  }).format(amount);
}
```

#### Documentation Files

- Update README.md with setup instructions
- Create/update specific documentation for new features
- Document architectural decisions

### Git Workflow

#### Branch Naming

- Feature branches: `feature/{ticket-number}-{short-description}`
- Bug fixes: `fix/{ticket-number}-{short-description}`
- Releases: `release/v{major}.{minor}.{patch}`

#### Commit Messages

- Follow conventional commits format
- Include ticket number when applicable
- Descriptive but concise messages

Examples:

```
feat(subscription): add recurring payment functionality (LOS-123)
fix(expenses): correct amount calculation in monthly summary (LOS-124)
docs: update event sourcing documentation
chore: update dependencies
```

#### Pull Requests

- Include description of changes
- Reference tickets and issues
- Require code review before merging

Pull request template:

```markdown
## Description

[Describe the changes made in this PR]

## Related Issues

- Fixes #[issue_number]
- Relates to #[issue_number]

## Type of change

- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Checklist

- [ ] My code follows the coding standards of this project
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] I have updated the documentation accordingly
- [ ] All new and existing tests passed
```

## Implementation Notes

This document outlines the coding standards for the LifeOS project. It is expected that all team members follow these standards for all code contributions.

The standards described here are not exhaustive and will evolve over time. Always consider the existing codebase and maintain consistency with established patterns.

Regular reviews of this document will be conducted to ensure it remains current and effective. 
