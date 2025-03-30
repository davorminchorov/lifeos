# LifeOS Performance Optimization Guide

This document outlines performance optimization strategies for the LifeOS application, with a special focus on event sourcing optimizations.

## Performance Monitoring Tools

Before optimizing, implement proper monitoring:

1. **Laravel Telescope** - Application-level monitoring
2. **Laravel Debugbar** - Development debugging
3. **Blackfire.io** - Profiling and bottleneck detection
4. **New Relic** - Production monitoring and alerting
5. **MySQL Slow Query Log** - Database query optimization

## Event Sourcing Performance Considerations

### 1. Event Store Optimization

The event store is the foundation of an event-sourced system and can become a performance bottleneck as it grows:

#### Database Indexing

Ensure the event store has optimized indexes:

```php
Schema::table('stored_events', function (Blueprint $table) {
    $table->index(['aggregate_uuid', 'aggregate_version']);
    $table->index('created_at');
    $table->index('event_class');
});
```

#### Event Store Partitioning

For large event stores, consider partitioning by date or aggregate type:

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
    PARTITION p_2023_03 VALUES LESS THAN (UNIX_TIMESTAMP('2023-04-01')),
    -- Add more partitions as needed
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

#### Event Data Compression

Consider compressing event data for storage efficiency:

```php
class CompressedEventStore implements StoredEventRepository
{
    private $decoratedRepository;
    
    public function __construct(StoredEventRepository $repository)
    {
        $this->decoratedRepository = $repository;
    }
    
    public function persist(StoredEvent $storedEvent): StoredEvent
    {
        // Compress event properties
        $compressedProperties = gzcompress(json_encode($storedEvent->event_properties));
        $storedEvent->event_properties = ['compressed' => base64_encode($compressedProperties)];
        
        return $this->decoratedRepository->persist($storedEvent);
    }
    
    public function retrieveAll(string $className, $aggregateUuid = null): array
    {
        $events = $this->decoratedRepository->retrieveAll($className, $aggregateUuid);
        
        foreach ($events as $event) {
            if (isset($event->event_properties['compressed'])) {
                $decompressed = gzuncompress(base64_decode($event->event_properties['compressed']));
                $event->event_properties = json_decode($decompressed, true);
            }
        }
        
        return $events;
    }
    
    // Implement other methods...
}
```

### 2. Aggregate Performance

Optimizing aggregate loading and processing:

#### Snapshots

Implement snapshots to reduce event replay time for frequently accessed aggregates:

```php
class SubscriptionAggregate extends AggregateRoot
{
    use SnapshotTrait;
    
    // State properties
    private string $id;
    private string $name;
    private float $amount;
    private string $status;
    
    protected function getSnapshotVersion(): int
    {
        return 1; // Increment when snapshot structure changes
    }
    
    protected function getSnapshotState(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'status' => $this->status,
        ];
    }
    
    protected function restoreFromSnapshotState(array $state): void
    {
        $this->id = $state['id'];
        $this->name = $state['name'] ?? '';
        $this->amount = $state['amount'] ?? 0.0;
        $this->status = $state['status'] ?? 'inactive';
    }
}
```

Configure snapshot frequency:

```php
// config/event-sourcing.php
return [
    // ...
    'snapshot_when_event_count_reaches' => 50,
    // ...
];
```

#### Event Selection Optimization

Only load events needed for the current operation:

```php
class OptimizedEventStore implements StoredEventRepository
{
    private $decoratedRepository;
    
    public function __construct(StoredEventRepository $repository)
    {
        $this->decoratedRepository = $repository;
    }
    
    public function retrieveAll(string $className, $aggregateUuid = null): array
    {
        // For certain event types, we can optimize by selecting only relevant events
        if ($className === 'App\\Domain\\Subscriptions\\Events\\SubscriptionEvent') {
            $relevantEvents = [
                'App\\Domain\\Subscriptions\\Events\\SubscriptionCreated',
                'App\\Domain\\Subscriptions\\Events\\SubscriptionCancelled',
                // Only include events that affect the status
            ];
            
            // Query for only the relevant events
            return $this->decoratedRepository->retrieveAll($relevantEvents, $aggregateUuid);
        }
        
        return $this->decoratedRepository->retrieveAll($className, $aggregateUuid);
    }
    
    // Implement other methods...
}
```

#### Lazy Loading Aggregates

Implement lazy loading for aggregates to defer event loading:

