# LifeOS Backend Architecture

## Vertical Slice Architecture with Event Sourcing

LifeOS backend is built using a combination of vertical slice architecture and event sourcing, providing a robust foundation for tracking and managing personal life operations.

### Key Architectural Principles

1. **Vertical Slice Architecture**
   - Organizes code by business capabilities rather than technical layers
   - Each feature slice contains all necessary components (controllers, services, repositories)
   - Minimizes cross-slice dependencies for better maintainability
   - Enables independent development and deployment of features

2. **Event Sourcing**
   - Uses events as the primary source of truth
   - Stores complete history of domain changes
   - Enables powerful audit trails and temporal queries
   - Supports rebuilding state from event history

3. **Command Query Responsibility Segregation (CQRS)**
   - Separates write operations (commands) from read operations (queries)
   - Optimizes read and write models independently
   - Improves scalability and performance

## System Structure

```
app/
├── Domain/                      # Domain-driven core
│   ├── Subscriptions/           # Subscription management domain
│   │   ├── Commands/            # Command handlers
│   │   ├── Events/              # Domain events
│   │   ├── Projections/         # Read models
│   │   ├── Exceptions/          # Domain exceptions
│   │   ├── Models/              # Domain models
│   │   └── Services/            # Domain services
│   ├── Bills/                   # Bills management domain
│   ├── Investments/             # Investment tracking domain
│   ├── Jobs/                    # Job applications domain
│   ├── Expenses/                # Expense tracking domain
│   └── Shared/                  # Shared domain concepts
├── Application/                 # Application services
│   ├── Subscriptions/           # Subscription feature slice
│   │   ├── Commands/            # Command DTOs
│   │   ├── Queries/             # Query DTOs
│   │   ├── CommandHandlers/     # Command handlers
│   │   ├── QueryHandlers/       # Query handlers
│   │   └── ViewModels/          # Data transfer objects
│   ├── Bills/                   # Bills feature slice 
│   ├── Investments/             # Investments feature slice
│   ├── Jobs/                    # Jobs feature slice
│   ├── Expenses/                # Expenses feature slice
│   └── Shared/                  # Shared application services
├── Infrastructure/              # Infrastructure concerns
│   ├── EventSourcing/           # Event sourcing implementation
│   ├── Persistence/             # Database adapters
│   ├── Messaging/               # Message bus implementation
│   └── ExternalServices/        # External API integrations
└── Interface/                   # User interfaces
    ├── Api/                     # API controllers
    ├── Console/                 # Console commands
    └── Web/                     # Web controllers (if any)
```

## Event Sourcing Implementation

### Event Lifecycle

1. **Command Submission**
   - External request comes through API controller
   - Command object is created and validated
   - Command is dispatched to the appropriate handler

2. **Command Handling**
   - Command handler loads the aggregate (domain model)
   - Business logic determines if command can be executed
   - Aggregate generates and applies domain events

3. **Event Persistence**
   - Events are persisted to the event store in atomic operation
   - Events are timestamped and versioned for optimistic concurrency

4. **Event Publishing**
   - Stored events are published to event bus
   - Projectors and process managers subscribe to relevant events

5. **Projection Updates**
   - Projectors update read models based on events
   - Read models are optimized for query performance

### Aggregate Structure

Aggregates are the central domain models that encapsulate business rules:

```php
class SubscriptionAggregate extends AggregateRoot
{
    private string $id;
    private string $name;
    private float $amount;
    private string $billingCycle;
    private string $status;
    
    public static function create(string $id, string $name, float $amount, string $billingCycle): self
    {
        $aggregate = new self();
        
        $aggregate->recordThat(new SubscriptionCreated($id, $name, $amount, $billingCycle));
        
        return $aggregate;
    }
    
    public function cancel(string $reason): void
    {
        if ($this->status === 'cancelled') {
            throw new SubscriptionAlreadyCancelledException($this->id);
        }
        
        $this->recordThat(new SubscriptionCancelled($this->id, $reason));
    }
    
    protected function applySubscriptionCreated(SubscriptionCreated $event): void
    {
        $this->id = $event->subscriptionId;
        $this->name = $event->name;
        $this->amount = $event->amount;
        $this->billingCycle = $event->billingCycle;
        $this->status = 'active';
    }
    
    protected function applySubscriptionCancelled(SubscriptionCancelled $event): void
    {
        $this->status = 'cancelled';
    }
}
```

