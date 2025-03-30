# LifeOS Testing Strategy

This document outlines the comprehensive testing approach for the LifeOS application, with special focus on testing event-sourced systems.

## Testing Pyramid

LifeOS follows a modified testing pyramid approach:

1. **Unit Tests** (Base) - Fast, focused tests of individual components
2. **Domain Tests** - Tests for aggregates and domain logic
3. **Projection Tests** - Tests for read model projections
4. **API Tests** - Tests for HTTP endpoints
5. **End-to-End Tests** (Peak) - Complete workflow tests

## Testing Domains in Event Sourcing

### 1. Unit Testing Aggregates

Aggregates are the core of our domain logic and should have thorough test coverage:

```php
class SubscriptionAggregateTest extends TestCase
{
    /** @test */
    public function it_creates_a_subscription()
    {
        // Arrange
        $id = Str::uuid()->toString();
        
        // Act
        $aggregate = SubscriptionAggregate::retrieve($id)
            ->createSubscription(
                $id,
                'Netflix',
                14.99,
                'monthly',
                '2023-01-01'
            );
        
        // Assert
        $recordedEvents = $aggregate->getRecordedEvents();
        
        $this->assertCount(1, $recordedEvents);
        $this->assertInstanceOf(SubscriptionCreated::class, $recordedEvents[0]);
        $this->assertEquals('Netflix', $recordedEvents[0]->name);
        $this->assertEquals(14.99, $recordedEvents[0]->amount);
    }
    
    /** @test */
    public function it_enforces_business_rules()
    {
        // Arrange
        $id = Str::uuid()->toString();
        
        // Assert
        $this->expectException(InvalidAmountException::class);
        
        // Act - should throw exception for negative amount
        SubscriptionAggregate::retrieve($id)
            ->createSubscription(
                $id,
                'Netflix',
                -14.99, // Invalid amount
                'monthly',
                '2023-01-01'
            );
    }

    /** @test */
    public function it_applies_events_correctly()
    {
        // Arrange
        $id = Str::uuid()->toString();
        
        // Act
        $aggregate = SubscriptionAggregate::retrieve($id)
            ->createSubscription(
                $id,
                'Netflix',
                14.99,
                'monthly',
                '2023-01-01'
            )
            ->cancelSubscription('Too expensive');
            
        // Assert - using reflection to check private state
        $status = (new ReflectionClass($aggregate))->getProperty('status');
        $status->setAccessible(true);
        
        $this->assertEquals('cancelled', $status->getValue($aggregate));
    }
}
```

### 2. Testing Event Handlers/Projectors

Projectors transform events into read models and require specialized testing:

```php
class SubscriptionProjectorTest extends TestCase
{
    use RefreshDatabase;
    
    private SubscriptionProjector $projector;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->projector = new SubscriptionProjector();
    }
    
    /** @test */
    public function it_creates_subscription_record_when_subscription_created()
    {
        // Arrange
        $event = new SubscriptionCreated(
            'sub-123',
            'Netflix',
            14.99,
            'monthly',
            '2023-01-01'
        );
        
        // Act
        $this->projector->onSubscriptionCreated($event);
        
        // Assert
        $this->assertDatabaseHas('subscriptions', [
            'id' => 'sub-123',
            'name' => 'Netflix',
            'amount' => 14.99,
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);
    }
    
    /** @test */
    public function it_updates_subscription_status_when_subscription_cancelled()
    {
        // Arrange
        $this->projector->onSubscriptionCreated(
            new SubscriptionCreated(
                'sub-123',
                'Netflix',
                14.99,
                'monthly',
                '2023-01-01'
            )
        );
        
        $event = new SubscriptionCancelled(
            'sub-123',
            'Too expensive',
            now()->toIso8601String()
        );
        
        // Act
        $this->projector->onSubscriptionCancelled($event);
        
        // Assert
        $this->assertDatabaseHas('subscriptions', [
            'id' => 'sub-123',
            'status' => 'cancelled',
        ]);
    }
    
    /** @test */
    public function it_calculates_next_billing_date_correctly()
    {
        // Arrange
        $event = new SubscriptionCreated(
            'sub-123',
            'Netflix',
            14.99,
            'monthly',
            '2023-01-01'
        );
        
        // Act
        $this->projector->onSubscriptionCreated($event);
        
        // Assert
        $subscription = Subscription::find('sub-123');
        $this->assertEquals('2023-02-01', $subscription->next_billing_date->format('Y-m-d'));
    }
}
```