```php
class LazyAggregate
{
    private $aggregateId;
    private $aggregateClass;
    private $loadedAggregate = null;
    
    public function __construct(string $aggregateClass, string $aggregateId)
    {
        $this->aggregateClass = $aggregateClass;
        $this->aggregateId = $aggregateId;
    }
    
    public function __call($method, $arguments)
    {
        if ($this->loadedAggregate === null) {
            $this->loadedAggregate = $this->aggregateClass::retrieve($this->aggregateId);
        }
        
        return $this->loadedAggregate->$method(...$arguments);
    }
}
```

### 3. Projection Optimization

Projections can be a major performance bottleneck:

#### Asynchronous Projections

Process projections asynchronously using queues:

```php
class QueuedProjector implements Projector
{
    public function handleEvent(StoredEvent $storedEvent): void
    {
        // Instead of processing immediately, dispatch to queue
        ProcessProjection::dispatch($storedEvent)->onQueue('projections');
    }
}

class ProcessProjection implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private $storedEvent;
    
    public function __construct(StoredEvent $storedEvent)
    {
        $this->storedEvent = $storedEvent;
    }
    
    public function handle()
    {
        // Process the projection here
        $projector = app(RealProjector::class);
        $projector->handleEvent($this->storedEvent);
    }
}
```

#### Projection Caching

Cache frequently accessed projections:

```php
class CachedSubscriptionProjection
{
    private $cache;
    private $repository;
    
    public function __construct(Repository $cache, SubscriptionRepository $repository)
    {
        $this->cache = $cache;
        $this->repository = $repository;
    }
    
    public function getActiveSubscriptions(): Collection
    {
        return $this->cache->remember('active_subscriptions', now()->addMinutes(5), function () {
            return $this->repository->getActive();
        });
    }
    
    public function clearCache(): void
    {
        $this->cache->forget('active_subscriptions');
    }
}
```

#### Batch Processing

Process events in batches for better performance:

```php
class BatchProjector extends Projector
{
    private $pendingEvents = [];
    private $batchSize = 100;
    
    public function handleEvent(StoredEvent $storedEvent): void
    {
        $this->pendingEvents[] = $storedEvent;
        
        if (count($this->pendingEvents) >= $this->batchSize) {
            $this->processBatch();
        }
    }
    
    private function processBatch(): void
    {
        DB::transaction(function () {
            foreach ($this->pendingEvents as $event) {
                $this->processEvent($event);
            }
        });
        
        $this->pendingEvents = [];
    }
    
    private function processEvent(StoredEvent $event): void
    {
        // Process individual event
    }
    
    public function __destruct()
    {
        // Process any remaining events
        if (count($this->pendingEvents) > 0) {
            $this->processBatch();
        }
    }
}
```

#### Materialized Views

Use database materialized views for complex reports:

```sql
CREATE MATERIALIZED VIEW subscription_summary AS
SELECT
    billing_cycle,
    COUNT(*) as total_count,
    SUM(amount) as total_amount
FROM
    subscriptions
WHERE
    status = 'active'
GROUP BY
    billing_cycle;
```

Refresh periodically:

```php
// In scheduled tasks
$schedule->call(function () {
    DB::statement('REFRESH MATERIALIZED VIEW subscription_summary');
})->daily();
```

### 4. Query Optimization

Optimize read model queries:

#### Efficient Indexes

Ensure read models have proper indexes:

```php
Schema::table('subscriptions', function (Blueprint $table) {
    $table->index('status');
    $table->index('next_billing_date');
    $table->index(['status', 'next_billing_date']);
});
```

#### Query Caching

Cache expensive queries:

```php
public function getSubscriptionsDueNextWeek(): Collection
{
    $cacheKey = 'subscriptions_due_next_week';
    
    return Cache::remember($cacheKey, now()->addHours(1), function () {
        return Subscription::where('status', 'active')
            ->whereBetween('next_billing_date', [
                now(),
                now()->addDays(7)
            ])
            ->get();
    });
}

// Clear cache when relevant events occur
public function onSubscriptionUpdated(SubscriptionUpdated $event): void
{
    Cache::forget('subscriptions_due_next_week');
}
```

#### Eager Loading

Ensure relations are eager loaded:

```php
// Instead of this (N+1 problem)
$subscriptions = Subscription::where('status', 'active')->get();
foreach ($subscriptions as $subscription) {
    $category = $subscription->category; // Separate query for each subscription
}

// Do this
$subscriptions = Subscription::with('category')
    ->where('status', 'active')
    ->get();
```