### Event Structure

Events are immutable value objects that represent facts that occurred in the domain:

```php
class SubscriptionCreated implements ShouldBeStored
{
    public string $subscriptionId;
    public string $name;
    public float $amount;
    public string $billingCycle;
    public string $createdAt;
    
    public function __construct(string $subscriptionId, string $name, float $amount, string $billingCycle)
    {
        $this->subscriptionId = $subscriptionId;
        $this->name = $name;
        $this->amount = $amount;
        $this->billingCycle = $billingCycle;
        $this->createdAt = now()->toIso8601String();
    }
}
```

### Projection Implementation

Projections build read models from event streams:

```php
class ActiveSubscriptionsProjection implements Projection
{
    private $repository;
    
    public function __construct(ActiveSubscriptionsRepository $repository)
    {
        $this->repository = $repository;
    }
    
    public function when(object $event): void
    {
        switch (get_class($event)) {
            case SubscriptionCreated::class:
                $this->whenSubscriptionCreated($event);
                break;
            case SubscriptionCancelled::class:
                $this->whenSubscriptionCancelled($event);
                break;
        }
    }
    
    private function whenSubscriptionCreated(SubscriptionCreated $event): void
    {
        $this->repository->add([
            'id' => $event->subscriptionId,
            'name' => $event->name,
            'amount' => $event->amount,
            'billing_cycle' => $event->billingCycle,
            'status' => 'active',
            'created_at' => $event->createdAt
        ]);
    }
    
    private function whenSubscriptionCancelled(SubscriptionCancelled $event): void
    {
        $this->repository->updateStatus($event->subscriptionId, 'cancelled');
    }
}
```

## API Layer

The API layer is organized by feature slice and follows RESTful principles:

```php
class SubscriptionController extends Controller
{
    private $commandBus;
    private $queryBus;
    
    public function __construct(CommandBus $commandBus, QueryBus $queryBus)
    {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }
    
    public function index(Request $request)
    {
        $subscriptions = $this->queryBus->dispatch(
            new GetSubscriptionsQuery($request->query('status'))
        );
        
        return response()->json($subscriptions);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly,weekly',
        ]);
        
        $id = Str::uuid()->toString();
        
        $this->commandBus->dispatch(
            new CreateSubscriptionCommand(
                $id,
                $validated['name'],
                $validated['amount'],
                $validated['billing_cycle']
            )
        );
        
        return response()->json(['id' => $id], 201);
    }
    
    public function cancel(Request $request, string $id)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);
        
        $this->commandBus->dispatch(
            new CancelSubscriptionCommand($id, $validated['reason'] ?? null)
        );
        
        return response()->noContent();
    }
}
```

## Real-time Updates with Laravel Reverb

LifeOS uses Laravel Reverb for WebSocket communication:

1. **Event Broadcasting**:
   - Events are broadcast to appropriate channels
   - Authorization guards ensure proper access control

2. **WebSocket Connections**:
   - Frontend connects to WebSocket server
   - Authentication ensures secure connections
   - Client subscribes to relevant channels

3. **Real-time Updates**:
   - Updates flow from event projections to clients
   - UI reacts to incoming real-time data

## Data Consistency

To maintain data consistency:

1. **Optimistic Concurrency Control**:
   - Event streams are versioned
   - Concurrent modifications are detected and rejected

2. **Eventual Consistency**:
   - Read models may be temporarily out of sync with write models
   - System converges to a consistent state over time

3. **Idempotent Processing**:
   - Commands and events can be safely reprocessed
   - Duplicate events are handled properly 