### 3. Testing Command Handlers

Command handlers orchestrate operations between aggregates and repositories:

```php
class CreateSubscriptionHandlerTest extends TestCase
{
    /** @test */
    public function it_handles_create_subscription_command()
    {
        // Arrange
        $commandHandler = new CreateSubscriptionHandler();
        
        $command = new CreateSubscriptionCommand(
            'sub-123',
            'Netflix',
            14.99,
            'monthly',
            '2023-01-01'
        );
        
        // Mock the aggregate to verify interactions
        $aggregate = Mockery::mock('overload:' . SubscriptionAggregate::class);
        $aggregate->shouldReceive('retrieve')
            ->with('sub-123')
            ->once()
            ->andReturnSelf();
            
        $aggregate->shouldReceive('createSubscription')
            ->with('sub-123', 'Netflix', 14.99, 'monthly', '2023-01-01')
            ->once()
            ->andReturnSelf();
            
        $aggregate->shouldReceive('persist')
            ->once();
        
        // Act
        $commandHandler->handle($command);
        
        // Assert - verification happens via Mockery expectations
    }
}
```

### 4. Testing API Endpoints

API tests verify the HTTP interface works correctly:

```php
class SubscriptionApiTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_creates_a_subscription()
    {
        // Act
        $response = $this->postJson('/api/v1/subscriptions', [
            'name' => 'Netflix',
            'amount' => 14.99,
            'billing_cycle' => 'monthly',
            'start_date' => '2023-01-01',
        ]);
        
        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('subscriptions', [
            'name' => 'Netflix',
            'amount' => 14.99,
            'billing_cycle' => 'monthly',
        ]);
    }
    
    /** @test */
    public function it_validates_subscription_input()
    {
        // Act
        $response = $this->postJson('/api/v1/subscriptions', [
            'name' => 'Netflix',
            'amount' => -14.99, // Invalid amount
            'billing_cycle' => 'monthly',
        ]);
        
        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount', 'start_date']);
    }
    
    /** @test */
    public function it_cancels_a_subscription()
    {
        // Arrange
        $eventStore = app(Spatie\EventSourcing\StoredEvents\Repositories\StoredEventRepository::class);
        
        $subscriptionId = Str::uuid()->toString();
        
        $event = new SubscriptionCreated(
            $subscriptionId,
            'Netflix',
            14.99,
            'monthly',
            '2023-01-01'
        );
        
        $eventStore->persist(
            (new StoredEventFactory())->createFromEvent($event)
        );
        
        // Wait for projector to process
        Event::dispatch(new SubscriptionCreated(
            $subscriptionId,
            'Netflix',
            14.99,
            'monthly',
            '2023-01-01'
        ));
        
        // Act
        $response = $this->postJson("/api/v1/subscriptions/{$subscriptionId}/cancel", [
            'reason' => 'Too expensive',
        ]);
        
        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscriptionId,
            'status' => 'cancelled',
        ]);
    }
}
```

## Testing Strategies Specific to Event Sourcing

### 1. Event Store Testing

```php
class EventStoreTest extends TestCase
{
    /** @test */
    public function it_stores_and_retrieves_events()
    {
        // Arrange
        $eventStore = app(StoredEventRepository::class);
        $subscriptionId = Str::uuid()->toString();
        $event = new SubscriptionCreated(
            $subscriptionId,
            'Netflix',
            14.99,
            'monthly',
            '2023-01-01'
        );
        
        // Act
        $storedEvent = (new StoredEventFactory())->createFromEvent($event);
        $eventStore->persist($storedEvent);
        
        // Retrieve events for this aggregate
        $retrievedEvents = $eventStore->retrieveAll(
            SubscriptionCreated::class,
            $subscriptionId
        );
        
        // Assert
        $this->assertCount(1, $retrievedEvents);
        $this->assertEquals('Netflix', $retrievedEvents[0]->event->name);
    }
}
```

### 2. Event Replay Testing