### 5. API and Frontend Optimization

#### JSON Response Optimization

Optimize API responses:

```php
class SubscriptionResource extends JsonResource
{
    public function toArray($request)
    {
        // Only include necessary fields
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'status' => $this->status,
            // Conditionally include relations only when needed
            'payments' => $request->include_payments 
                ? PaymentResource::collection($this->whenLoaded('payments'))
                : null,
        ];
    }
}
```

#### Pagination

Implement pagination for all collection endpoints:

```php
// API controller
public function index(Request $request)
{
    $subscriptions = Subscription::query()
        ->when($request->status, fn($q) => $q->where('status', $request->status))
        ->paginate($request->per_page ?? 15);
    
    return SubscriptionResource::collection($subscriptions);
}
```

#### API Caching

Implement HTTP caching for API responses:

```php
// In SubscriptionController
public function show($id)
{
    $subscription = Subscription::findOrFail($id);
    
    $etag = md5($subscription->updated_at->timestamp);
    
    if (request()->header('If-None-Match') === $etag) {
        return response()->noContent(304);
    }
    
    return response()
        ->json(new SubscriptionResource($subscription))
        ->header('ETag', $etag)
        ->header('Cache-Control', 'private, max-age=60');
}
```

### 6. Real-time Updates Optimization

#### Selective Broadcasting

Only broadcast essential changes:

```php
class SubscriptionEventSubscriber
{
    public function onSubscriptionCreated(SubscriptionCreated $event)
    {
        // Always broadcast new subscriptions
        broadcast(new SubscriptionCreatedEvent($event->subscriptionId));
    }
    
    public function onSubscriptionAmountUpdated(SubscriptionAmountUpdated $event)
    {
        // Only broadcast significant changes
        if ($event->amountDifference > 5.0) {
            broadcast(new SubscriptionUpdatedEvent($event->subscriptionId));
        }
    }
}
```

#### Throttled Updates

Throttle frequent updates to prevent overloading clients:

```php
class ThrottledBroadcaster
{
    private $cache;
    private $debounceTime;
    
    public function __construct(Repository $cache, $debounceTime = 5)
    {
        $this->cache = $cache;
        $this->debounceTime = $debounceTime;
    }
    
    public function broadcastIfNotRecent(string $channel, $event): void
    {
        $cacheKey = "broadcast_{$channel}_" . get_class($event);
        
        if (!$this->cache->has($cacheKey)) {
            broadcast(new Channel($channel), $event);
            $this->cache->put($cacheKey, true, $this->debounceTime);
        }
    }
}
```

#### Message Batching

Batch WebSocket messages when possible:

```php
class BatchedBroadcaster
{
    private $pendingMessages = [];
    private $lastFlushTime;
    private $maxBatchTime = 1.0; // seconds
    
    public function __construct()
    {
        $this->lastFlushTime = microtime(true);
    }
    
    public function queueBroadcast(string $channel, $event): void
    {
        if (!isset($this->pendingMessages[$channel])) {
            $this->pendingMessages[$channel] = [];
        }
        
        $this->pendingMessages[$channel][] = $event;
        
        $currentTime = microtime(true);
        if ($currentTime - $this->lastFlushTime >= $this->maxBatchTime) {
            $this->flush();
        }
    }
    
    public function flush(): void
    {
        foreach ($this->pendingMessages as $channel => $events) {
            broadcast(new BatchedEventsEvent($channel, $events));
        }
        
        $this->pendingMessages = [];
        $this->lastFlushTime = microtime(true);
    }
    
    public function __destruct()
    {
        $this->flush();
    }
}
```

## Laravel-Specific Optimizations

### 1. Queue Configuration

Optimize queue processing:

```php
// config/queue.php
'redis' => [
    'driver' => 'redis',
    'connection' => 'default',
    'queue' => env('REDIS_QUEUE', 'default'),
    'retry_after' => 90,
    'block_for' => 5,
],
```

### 2. Cache Configuration

Implement strategic caching:

```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'redis'),

'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
    ],
    
    // For long-lived caches (e.g., configurations)
    'persistent' => [
        'driver' => 'redis',
        'connection' => 'persistent',
    ],
    
    // For high-volume, short-lived caches
    'volatile' => [
        'driver' => 'array',
    ],
],
```

### 3. Database Query Optimization

Use database query caching when beneficial:

```php
$results = DB::table('subscriptions')
    ->where('user_id', $userId)
    ->where('status', 'active')
    ->orderBy('next_billing_date')
    ->remember(5) // Cache for 5 minutes
    ->get();
```

