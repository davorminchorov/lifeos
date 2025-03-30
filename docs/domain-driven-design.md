# Domain-Driven Design Tactical Patterns in LifeOS

This document outlines the Domain-Driven Design (DDD) tactical patterns used throughout the LifeOS application. These patterns help us model complex domains effectively while ensuring our code remains maintainable, extensible, and aligned with business requirements.

## Table of Contents

- [Introduction](#introduction)
- [Core Domain Patterns](#core-domain-patterns)
  - [Aggregates](#aggregates)
  - [Entities](#entities)
  - [Value Objects](#value-objects)
- [Behavioral Patterns](#behavioral-patterns)
  - [Domain Events](#domain-events)
  - [Commands](#commands)
  - [Repositories](#repositories)
- [Structural Patterns](#structural-patterns)
  - [Domain Services](#domain-services)
  - [Application Services](#application-services)
  - [Factories](#factories)
- [Data Access Patterns](#data-access-patterns)
  - [Projections](#projections)
  - [Event Store](#event-store)
- [Implementation Guidelines](#implementation-guidelines)
- [Example Implementations](#example-implementations)

## Introduction

Domain-Driven Design (DDD) is particularly well-suited for the LifeOS application due to:

1. **Complex business domains** (subscriptions, bills, investments, expenses, job applications)
2. **Rich business logic** within each domain
3. **Event-sourced architecture** which naturally aligns with DDD concepts
4. **Vertical slice architecture** that organizes code by business capability

By applying DDD tactical patterns, we achieve:

- A shared language between developers and domain experts
- Clearer boundaries between different parts of the system
- More maintainable and testable code
- Better alignment with business requirements

## Core Domain Patterns

### Aggregates

Aggregates are consistency boundaries that encapsulate and protect business invariants. Each bounded context in LifeOS has its own aggregate root(s):

#### Primary Aggregates in LifeOS

| Aggregate | Responsibility | Example Invariants |
|-----------|----------------|-------------------|
| `SubscriptionAggregate` | Manages the lifecycle of subscriptions | Amount must be positive, billing cycle must be valid |
| `BillAggregate` | Handles utility bills and payment status | Payment amount must not exceed bill amount |
| `InvestmentAggregate` | Manages investment portfolios and transactions | Portfolio allocations must sum to 100% |
| `JobApplicationAggregate` | Tracks job applications and interview processes | Cannot schedule interviews for rejected applications |
| `ExpenseAggregate` | Records and categorizes expenses | Expense amount must be positive, category must be valid |

#### Implementation Guidelines

- Each aggregate has a unique identifier (UUID)
- Aggregates expose public methods that represent business operations
- Aggregates validate business rules before recording events
- Only aggregates emit domain events
- Keep aggregates small and focused
- Other entities can only be accessed through the aggregate root

```php
class SubscriptionAggregate extends AggregateRoot
{
    // State properties
    private string $id;
    private string $userId;
    private string $name;
    private Money $amount;
    private BillingCycle $billingCycle;
    private SubscriptionStatus $status;
    
    // Command method - handle business operation
    public function createSubscription(
        string $id,
        string $userId,
        string $name,
        Money $amount,
        BillingCycle $billingCycle
    ): self {
        // Business rule validation
        if ($amount->isNegative()) {
            throw new InvalidAmountException("Amount must be positive");
        }
        
        // Record the event
        $this->recordThat(new SubscriptionCreated(
            $id,
            $userId,
            $name,
            $amount,
            $billingCycle
        ));
        
        return $this;
    }
    
    // Apply method - reconstruct state
    protected function applySubscriptionCreated(SubscriptionCreated $event): void
    {
        $this->id = $event->subscriptionId;
        $this->userId = $event->userId;
        $this->name = $event->name;
        $this->amount = $event->amount;
        $this->billingCycle = $event->billingCycle;
        $this->status = SubscriptionStatus::ACTIVE;
    }
}
```

### Entities

Entities are domain objects with a distinct identity that persists through state changes.

#### Primary Entities in LifeOS

| Entity | Description | Key Attributes |
|--------|-------------|----------------|
| `Subscription` | Represents a recurring subscription | ID, name, amount, billing cycle, status |
| `Bill` | Represents a utility bill | ID, payee, amount, due date, status |
| `Investment` | Represents an investment vehicle | ID, name, type, value, purchase date |
| `JobApplication` | Represents a job application | ID, company, position, status, application date |
| `Expense` | Represents a single expense | ID, amount, category, date, description |

#### Implementation Guidelines

- Entities have unique identifiers
- Equality is determined by identity, not attributes
- Entities can change state over time
- Focus on behavior, not just data
- Protect invariants with proper encapsulation

```php
class Subscription implements Entity
{
    private string $id;
    private string $name;
    private Money $amount;
    private BillingCycle $billingCycle;
    private SubscriptionStatus $status;
    
    // Constructor and methods
    
    public function renew(): void
    {
        if ($this->status !== SubscriptionStatus::ACTIVE) {
            throw new InvalidStatusException("Only active subscriptions can be renewed");
        }
        
        $this->nextBillingDate = $this->calculateNextBillingDate();
    }
    
    // Getters and other methods
}
```

### Value Objects

Value objects are immutable objects that represent concepts in the domain and are defined by their attributes rather than an identity.

#### Primary Value Objects in LifeOS

| Value Object | Description | Examples |
|--------------|-------------|----------|
| `Money` | Represents a monetary amount with currency | $10.99 USD, €15.00 EUR |
| `BillingCycle` | Represents a recurring payment period | Monthly, Quarterly, Yearly |
| `DateRange` | Represents a period between two dates | Jan 1-31, 2023 |
| `Category` | Represents a classification | Entertainment, Utilities, Food |
| `PaymentStatus` | Represents the status of a payment | Paid, Pending, Overdue |
| `ApplicationStatus` | Represents a job application stage | Applied, Interviewing, Rejected, Accepted |

#### Implementation Guidelines

- Value objects are immutable
- Equality is based on attribute values, not identity
- No side effects in methods
- Rich behavior related to what they represent
- Self-validation in constructors

```php
final class Money
{
    private float $amount;
    private string $currency;
    
    public function __construct(float $amount, string $currency = 'USD')
    {
        // Validation
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException("Amount must be numeric");
        }
        
        $this->amount = $amount;
        $this->currency = $currency;
    }
    
    public function add(Money $money): Money
    {
        if ($this->currency !== $money->currency) {
            throw new InvalidCurrencyException("Cannot add money with different currencies");
        }
        
        return new Money($this->amount + $money->amount, $this->currency);
    }
    
    public function multiply(float $multiplier): Money
    {
        return new Money($this->amount * $multiplier, $this->currency);
    }
    
    public function isNegative(): bool
    {
        return $this->amount < 0;
    }
    
    // Other methods
}
```

## Behavioral Patterns

### Domain Events

Domain events represent significant occurrences within the domain and are the core of our event-sourced system.

#### Primary Domain Events in LifeOS

| Domain Event | Description | Key Data |
|--------------|-------------|----------|
| `SubscriptionCreated` | A new subscription was created | Subscription ID, User ID, Name, Amount, Billing Cycle |
| `BillPaid` | A bill was paid | Bill ID, Amount Paid, Payment Date, Payment Method |
| `InvestmentValuationUpdated` | The value of an investment changed | Investment ID, New Value, Previous Value, Valuation Date |
| `JobApplicationSubmitted` | A job application was submitted | Application ID, Company, Position, Submission Date |
| `ExpenseCategorized` | An expense was categorized | Expense ID, Category, Previous Category |

#### Event Versioning Strategy

Events evolve over time as requirements change. We use explicit versioning:

```php
// Original version (V1)
class SubscriptionCreatedV1 implements DomainEvent
{
    public function __construct(
        public string $subscriptionId,
        public string $name,
        public Money $amount,
        public BillingCycle $billingCycle
    ) {
    }
}

// Updated version (V2) with additional data
class SubscriptionCreatedV2 implements DomainEvent
{
    public function __construct(
        public string $subscriptionId,
        public string $userId, // New field
        public string $name,
        public Money $amount,
        public BillingCycle $billingCycle,
        public ?DateTime $startDate = null // New field
    ) {
    }
}
```

#### Implementation Guidelines

- Events are immutable data structures
- Events are named in past tense
- Store only relevant data, not the entire entity
- Include metadata (timestamp, correlation ID, user ID)
- Version events explicitly when schema changes
- Store events in the event store as the system of record

### Commands

Commands represent user intentions and are the entry point to our domain logic.

#### Primary Commands in LifeOS

| Command | Description | Handler Responsibility |
|---------|-------------|------------------------|
| `CreateSubscription` | Create a new subscription | Validate input, create subscription aggregate, persist events |
| `PayBill` | Record payment for a bill | Validate payment, update bill aggregate, persist events |
| `UpdateInvestmentValuation` | Update investment value | Validate input, update investment aggregate, persist events |
| `SubmitJobApplication` | Submit a new job application | Validate application, create application aggregate, persist events |
| `CategorizeExpense` | Assign category to expense | Validate category, update expense aggregate, persist events |

#### Implementation Guidelines

- Commands are simple DTOs with validation
- Commands are named with imperative verbs
- Each command has a single responsibility
- Commands are handled by command handlers
- Use command IDs for correlation with events

```php
class CreateSubscriptionCommand
{
    public function __construct(
        public string $subscriptionId,
        public string $userId,
        public string $name,
        public float $amount,
        public string $currency,
        public string $billingCycle,
        public ?string $startDate = null,
        public ?string $commandId = null
    ) {
        $this->commandId ??= (string) Str::uuid();
    }
    
    public function validate(): array
    {
        $errors = [];
        
        if (empty($this->name)) {
            $errors['name'] = 'Name is required';
        }
        
        if ($this->amount <= 0) {
            $errors['amount'] = 'Amount must be positive';
        }
        
        // More validation
        
        return $errors;
    }
}

class CreateSubscriptionHandler
{
    public function handle(CreateSubscriptionCommand $command): void
    {
        // Validate command
        $errors = $command->validate();
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
        
        // Create value objects
        $money = new Money($command->amount, $command->currency);
        $billingCycle = BillingCycle::fromString($command->billingCycle);
        
        // Execute business logic via aggregate
        SubscriptionAggregate::retrieve($command->subscriptionId)
            ->createSubscription(
                $command->subscriptionId,
                $command->userId,
                $command->name,
                $money,
                $billingCycle
            )
            ->persist();
    }
}
```

### Repositories

Repositories provide access to aggregates and abstracts the underlying storage mechanism.

#### Primary Repositories in LifeOS

| Repository | Responsibility |
|------------|----------------|
| `SubscriptionRepository` | Load and store subscription aggregates |
| `BillRepository` | Load and store bill aggregates |
| `InvestmentRepository` | Load and store investment aggregates |
| `JobApplicationRepository` | Load and store job application aggregates |
| `ExpenseRepository` | Load and store expense aggregates |

#### Implementation Guidelines

- Event-sourced repositories reconstitute aggregates from events
- Abstract persistent storage details from the domain
- Repository interfaces belong to the domain
- Implementations belong to the infrastructure
- Return fully hydrated aggregates
- Handle technical concerns like caching and optimistic concurrency

```php
// Domain layer
interface SubscriptionRepository
{
    public function getById(string $id): ?SubscriptionAggregate;
    public function save(SubscriptionAggregate $subscription): void;
}

// Infrastructure layer
class EventSourcedSubscriptionRepository implements SubscriptionRepository
{
    private EventStore $eventStore;
    
    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }
    
    public function getById(string $id): ?SubscriptionAggregate
    {
        $events = $this->eventStore->getEventsForAggregate($id);
        
        if (empty($events)) {
            return null;
        }
        
        return SubscriptionAggregate::reconstituteFromEvents($id, $events);
    }
    
    public function save(SubscriptionAggregate $subscription): void
    {
        $events = $subscription->getRecordedEvents();
        $this->eventStore->store($events);
        $subscription->clearRecordedEvents();
    }
}
```

## Structural Patterns

### Domain Services

Domain services encapsulate business logic that doesn't naturally belong to a single entity or aggregate.

#### Primary Domain Services in LifeOS

| Domain Service | Responsibility | Example Operations |
|----------------|----------------|-------------------|
| `BudgetAnalysisService` | Analyze expenses across categories | Calculate spending patterns, generate budget reports |
| `ReminderService` | Coordinate scheduling of reminders | Generate subscription renewal reminders, bill payment reminders |
| `PortfolioAnalysisService` | Calculate investment metrics | Calculate ROI, analyze asset allocation, generate performance reports |
| `SearchService` | Cross-domain search | Search across subscriptions, bills, expenses, and investments |

#### Implementation Guidelines

- Use domain services when logic involves multiple aggregates
- Keep domain services focused on business logic, not infrastructure
- Stateless operations preferred
- Inject repositories or other services as needed
- Place services in the domain layer

```php
class BudgetAnalysisService
{
    private ExpenseRepository $expenseRepository;
    private SubscriptionRepository $subscriptionRepository;
    
    public function __construct(
        ExpenseRepository $expenseRepository,
        SubscriptionRepository $subscriptionRepository
    ) {
        $this->expenseRepository = $expenseRepository;
        $this->subscriptionRepository = $subscriptionRepository;
    }
    
    public function analyzeMonthlySpendings(string $userId, int $month, int $year): MonthlyBudgetReport
    {
        $expenses = $this->expenseRepository->getByUserIdAndMonth($userId, $month, $year);
        $subscriptions = $this->subscriptionRepository->getActiveByUserId($userId);
        
        $spendingByCategory = $this->calculateSpendingByCategory($expenses, $subscriptions);
        $totalSpending = $this->calculateTotalSpending($spendingByCategory);
        
        return new MonthlyBudgetReport($userId, $month, $year, $spendingByCategory, $totalSpending);
    }
    
    // Private helper methods
}
```

### Application Services

Application services coordinate the overall application flow, connecting the UI with the domain layer.

#### Primary Application Services in LifeOS

| Application Service | Responsibility |
|---------------------|----------------|
| `CommandBus` | Route commands to their handlers |
| `QueryBus` | Route queries to their handlers |
| `EventBus` | Distribute events to their handlers |

#### Implementation Guidelines

- Keep application services thin
- Focus on orchestration, not business logic
- Handle technical concerns (transactions, security, logging)
- Use dependency injection for domain services and repositories
- Act as a facade to the domain layer

```php
class SubscriptionApplicationService
{
    private CommandBus $commandBus;
    private QueryBus $queryBus;
    
    public function __construct(CommandBus $commandBus, QueryBus $queryBus)
    {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }
    
    public function createSubscription(array $data): string
    {
        $subscriptionId = (string) Str::uuid();
        
        $command = new CreateSubscriptionCommand(
            $subscriptionId,
            $data['user_id'],
            $data['name'],
            $data['amount'],
            $data['currency'] ?? 'USD',
            $data['billing_cycle'],
            $data['start_date'] ?? null
        );
        
        $this->commandBus->dispatch($command);
        
        return $subscriptionId;
    }
    
    public function getActiveSubscriptions(string $userId): array
    {
        $query = new GetActiveSubscriptionsQuery($userId);
        return $this->queryBus->dispatch($query);
    }
    
    // Other methods
}
```

### Factories

Factories create complex domain objects, encapsulating the creation logic.

#### Primary Factories in LifeOS

| Factory | Responsibility |
|---------|----------------|
| `AggregateFactory` | Create new aggregate instances |
| `EventFactory` | Create domain events with proper metadata |
| `ValueObjectFactory` | Create value objects from primitive types |

#### Implementation Guidelines

- Use factories when object creation is complex
- Place creation logic in factories instead of constructors
- Create fully valid objects
- Make factories part of the domain when they contain domain logic
- Use static methods for simple factory operations

```php
class MoneyFactory
{
    public static function fromString(string $amount, string $currency = 'USD'): Money
    {
        // Handle currency symbols
        $cleanAmount = preg_replace('/[^0-9\.]/', '', $amount);
        
        // Convert to float
        $floatAmount = (float) $cleanAmount;
        
        return new Money($floatAmount, $currency);
    }
    
    public static function zero(string $currency = 'USD'): Money
    {
        return new Money(0, $currency);
    }
}

// Usage
$price = MoneyFactory::fromString('$10.99');
$initialBalance = MoneyFactory::zero('EUR');
```

## Data Access Patterns

### Projections

Projections transform event streams into optimized read models for querying.

#### Primary Projections in LifeOS

| Projection | Purpose | Event Sources |
|------------|---------|---------------|
| `ActiveSubscriptionsProjection` | List of active subscriptions | SubscriptionCreated, SubscriptionCancelled, SubscriptionRenewed |
| `MonthlyExpenseSummaryProjection` | Monthly expense summary by category | ExpenseRecorded, ExpenseCategorized |
| `UpcomingBillsProjection` | Bills due in the near future | BillCreated, BillPaid, BillScheduled |
| `InvestmentPerformanceProjection` | Investment performance metrics | InvestmentCreated, InvestmentValuationUpdated |
| `JobApplicationStatusProjection` | Current status of job applications | JobApplicationSubmitted, ApplicationStatusChanged |

#### Implementation Guidelines

- Each projection has a single responsibility
- Projectors handle specific events to update projections
- Design projections for specific query needs
- Make projections eventually consistent
- Implement idempotency in projectors
- Consider read model denormalization for performance

```php
class ActiveSubscriptionsProjector extends Projector
{
    public function onSubscriptionCreated(SubscriptionCreated $event): void
    {
        DB::table('subscription_read_model')->insert([
            'id' => $event->subscriptionId,
            'user_id' => $event->userId,
            'name' => $event->name,
            'amount' => $event->amount->getAmount(),
            'currency' => $event->amount->getCurrency(),
            'billing_cycle' => $event->billingCycle->toString(),
            'status' => 'active',
            'created_at' => now(),
        ]);
    }
    
    public function onSubscriptionCancelled(SubscriptionCancelled $event): void
    {
        DB::table('subscription_read_model')
            ->where('id', $event->subscriptionId)
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => $event->cancelledAt,
                'updated_at' => now(),
            ]);
    }
    
    // Other event handlers
}
```

### Event Store

The event store is the system of record for all domain events.

#### Implementation Guidelines

- Store events with metadata (timestamp, aggregate ID, version)
- Implement optimistic concurrency control
- Design for efficient retrieval by aggregate ID
- Consider event partitioning for large event stores
- Add indexing for commonly queried fields
- Implement snapshotting for performance optimization

```php
interface EventStore
{
    public function append(string $aggregateId, array $events, int $expectedVersion = null): void;
    public function getEvents(string $aggregateId): array;
    public function getAllEvents(): array;
}

class MySqlEventStore implements EventStore
{
    private PDO $connection;
    
    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }
    
    public function append(string $aggregateId, array $events, int $expectedVersion = null): void
    {
        // Start transaction
        $this->connection->beginTransaction();
        
        try {
            // Check version if optimistic concurrency control is needed
            if ($expectedVersion !== null) {
                $currentVersion = $this->getCurrentVersion($aggregateId);
                if ($currentVersion !== $expectedVersion) {
                    throw new ConcurrencyException("Expected version {$expectedVersion}, but got {$currentVersion}");
                }
            }
            
            // Store events
            foreach ($events as $event) {
                $this->storeEvent($aggregateId, $event);
            }
            
            // Commit transaction
            $this->connection->commit();
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->connection->rollBack();
            throw $e;
        }
    }
    
    // Other methods
}
```

## Implementation Guidelines

### Integration with Event Sourcing

1. **Aggregates as Event Emitters**
   - Aggregates record domain events representing state changes
   - Events include all data necessary to reconstruct aggregate state
   - Apply events to update aggregate state

2. **Event-Driven Projections**
   - Projectors listen to domain events
   - Projections are rebuilt from event streams
   - Use blue/green deployment for zero-downtime rebuilds

3. **Commands for State Changes**
   - All state changes go through commands
   - Commands are validated before processing
   - Command handlers coordinate with aggregates

### Steps for Implementing a New Feature

1. **Define the Ubiquitous Language**
   - Collaborate with domain experts to define key terms
   - Document terms in a glossary
   - Use consistent naming in code and documentation

2. **Identify Aggregate Boundaries**
   - Determine which entities should be grouped together
   - Define invariants that need to be protected
   - Design aggregates to be as small as possible

3. **Design Value Objects**
   - Identify concepts that are defined by their attributes
   - Make value objects immutable
   - Implement rich behavior in value objects

4. **Define Domain Events**
   - Name events using past tense verbs
   - Include only relevant data in events
   - Design for versioning from the start

5. **Create Commands and Handlers**
   - Name commands using imperative verbs
   - Implement validation in commands
   - Keep command handlers focused

6. **Build Projections**
   - Design projections for specific query needs
   - Implement projectors for event handling
   - Document rebuilding strategies

## Example Implementations

### Subscription Domain Example

Here's a complete example of the Subscription domain implementation using DDD tactical patterns:

#### Aggregate and Events

```php
// Subscription aggregate
class SubscriptionAggregate extends AggregateRoot
{
    private string $id;
    private string $userId;
    private string $name;
    private Money $amount;
    private BillingCycle $billingCycle;
    private SubscriptionStatus $status;
    private ?DateTime $nextBillingDate = null;
    
    public function createSubscription(
        string $id,
        string $userId,
        string $name,
        Money $amount,
        BillingCycle $billingCycle,
        ?DateTime $startDate = null
    ): self {
        // Business rule validation
        if ($amount->isNegative()) {
            throw new InvalidAmountException("Amount must be positive");
        }
        
        $nextBillingDate = $this->calculateNextBillingDate($startDate ?? new DateTime(), $billingCycle);
        
        // Record the event
        $this->recordThat(new SubscriptionCreated(
            $id,
            $userId,
            $name,
            $amount,
            $billingCycle,
            $startDate,
            $nextBillingDate
        ));
        
        return $this;
    }
    
    public function cancelSubscription(string $reason = null): self
    {
        if ($this->status === SubscriptionStatus::CANCELLED) {
            throw new InvalidStatusException("Subscription is already cancelled");
        }
        
        $this->recordThat(new SubscriptionCancelled(
            $this->id,
            new DateTime(),
            $reason
        ));
        
        return $this;
    }
    
    protected function applySubscriptionCreated(SubscriptionCreated $event): void
    {
        $this->id = $event->subscriptionId;
        $this->userId = $event->userId;
        $this->name = $event->name;
        $this->amount = $event->amount;
        $this->billingCycle = $event->billingCycle;
        $this->status = SubscriptionStatus::ACTIVE;
        $this->nextBillingDate = $event->nextBillingDate;
    }
    
    protected function applySubscriptionCancelled(SubscriptionCancelled $event): void
    {
        $this->status = SubscriptionStatus::CANCELLED;
    }
    
    private function calculateNextBillingDate(DateTime $startDate, BillingCycle $billingCycle): DateTime
    {
        $nextDate = clone $startDate;
        
        switch ($billingCycle->getValue()) {
            case BillingCycle::MONTHLY:
                $nextDate->modify('+1 month');
                break;
            case BillingCycle::QUARTERLY:
                $nextDate->modify('+3 months');
                break;
            case BillingCycle::YEARLY:
                $nextDate->modify('+1 year');
                break;
        }
        
        return $nextDate;
    }
}
```

#### Value Objects

```php
// Money value object
final class Money
{
    private float $amount;
    private string $currency;
    
    public function __construct(float $amount, string $currency = 'USD')
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }
    
    public function getAmount(): float
    {
        return $this->amount;
    }
    
    public function getCurrency(): string
    {
        return $this->currency;
    }
    
    public function add(Money $money): Money
    {
        if ($this->currency !== $money->currency) {
            throw new InvalidCurrencyException("Cannot add money with different currencies");
        }
        
        return new Money($this->amount + $money->amount, $this->currency);
    }
    
    public function multiply(float $multiplier): Money
    {
        return new Money($this->amount * $multiplier, $this->currency);
    }
    
    public function isNegative(): bool
    {
        return $this->amount < 0;
    }
    
    public function equals(Money $money): bool
    {
        return $this->amount === $money->amount && $this->currency === $money->currency;
    }
    
    public function __toString(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }
}

// BillingCycle value object
final class BillingCycle
{
    public const MONTHLY = 'monthly';
    public const QUARTERLY = 'quarterly';
    public const YEARLY = 'yearly';
    
    private string $value;
    
    private function __construct(string $value)
    {
        if (!in_array($value, [self::MONTHLY, self::QUARTERLY, self::YEARLY])) {
            throw new InvalidArgumentException("Invalid billing cycle: {$value}");
        }
        
        $this->value = $value;
    }
    
    public static function monthly(): self
    {
        return new self(self::MONTHLY);
    }
    
    public static function quarterly(): self
    {
        return new self(self::QUARTERLY);
    }
    
    public static function yearly(): self
    {
        return new self(self::YEARLY);
    }
    
    public static function fromString(string $value): self
    {
        return new self(strtolower($value));
    }
    
    public function getValue(): string
    {
        return $this->value;
    }
    
    public function toString(): string
    {
        return $this->value;
    }
    
    public function __toString(): string
    {
        return $this->value;
    }
}

// SubscriptionStatus value object
final class SubscriptionStatus
{
    public const ACTIVE = 'active';
    public const PAUSED = 'paused';
    public const CANCELLED = 'cancelled';
    
    private string $value;
    
    private function __construct(string $value)
    {
        if (!in_array($value, [self::ACTIVE, self::PAUSED, self::CANCELLED])) {
            throw new InvalidArgumentException("Invalid subscription status: {$value}");
        }
        
        $this->value = $value;
    }
    
    public static function active(): self
    {
        return new self(self::ACTIVE);
    }
    
    public static function paused(): self
    {
        return new self(self::PAUSED);
    }
    
    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }
    
    public static function fromString(string $value): self
    {
        return new self(strtolower($value));
    }
    
    public function getValue(): string
    {
        return $this->value;
    }
    
    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }
    
    public function isCancelled(): bool
    {
        return $this->value === self::CANCELLED;
    }
    
    public function __toString(): string
    {
        return $this->value;
    }
}
```

This documentation provides a comprehensive guide to implementing Domain-Driven Design tactical patterns in the LifeOS project. By following these patterns consistently, we ensure a maintainable, extensible, and business-aligned codebase that effectively handles the complexities of personal finance management. 
