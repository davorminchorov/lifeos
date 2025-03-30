# LifeOS Event Sourcing Guide

This document provides detailed information on how event sourcing is implemented in the LifeOS application, including patterns, practices, and examples.

## Event Sourcing Overview

Event sourcing is an architectural pattern where:

1. **Events as Source of Truth**: Application state is derived from a sequence of events
2. **Immutable Event Log**: Events are stored in an append-only log
3. **State Reconstruction**: Current state can be rebuilt by replaying events
4. **Complete History**: All changes to the system are captured as events

## Core Concepts

### Events

Events are immutable records of something that happened in the domain. They're usually named in past tense and contain all information relevant to the change.

```php
class SubscriptionCreated implements ShouldBeStored
{
    public function __construct(
        public string $subscriptionId,
        public string $userId,
        public string $name,
        public float $amount,
        public string $billingCycle,
        public string $startDate
    ) {
    }
}
```

### Aggregates

Aggregates are domain entities that encapsulate business rules and ensure consistency. They emit events when their state changes.

```php
class SubscriptionAggregate extends AggregateRoot
{
    // State properties
    private string $id;
    private string $userId;
    private string $status;
    
    // Command methods - perform business rules and emit events
    public function createSubscription(string $id, string $userId, string $name, float $amount, string $billingCycle, string $startDate): self
    {
        // Business rule validation
        if ($amount <= 0) {
            throw new InvalidAmountException("Amount must be positive");
        }
        
        // Record the event if validation passes
        $this->recordThat(new SubscriptionCreated($id, $userId, $name, $amount, $billingCycle, $startDate));
        
        return $this;
    }
    
    // Apply methods - update internal state based on events
    protected function applySubscriptionCreated(SubscriptionCreated $event): void
    {
        $this->id = $event->subscriptionId;
        $this->userId = $event->userId;
        $this->status = 'active';
    }
}
```

### Projections (Read Models)

Projections transform event streams into optimized read models for querying.

```php
class SubscriptionProjector extends Projector
{
    public function onSubscriptionCreated(SubscriptionCreated $event): void
    {
        DB::table('subscriptions')->insert([
            'id' => $event->subscriptionId,
            'user_id' => $event->userId,
            'name' => $event->name,
            'amount' => $event->amount,
            'billing_cycle' => $event->billingCycle,
            'status' => 'active',
            'start_date' => $event->startDate,
            'next_billing_date' => $this->calculateNextBillingDate($event->startDate, $event->billingCycle),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    public function onSubscriptionCancelled(SubscriptionCancelled $event): void
    {
        DB::table('subscriptions')
            ->where('id', $event->subscriptionId)
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => $event->cancelledAt,
                'updated_at' => now(),
            ]);
    }
    
    private function calculateNextBillingDate(string $startDate, string $billingCycle): string
    {
        $date = Carbon::parse($startDate);
        
        return match($billingCycle) {
            'monthly' => $date->addMonth()->toDateString(),
            'quarterly' => $date->addMonths(3)->toDateString(),
            'yearly' => $date->addYear()->toDateString(),
            default => $date->addMonth()->toDateString(),
        };
    }
}
```

## Event Sourcing Implementation in LifeOS