```php
class EventReplayTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_rebuilds_projections_on_replay()
    {
        // Arrange
        $eventStore = app(StoredEventRepository::class);
        $subscriptionId = Str::uuid()->toString();
        
        // Store events directly in event store
        $eventStore->persist(
            (new StoredEventFactory())->createFromEvent(
                new SubscriptionCreated(
                    $subscriptionId,
                    'Netflix',
                    14.99,
                    'monthly',
                    '2023-01-01'
                )
            )
        );
        
        // Clean projection tables to simulate out-of-sync state
        DB::table('subscriptions')->truncate();
        
        // Act - replay events
        $projector = app(SubscriptionProjector::class);
        app(Projectionist::class)
            ->replay(collect([$projector]));
        
        // Assert
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscriptionId,
            'name' => 'Netflix',
        ]);
    }
}
```

### 3. Snapshot Testing

```php
class SnapshotTest extends TestCase
{
    /** @test */
    public function it_can_restore_from_snapshot()
    {
        // Arrange
        $subscriptionId = Str::uuid()->toString();
        $aggregate = SubscriptionAggregate::retrieve($subscriptionId);
        
        // Create several events
        $aggregate->createSubscription($subscriptionId, 'Netflix', 14.99, 'monthly', '2023-01-01');
        $aggregate->updateSubscriptionName('Netflix Premium');
        $aggregate->updateSubscriptionAmount(19.99);
        
        // Create a snapshot
        $aggregate->snapshot();
        
        // Act - retrieve from the snapshot
        $restoredAggregate = SubscriptionAggregate::retrieve($subscriptionId);
        
        // Assert using reflection to check internal state
        $nameProperty = (new ReflectionClass($restoredAggregate))->getProperty('name');
        $nameProperty->setAccessible(true);
        
        $this->assertEquals('Netflix Premium', $nameProperty->getValue($restoredAggregate));
    }
}
```

## Test Doubles for Event Sourcing

### 1. Fake Event Store

```php
class FakeStoredEventRepository implements StoredEventRepository
{
    private array $events = [];
    
    public function persist(StoredEvent $storedEvent): StoredEvent
    {
        $this->events[] = $storedEvent;
        return $storedEvent;
    }
    
    public function retrieveAll(string $className, $aggregateUuid = null): array
    {
        return array_filter($this->events, function (StoredEvent $event) use ($className, $aggregateUuid) {
            return $event->event_class === $className && 
                   ($aggregateUuid === null || $event->aggregate_uuid === $aggregateUuid);
        });
    }
    
    // Implement other methods...
}
```

### 2. Testing with an In-Memory Event Bus

```php
class InMemoryEventBus implements EventBus
{
    private array $dispatchedEvents = [];
    
    public function dispatch(object $event): void
    {
        $this->dispatchedEvents[] = $event;
    }
    
    public function getDispatchedEvents(): array
    {
        return $this->dispatchedEvents;
    }
    
    public function hasDispatched(string $eventClass): bool
    {
        foreach ($this->dispatchedEvents as $event) {
            if ($event instanceof $eventClass) {
                return true;
            }
        }
        
        return false;
    }
}
```

## Automated Testing Setup

### 1. CI Pipeline Configuration

```yaml
# .github/workflows/tests.yml
name: Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: lifeos_test
          MYSQL_USER: lifeos
          MYSQL_PASSWORD: password
          MYSQL_ROOT_PASSWORD: password
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: mbstring, dom, fileinfo, mysql
        tools: composer:v2
        coverage: xdebug
    
    - name: Copy .env
      run: cp .env.example .env.testing
    
    - name: Install Dependencies
      run: composer install -q --no-ansi --no-interaction --no-scripts --no-progress
    
    - name: Generate key
      run: php artisan key:generate --env=testing
    
    - name: Directory Permissions
      run: chmod -R 777 storage bootstrap/cache
    
    - name: Run Unit and Feature Tests
      run: php artisan test --env=testing
    
    - name: Run Domain Tests
      run: php artisan test --testsuite=Domain --env=testing
    
    - name: Run Projection Tests
      run: php artisan test --testsuite=Projections --env=testing
```

### 2. Test Suite Configuration

```php
// phpunit.xml
<testsuites>
    <testsuite name="Unit">
        <directory>tests/Unit</directory>
    </testsuite>
    <testsuite name="Feature">
        <directory>tests/Feature</directory>
    </testsuite>
    <testsuite name="Domain">
        <directory>tests/Domain</directory>
    </testsuite>
    <testsuite name="Projections">
        <directory>tests/Projections</directory>
    </testsuite>
    <testsuite name="Integration">
        <directory>tests/Integration</directory>
    </testsuite>
</testsuites>
```