### 4. Route Caching in Production

Cache routes for faster routing:

```bash
php artisan route:cache
```

## Frontend Performance Optimizations

### 1. Bundle Optimization

Optimize JavaScript bundles:

```js
// webpack.mix.js
mix.js('resources/js/app.js', 'public/js')
   .extract(['react', 'react-dom']) // Extract vendor libraries
   .version();
```

### 2. Lazy Loading Components

Implement code splitting for React components:

```jsx
import React, { lazy, Suspense } from 'react';

const SubscriptionForm = lazy(() => import('./SubscriptionForm'));

function App() {
  return (
    <div>
      <Suspense fallback={<div>Loading...</div>}>
        <SubscriptionForm />
      </Suspense>
    </div>
  );
}
```

### 3. API Request Batching

Batch API requests on the client side:

```jsx
// Instead of multiple fetch calls
const fetchSubscriptions = () => api.get('/subscriptions');
const fetchCategories = () => api.get('/categories');
const fetchPayments = () => api.get('/payments');

// Batch them in a single request
const fetchAllData = () => api.get('/batch', {
  requests: [
    { url: '/subscriptions' },
    { url: '/categories' },
    { url: '/payments' }
  ]
});
```

Server-side implementation:

```php
public function batchRequests(Request $request)
{
    $responses = [];
    
    foreach ($request->requests as $req) {
        $response = $this->performInternalRequest($req['url'], $req['method'] ?? 'GET');
        $responses[$req['url']] = $response;
    }
    
    return response()->json(['responses' => $responses]);
}
```

### 4. React Query Optimization

Optimize React Query for efficient API interaction:

```jsx
import { useQuery, useQueryClient } from 'react-query';

// Configure global settings
const queryClient = useQueryClient();
queryClient.setDefaultOptions({
  queries: {
    refetchOnWindowFocus: false,
    staleTime: 5 * 60 * 1000, // 5 minutes
    cacheTime: 10 * 60 * 1000, // 10 minutes
  },
});

// Use optimized queries
function SubscriptionList() {
  const { data, isLoading } = useQuery(
    ['subscriptions', { status: 'active' }], 
    () => api.getSubscriptions({ status: 'active' }),
    {
      keepPreviousData: true,
      placeholderData: previousData
    }
  );
  
  // Component implementation
}
```

## Monitoring Performance Improvements

### 1. Application Performance Metrics

Track key performance metrics:

```php
// Register middleware to track response times
class PerformanceMiddleware
{
    public function handle($request, Closure $next)
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $duration = microtime(true) - $startTime;
        Log::info("Request processed in {$duration}s", [
            'uri' => $request->getRequestUri(),
            'method' => $request->getMethod(),
            'duration' => $duration,
        ]);
        
        return $response;
    }
}
```

### 2. Regular Load Testing

Implement regular load testing to identify bottlenecks:

```bash
# Using k6 for load testing
k6 run --vus 50 --duration 30s load-tests/subscription-api.js
```

Example test script:

```js
// load-tests/subscription-api.js
import http from 'k6/http';
import { check, sleep } from 'k6';

export default function() {
  const res = http.get('https://api.example.com/subscriptions');
  check(res, { 'status was 200': (r) => r.status == 200 });
  sleep(1);
}
```

### 3. Performance Regression Testing

Include performance assertions in tests:

```php
/** @test */
public function it_retrieves_subscription_list_within_acceptable_time()
{
    // Arrange
    $this->createManySubscriptions(100);
    
    // Act
    $startTime = microtime(true);
    $response = $this->getJson('/api/v1/subscriptions');
    $duration = microtime(true) - $startTime;
    
    // Assert
    $response->assertStatus(200);
    $this->assertLessThan(
        0.5, // Maximum acceptable seconds
        $duration,
        "API response took too long: {$duration}s"
    );
}
```

## Performance Optimization Checklist

Before deploying to production, complete this performance checklist:

- [ ] Implement database indexing strategies
- [ ] Add caching for frequently accessed data
- [ ] Configure queue workers and dispatching
- [ ] Implement projection optimizations
- [ ] Set up snapshot storage for large aggregates
- [ ] Optimize API response format and size
- [ ] Configure frontend data fetching strategies
- [ ] Set up performance monitoring tools
- [ ] Run load tests and fix bottlenecks
- [ ] Optimize asset bundling and delivery
- [ ] Configure proper server-side caching headers 