LifeOS uses the [Spatie Laravel Event Sourcing](https://spatie.be/docs/laravel-event-sourcing) package for its event sourcing implementation. We chose this package for several reasons:

1. **Seamless Laravel Integration**: Native Laravel event system compatibility
2. **Active Maintenance**: Regular updates and active community support
3. **Comprehensive Features**: Built-in support for snapshots, projectors, and reactors
4. **Excellent Documentation**: Well-documented with many examples
5. **Performance Optimizations**: Support for database connections, queue workers, and more

### Event Store

Events are stored in a dedicated `stored_events` table with the following structure:

```php
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

For larger systems, we recommend partitioning the event store:

```sql
-- Example: Partitioning by month
CREATE TABLE stored_events (
    id BIGINT AUTO_INCREMENT,
    aggregate_uuid CHAR(36),
    aggregate_version INT,
    event_class VARCHAR(255),
    event_properties JSON,
    meta_data JSON,
    created_at TIMESTAMP,
    PRIMARY KEY (id, created_at)
) PARTITION BY RANGE (UNIX_TIMESTAMP(created_at)) (
    PARTITION p_2023_01 VALUES LESS THAN (UNIX_TIMESTAMP('2023-02-01')),
    PARTITION p_2023_02 VALUES LESS THAN (UNIX_TIMESTAMP('2023-03-01')),
    -- Add more partitions as needed
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

### Command Pattern Implementation

We use the command pattern to encapsulate user intent:

```php
class CreateSubscriptionCommand
{
    public function __construct(
        public string $subscriptionId,
        public string $userId,
        public string $name,
        public float $amount,
        public string $billingCycle,
        public string $startDate,
        public ?string $commandId = null // For correlation with events
    ) {
        $this->commandId ??= (string) Str::uuid();
    }
}

class CreateSubscriptionHandler
{
    public function __construct(
        private StoredEventRepository $eventRepository
    ) {}
    
    public function handle(CreateSubscriptionCommand $command): void
    {
        // Perform any validation or authorization checks
        
        // Create and persist the aggregate
        SubscriptionAggregate::retrieve($command->subscriptionId)
            ->createSubscription(
                $command->subscriptionId,
                $command->userId,
                $command->name,
                $command->amount,
                $command->billingCycle,
                $command->startDate
            )
            ->persist();
    }
}
```

### Storing Events

Events are stored through the aggregate:

```php
SubscriptionAggregate::retrieve($subscriptionId)
    ->createSubscription($subscriptionId, $userId, 'Netflix', 14.99, 'monthly', '2023-01-01')
    ->persist();
```

This will:
1. Create a `SubscriptionCreated` event
2. Apply the event to update the aggregate's state
3. Store the event in the event store
4. Dispatch the event to projectors and reactors

### Replaying Events

Events can be replayed to rebuild projections:

```php
php artisan event-sourcing:replay
```

You can also replay events for specific projectors:

```php
php artisan event-sourcing:replay "App\\Domain\\Subscriptions\\Projections\\SubscriptionProjector"
```

Or replay events from a specific starting point:

```php
php artisan event-sourcing:replay --from=<stored-event-id>
```

### Blue/Green Projection Rebuilding

To minimize downtime during projection rebuilds, LifeOS implements a blue/green deployment strategy:

```php
class ProjectionRebuildCommand extends Command
{
    public function handle()
    {
        // 1. Create temporary duplicate tables with _rebuild suffix
        Schema::create('subscriptions_rebuild', function (Blueprint $table) {
            // Copy structure from original table
            $this->copyTableStructure('subscriptions', 'subscriptions_rebuild');
        });
        
        // 2. Redirect projectors to write to new tables
        config(['event-sourcing.projection_suffix' => '_rebuild']);
        
        // 3. Replay events to build new tables
        app(Projectionist::class)->replay(
            // Can target specific projectors if needed
            collect([app(SubscriptionProjector::class)])
        );
        
        // 4. Atomic swap - replace old tables with new ones
        DB::transaction(function () {
            Schema::rename('subscriptions', 'subscriptions_old');
            Schema::rename('subscriptions_rebuild', 'subscriptions');
            
            // Clean up old table after successful swap
            Schema::dropIfExists('subscriptions_old');
        });
        
        // 5. Reset configuration
        config(['event-sourcing.projection_suffix' => '']);
    }
    
    private function copyTableStructure(string $sourceTable, string $targetTable): void
    {
        // Implementation depends on database system
        if (config('database.default') === 'mysql') {
            DB::statement("CREATE TABLE {$targetTable} LIKE {$sourceTable}");
        } else {
            // PostgreSQL or SQLite implementation
        }
    }
}
```

This approach allows us to rebuild projections without downtime:
1. Users continue to read from the current tables
2. New events during rebuild are applied to both old and new tables
3. When rebuild is complete, tables are swapped atomically

## Advanced Event Sourcing Patterns

### Snapshots

For aggregates with many events, snapshots can improve performance:

```php
class SubscriptionAggregate extends AggregateRoot
{
    use SnapshotTrait;
    
    // State properties
    private string $id;
    private string $userId;
    private string $name;
    private float $amount;
    private string $billingCycle;
    private string $status = 'inactive';
    private ?Carbon $startDate = null;
    private ?Carbon $nextBillingDate = null;
    
    protected function getSnapshotVersion(): int
    {
        return 1; // Increment when snapshot structure changes
    }
    
    protected function getSnapshotState(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'name' => $this->name,
            'amount' => $this->amount,
            'billingCycle' => $this->billingCycle,
            'status' => $this->status,
            'startDate' => $this->startDate ? $this->startDate->toIso8601String() : null,
            'nextBillingDate' => $this->nextBillingDate ? $this->nextBillingDate->toIso8601String() : null,
        ];
    }
    
    protected function restoreFromSnapshotState(array $state): void
    {
        $this->id = $state['id'];
        $this->userId = $state['userId'];
        $this->name = $state['name'] ?? '';
        $this->amount = $state['amount'] ?? 0.0;
        $this->billingCycle = $state['billingCycle'] ?? 'monthly';
        $this->status = $state['status'] ?? 'inactive';
        $this->startDate = isset($state['startDate']) ? Carbon::parse($state['startDate']) : null;
        $this->nextBillingDate = isset($state['nextBillingDate']) ? Carbon::parse($state['nextBillingDate']) : null;
    }
}
```

Snapshots are taken automatically at configurable intervals:

```php
// config/event-sourcing.php
'snapshot_when_event_count_reaches' => 50,
```

### Event Versioning

As your domain evolves, events may need to change. LifeOS implements a comprehensive event versioning strategy using upcasters:

#### 1. Version Naming

We explicitly version events in the class name:

```php
// Original version
class SubscriptionCreatedV1 implements ShouldBeStored
{
    public function __construct(
        public string $subscriptionId,
        public string $name,
        public float $amount,
        public string $billingCycle,
        public string $startDate
    ) {
    }
}

// New version with additional fields
class SubscriptionCreatedV2 implements ShouldBeStored
{
    public function __construct(
        public string $subscriptionId,
        public string $userId, // New field
        public string $name,
        public float $amount,
        public string $billingCycle,
        public string $startDate,
        public ?int $trialPeriodDays = null // New optional field
    ) {
    }
}
```

#### 2. Event Upcasters

We implement upcasters to transform old event versions to new ones:

```php
class SubscriptionCreatedEventUpcaster implements EventUpcaster
{
    public function canUpcast(StoredEvent $storedEvent): bool
    {
        return $storedEvent->event_class === SubscriptionCreatedV1::class;
    }

    public function upcast(StoredEvent $storedEvent): StoredEvent 
    {
        $eventData = $storedEvent->event_properties;
        
        // Add missing fields with default values
        $eventData['userId'] = $eventData['userId'] ?? 'unknown';
        $eventData['trialPeriodDays'] = $eventData['trialPeriodDays'] ?? 0;
        
        // Create new stored event with updated class and data
        return new StoredEvent(
            $storedEvent->aggregate_uuid,
            $storedEvent->aggregate_version,
            SubscriptionCreatedV2::class,
            $eventData,
            $storedEvent->meta_data,
            $storedEvent->created_at
        );
    }
}
```

#### 3. Upcaster Registration

Register upcasters in the event sourcing configuration:

```php
// config/event-sourcing.php
'event_upcasters' => [
    App\Infrastructure\EventSourcing\Upcasters\SubscriptionCreatedEventUpcaster::class,
],
```

#### 4. Deployment Strategy for Event Changes

When changing event schemas:
1. First deploy the upcasters and code that can handle both old and new event versions
2. Once deployed and stable, deploy code that produces the new event versions
3. This ensures backward compatibility during deployment

### Integrating with Laravel Reverb for Real-time Updates

LifeOS integrates with Laravel Reverb to provide real-time updates based on domain events:

```php
class EventBroadcaster
{
    public function subscribe($events)
    {
        $events->listen(
            SubscriptionCreated::class,
            [self::class, 'onSubscriptionCreated']
        );
        
        $events->listen(
            SubscriptionCancelled::class,
            [self::class, 'onSubscriptionCancelled']
        );
    }
    
    public function onSubscriptionCreated(SubscriptionCreated $event)
    {
        broadcast(new SubscriptionCreatedEvent(
            $event->subscriptionId,
            $event->name,
            $event->amount,
            $event->billingCycle
        ))->toChannel('user.' . $event->userId);
    }
    
    public function onSubscriptionCancelled(SubscriptionCancelled $event)
    {
        // Find the user ID from the subscription ID
        $subscription = Subscription::findOrFail($event->subscriptionId);
        
        broadcast(new SubscriptionCancelledEvent(
            $event->subscriptionId,
            $event->reason
        ))->toChannel('user.' . $subscription->user_id);
    }
}
```

Clients connect to Reverb channels and receive real-time updates:

```javascript
// React client example
import { ReverbConnection } from 'laravel-reverb';

const reverb = new ReverbConnection({
    host: 'reverb.example.com',
    appId: 'lifeos',
    key: process.env.REVERB_KEY
});

// Subscribe to user-specific channel
reverb.subscribe(`private-user.${userId}`)
      .listen('SubscriptionCreatedEvent', (event) => {
          // Update client-side state
          dispatch({ type: 'SUBSCRIPTION_CREATED', payload: event });
      });
```

### Process Managers (Sagas)

Process managers coordinate workflows across multiple aggregates:

```php
class SubscriptionRenewalProcess implements EventHandler
{
    public function onSubscriptionRenewalDue(SubscriptionRenewalDue $event): void
    {
        // Create a payment command
        $command = new ProcessSubscriptionPaymentCommand(
            $event->subscriptionId,
            $event->amount
        );
        
        // Dispatch to appropriate handler
        $this->commandBus->dispatch($command);
    }
    
    public function onPaymentSucceeded(PaymentSucceeded $event): void
    {
        // Update subscription with new billing date
        $command = new UpdateSubscriptionBillingDateCommand(
            $event->subscriptionId
        );
        
        $this->commandBus->dispatch($command);
    }
    
    public function onPaymentFailed(PaymentFailed $event): void
    {
        // Handle failed payment
        $command = new MarkSubscriptionPaymentFailedCommand(
            $event->subscriptionId,
            $event->reason
        );
        
        $this->commandBus->dispatch($command);
    }
}
```

Register process managers in the event sourcing configuration:

```php
// config/event-sourcing.php
'process_managers' => [
    App\Domain\Subscriptions\ProcessManagers\SubscriptionRenewalProcess::class,
],
```

## Event Sourcing in Action: Complete Example

Let's walk through a complete example of implementing a feature using event sourcing:

### 1. Define Events

```php
// app/Domain/Expenses/Events/ExpenseRecorded.php
class ExpenseRecorded implements ShouldBeStored
{
    public function __construct(
        public string $expenseId,
        public string $userId,
        public string $description,
        public float $amount,
        public string $category,
        public string $date,
        public ?string $notes,
        public ?string $commandId = null // For correlation
    ) {
    }
}

// app/Domain/Expenses/Events/ExpenseCategorized.php
class ExpenseCategorized implements ShouldBeStored
{
    public function __construct(
        public string $expenseId,
        public string $oldCategory,
        public string $newCategory
    ) {
    }
}
```

### 2. Create Aggregate

```php
// app/Domain/Expenses/ExpenseAggregate.php
class ExpenseAggregate extends AggregateRoot
{
    private string $id;
    private string $userId;
    private string $category;
    
    public function recordExpense(
        string $expenseId,
        string $userId,
        string $description,
        float $amount,
        string $category,
        string $date,
        ?string $notes = null,
        ?string $commandId = null
    ): self {
        if ($amount <= 0) {
            throw new InvalidExpenseAmountException("Expense amount must be positive");
        }
        
        $this->recordThat(new ExpenseRecorded(
            $expenseId,
            $userId,
            $description,
            $amount,
            $category,
            $date,
            $notes,
            $commandId
        ));
        
        return $this;
    }
    
    public function categorize(string $newCategory): self
    {
        if ($this->category === $newCategory) {
            return $this;
        }
        
        $this->recordThat(new ExpenseCategorized(
            $this->id,
            $this->category,
            $newCategory
        ));
        
        return $this;
    }
    
    protected function applyExpenseRecorded(ExpenseRecorded $event): void
    {
        $this->id = $event->expenseId;
        $this->userId = $event->userId;
        $this->category = $event->category;
    }
    
    protected function applyExpenseCategorized(ExpenseCategorized $event): void
    {
        $this->category = $event->newCategory;
    }
}
```

### 3. Implement Commands and Handlers

```php
// app/Application/Expenses/Commands/RecordExpenseCommand.php
class RecordExpenseCommand
{
    public function __construct(
        public string $expenseId,
        public string $userId,
        public string $description,
        public float $amount,
        public string $category,
        public string $date,
        public ?string $notes,
        public ?string $commandId = null
    ) {
        $this->commandId ??= (string) Str::uuid();
        $this->expenseId ??= (string) Str::uuid();
    }
}

// app/Application/Expenses/CommandHandlers/RecordExpenseHandler.php
class RecordExpenseHandler
{
    public function handle(RecordExpenseCommand $command): void
    {
        // Create and persist the aggregate
        ExpenseAggregate::retrieve($command->expenseId)
            ->recordExpense(
                $command->expenseId,
                $command->userId,
                $command->description,
                $command->amount,
                $command->category,
                $command->date,
                $command->notes,
                $command->commandId
            )
            ->persist();
    }
}
```

### 4. Implement Projectors

```php
// app/Infrastructure/Projections/ExpenseProjector.php
class ExpenseProjector extends Projector
{
    public function onExpenseRecorded(ExpenseRecorded $event): void
    {
        // Create expense record
        DB::table('expenses')->insert([
            'id' => $event->expenseId,
            'user_id' => $event->userId,
            'description' => $event->description,
            'amount' => $event->amount,
            'category' => $event->category,
            'date' => $event->date,
            'notes' => $event->notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Update category summary
        DB::table('category_summaries')
            ->updateOrInsert(
                ['user_id' => $event->userId, 'category' => $event->category],
                [
                    'total' => DB::raw('total + ' . $event->amount),
                    'updated_at' => now(),
                ]
            );
    }
    
    public function onExpenseCategorized(ExpenseCategorized $event): void
    {
        $expense = Expense::find($event->expenseId);
        
        if (!$expense) {
            return;
        }
        
        // Update the expense category
        $expense->update(['category' => $event->newCategory]);
        
        // Update category summaries
        $amount = $expense->amount;
        
        CategorySummary::where('category', $event->oldCategory)
            ->where('user_id', $expense->user_id)
            ->update(['total' => DB::raw('total - ' . $amount)]);
            
        CategorySummary::updateOrCreate(
            ['user_id' => $expense->user_id, 'category' => $event->newCategory],
            ['total' => DB::raw('total + ' . $amount)]
        );
    }
}
```

### 5. Create API Controller

```php
// app/Interface/Api/ExpenseController.php
class ExpenseController extends Controller
{
    private $commandBus;
    
    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:50',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'command_id' => 'nullable|uuid',
        ]);
        
        $expenseId = (string) Str::uuid();
        $userId = Auth::id();
        
        $command = new RecordExpenseCommand(
            $expenseId,
            $userId,
            $validated['description'],
            $validated['amount'],
            $validated['category'],
            $validated['date'],
            $validated['notes'] ?? null,
            $validated['command_id'] ?? null
        );
        
        $this->commandBus->dispatch($command);
        
        return response()->json([
            'id' => $expenseId,
            'command_id' => $command->commandId
        ], 201);
    }
}
```

## Best Practices

### 1. Keep Events Simple

Events should contain only the data that changed, not derived data. They should be simple data containers without behavior.

### 2. Design for Versioning

Anticipate that events will evolve over time and design your system to handle versioning from the start.

### 3. Consider Event Ownership

Each event should be "owned" by a specific aggregate, which is responsible for validating commands and emitting the appropriate events.

### 4. Optimize Projections

Projections should be optimized for the queries they support. Don't hesitate to create multiple projections from the same events to support different query patterns.

### 5. Test at Different Levels

- Unit test aggregates to verify business rules
- Integration test projectors to verify read model updates
- End-to-end test API endpoints

### 6. Handle Idempotency

Ensure that projectors and reactors can handle the same event multiple times without side effects.

### 7. Implement Optimistic UI Updates

For better user experience:
1. Generate command IDs on the client
2. Update UI optimistically
3. Include command ID in events
4. Listen for confirmation events
5. Revert UI if confirmation doesn't arrive

## Troubleshooting

### Projections Out of Sync

If projections become out of sync with events:

1. Identify which projectors need to be rebuilt
2. Use the blue/green rebuild strategy to minimize downtime
3. Monitor the rebuild process for errors

### Event Store Growing Too Large

For large event stores:

1. Implement snapshots for frequently accessed aggregates
2. Use database partitioning for the event store table
3. Consider archiving old events to cold storage
4. Optimize event store indexing

### Handling Failed Events

If an event handler fails:

1. Configure exception handling in the event sourcing config
2. Implement a retry mechanism for failed events
3. Set up monitoring to alert on persistent failures 