## Test Data Management

### 1. Test Event Factory

```php
class TestEventFactory
{
    public static function subscriptionCreated(array $attributes = []): SubscriptionCreated
    {
        return new SubscriptionCreated(
            $attributes['id'] ?? Str::uuid()->toString(),
            $attributes['name'] ?? 'Test Subscription',
            $attributes['amount'] ?? 9.99,
            $attributes['billingCycle'] ?? 'monthly',
            $attributes['startDate'] ?? now()->format('Y-m-d')
        );
    }
    
    public static function subscriptionCancelled(string $id, array $attributes = []): SubscriptionCancelled
    {
        return new SubscriptionCancelled(
            $id,
            $attributes['reason'] ?? 'Test cancellation',
            $attributes['cancelledAt'] ?? now()->toIso8601String()
        );
    }
    
    // Add more factory methods for other events
}
```

### 2. Aggregate Test Helper

```php
trait AggregateTestHelper
{
    protected function assertEventRecorded($aggregate, string $eventClass, array $expectedProperties = []): void
    {
        $recordedEvents = $aggregate->getRecordedEvents();
        
        $found = false;
        
        foreach ($recordedEvents as $event) {
            if ($event instanceof $eventClass) {
                $found = true;
                
                foreach ($expectedProperties as $property => $value) {
                    $this->assertEquals(
                        $value, 
                        $event->$property, 
                        "Property '{$property}' does not match expected value."
                    );
                }
                
                break;
            }
        }
        
        $this->assertTrue($found, "Event of class {$eventClass} was not recorded.");
    }
}
```

## Performance Testing

### 1. Event Store Performance

```php
class EventStorePerformanceTest extends TestCase
{
    /** @test */
    public function it_measures_event_store_write_performance()
    {
        // Arrange
        $eventStore = app(StoredEventRepository::class);
        $events = [];
        
        for ($i = 0; $i < 1000; $i++) {
            $events[] = new SubscriptionCreated(
                Str::uuid()->toString(),
                "Subscription {$i}",
                9.99,
                'monthly',
                now()->format('Y-m-d')
            );
        }
        
        // Act
        $startTime = microtime(true);
        
        foreach ($events as $event) {
            $eventStore->persist(
                (new StoredEventFactory())->createFromEvent($event)
            );
        }
        
        $duration = microtime(true) - $startTime;
        
        // Assert
        $this->assertLessThan(
            5.0, // Maximum allowable seconds
            $duration,
            "Event store write performance exceeds threshold (took {$duration}s for 1000 events)"
        );
    }
}
```

### 2. Projection Rebuild Performance

```php
class ProjectionRebuildPerformanceTest extends TestCase
{
    /** @test */
    public function it_measures_projection_rebuild_performance()
    {
        // Arrange - seed event store with 1000 events
        $this->seedEventStore(1000);
        
        // Act
        $startTime = microtime(true);
        
        $projector = app(SubscriptionProjector::class);
        app(Projectionist::class)
            ->replay(collect([$projector]));
            
        $duration = microtime(true) - $startTime;
        
        // Assert
        $this->assertLessThan(
            10.0, // Maximum allowable seconds
            $duration,
            "Projection rebuild performance exceeds threshold (took {$duration}s for 1000 events)"
        );
    }
    
    private function seedEventStore(int $count): void
    {
        // Implementation to seed event store
    }
}
```

## Testing Best Practices

1. **Test Business Rules First**: Focus on domain logic in aggregates
2. **Use Real Events**: Avoid mocking events, use real event objects
3. **Separate Test Databases**: Use separate databases for testing and development
4. **Test Event Versioning**: Ensure backwards compatibility with event versioning
5. **Idempotency Testing**: Verify projectors handle duplicate events correctly
6. **Clean Event Store Between Tests**: Isolate tests by cleaning the event store
7. **Test Complete Flows**: Create end-to-end tests for critical business processes
8. **Performance Benchmarks**: Establish baseline performance metrics 
9. **Regular Projection Rebuild Testing**: Verify projections can be rebuilt correctly
10. **Comprehensive Command Testing**: Verify command validation and handling

## Test Monitoring and Reporting

1. Configure test reports with coverage metrics
2. Set up continuous monitoring of test performance
3. Analyze test failures for patterns
4. Document flaky tests and create action plans
5. Review tests for redundancy and gaps 
