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
    private string $status;
    
    // Command methods - perform business rules and emit events
    public function createSubscription(string $id, string $name, float $amount, string $billingCycle): self
    {
        // Business rule validation
        if ($amount <= 0) {
            throw new InvalidAmountException("Amount must be positive");
        }
        
        // Record the event if validation passes
        $this->recordThat(new SubscriptionCreated($id, $name, $amount, $billingCycle));
        
        return $this;
    }
    
    // Apply methods - update internal state based on events
    protected function applySubscriptionCreated(SubscriptionCreated $event): void
    {
        $this->id = $event->subscriptionId;
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
            'name' => $event->name,
            'amount' => $event->amount,
            'billing_cycle' => $event->billingCycle,
            'status' => 'active',
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
}
```

## Event Sourcing Implementation in LifeOS

LifeOS uses the [Spatie Laravel Event Sourcing](https://spatie.be/docs/laravel-event-sourcing) package for its event sourcing implementation.

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
    
    $table->index('event_class');
    $table->index('created_at');
});
```

### Storing Events

Events are stored through the aggregate:

```php
SubscriptionAggregate::retrieve($subscriptionId)
    ->createSubscription($subscriptionId, 'Netflix', 14.99, 'monthly')
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

## Advanced Event Sourcing Patterns

### Snapshots

For aggregates with many events, snapshots can improve performance:

```php
class SubscriptionAggregate extends AggregateRoot
{
    use SnapshotTrait;
    
    protected function getSnapshotVersion(): int
    {
        return 1; // Increment when snapshot structure changes
    }
    
    protected function getSnapshotState(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            // Other state properties
        ];
    }
    
    protected function restoreFromSnapshotState(array $state): void
    {
        $this->id = $state['id'];
        $this->status = $state['status'];
        // Restore other state properties
    }
}
```

Snapshots are taken automatically at configurable intervals:

```php
// config/event-sourcing.php
'snapshot_when_event_count_reaches' => 100,
```

### Event Versioning

As your domain evolves, events may need to change. LifeOS handles event versioning using upcasting:

```php
class SubscriptionCreatedUpCaster implements EventUpcaster
{
    public function canUpcast(string $eventClass, array $eventData): bool
    {
        return $eventClass === SubscriptionCreated::class && !isset($eventData['category']);
    }
    
    public function upcast(string $eventClass, array $eventData): array
    {
        // Add missing properties with default values
        $eventData['category'] = 'entertainment';
        
        return $eventData;
    }
}
```

Register upcasters in the event sourcing configuration:

```php
// config/event-sourcing.php
'event_upcasters' => [
    App\Infrastructure\EventSourcing\Upcasters\SubscriptionCreatedUpCaster::class,
],
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
    
    public function onSubscriptionPaymentSucceeded(SubscriptionPaymentSucceeded $event): void
    {
        // Update next billing date
        $command = new UpdateNextBillingDateCommand(
            $event->subscriptionId,
            $event->nextBillingDate
        );
        
        $this->commandBus->dispatch($command);
    }
    
    public function onSubscriptionPaymentFailed(SubscriptionPaymentFailed $event): void
    {
        // Handle failed payment
        $command = new SendSubscriptionPaymentFailedNotification(
            $event->subscriptionId,
            $event->failureReason
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
        public string $description,
        public float $amount,
        public string $category,
        public string $date,
        public ?string $notes
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
    private string $category;
    
    public function recordExpense(
        string $expenseId,
        string $description,
        float $amount,
        string $category,
        string $date,
        ?string $notes = null
    ): self {
        if ($amount <= 0) {
            throw new InvalidExpenseAmountException("Expense amount must be positive");
        }
        
        $this->recordThat(new ExpenseRecorded(
            $expenseId,
            $description,
            $amount,
            $category,
            $date,
            $notes
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
        public string $description,
        public float $amount,
        public string $category,
        public string $date,
        public ?string $notes
    ) {
    }
}

// app/Application/Expenses/CommandHandlers/RecordExpenseHandler.php
class RecordExpenseHandler
{
    public function handle(RecordExpenseCommand $command): void
    {
        ExpenseAggregate::retrieve($command->expenseId)
            ->recordExpense(
                $command->expenseId,
                $command->description,
                $command->amount,
                $command->category,
                $command->date,
                $command->notes
            )
            ->persist();
    }
}
```

### 4. Create Projections

```php
// app/Domain/Expenses/Projections/ExpenseProjector.php
class ExpenseProjector extends Projector
{
    public function onExpenseRecorded(ExpenseRecorded $event): void
    {
        Expense::create([
            'id' => $event->expenseId,
            'description' => $event->description,
            'amount' => $event->amount,
            'category' => $event->category,
            'date' => $event->date,
            'notes' => $event->notes,
        ]);
        
        // Update the monthly summary projection
        MonthlySummary::updateOrCreate(
            ['year_month' => substr($event->date, 0, 7)],
            ['total' => DB::raw('total + ' . $event->amount)]
        );
        
        // Update the category summary projection
        CategorySummary::updateOrCreate(
            ['category' => $event->category],
            ['total' => DB::raw('total + ' . $event->amount)]
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
            ->update(['total' => DB::raw('total - ' . $amount)]);
            
        CategorySummary::updateOrCreate(
            ['category' => $event->newCategory],
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
    private $queryBus;
    
    public function __construct(CommandBus $commandBus, QueryBus $queryBus)
    {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:50',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);
        
        $expenseId = (string) Str::uuid();
        
        $command = new RecordExpenseCommand(
            $expenseId,
            $validated['description'],
            $validated['amount'],
            $validated['category'],
            $validated['date'],
            $validated['notes'] ?? null
        );
        
        $this->commandBus->dispatch($command);
        
        return response()->json(['id' => $expenseId], 201);
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

## Troubleshooting

### Projections Out of Sync

If projections become out of sync with events:

1. Identify which projectors need to be rebuilt
2. Use the replay command to rebuild them:
   ```bash
   php artisan event-sourcing:replay --projector="App\Domain\Subscriptions\Projections\SubscriptionProjector"
   ```

### Event Store Growing Too Large

For large event stores:

1. Implement snapshots for frequently accessed aggregates
2. Consider archiving old events to cold storage
3. Use database partitioning for the event store table

### Handling Failed Events

If an event handler fails:

1. Configure exception handling in the event sourcing config
2. Implement a retry mechanism for failed events
3. Set up monitoring to alert on persistent failures 
