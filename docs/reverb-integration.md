# Integrating Laravel Reverb with Event Sourcing

This document outlines the strategy for integrating Laravel Reverb with the event sourcing system in LifeOS to provide real-time updates to clients.

## What is Laravel Reverb?

Laravel Reverb is a WebSocket server for Laravel applications that enables real-time, bidirectional communication between the server and clients. Unlike traditional polling methods, WebSockets maintain a persistent connection, allowing servers to push data to clients instantly.

Key advantages of Reverb:
- Native Laravel integration
- Simple authentication with Laravel's auth system
- Scalable hosting options
- Support for channels and private channels
- Low latency real-time updates

## Integration Strategy

In an event-sourced system, domain events are the perfect trigger for real-time updates. Our integration strategy follows these principles:

1. Domain events remain focused on business logic (no UI concerns)
2. Event handlers translate domain events to broadcast events 
3. Broadcast events contain only what clients need to know
4. Security is enforced through channel authorization

## Implementation Steps

### 1. Install and Configure Laravel Reverb

First, install the Reverb package:

```bash
composer require laravel/reverb
```

Publish the configuration:

```bash
php artisan vendor:publish --provider="Laravel\Reverb\ReverbServiceProvider"
```

Configure Reverb in your `.env` file:

```
REVERB_APP_ID=lifeos
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret

# For local development
REVERB_HOST=127.0.0.1
REVERB_PORT=8080

# For production
# REVERB_HOST=reverb.yourdomain.com
```

### 2. Create Broadcast Events

Create broadcast events corresponding to domain events:

```php
// app/Events/Subscriptions/SubscriptionCreatedEvent.php
class SubscriptionCreatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $id,
        public string $name,
        public float $amount,
        public string $billingCycle,
        public ?string $commandId = null // For correlation with the original command
    ) {
    }

    public function broadcastOn(): array
    {
        // Find the user associated with this subscription
        $subscription = Subscription::findOrFail($this->id);
        
        return [
            new PrivateChannel("user.{$subscription->user_id}")
        ];
    }
}
```

### 3. Create Domain Event Subscribers

Create event subscribers to translate domain events to broadcast events:

```php
// app/Subscribers/SubscriptionEventSubscriber.php
class SubscriptionEventSubscriber
{
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            SubscriptionCreated::class,
            [self::class, 'handleSubscriptionCreated']
        );
        
        $events->listen(
            SubscriptionCancelled::class,
            [self::class, 'handleSubscriptionCancelled']
        );
        
        $events->listen(
            SubscriptionUpdated::class,
            [self::class, 'handleSubscriptionUpdated']
        );
    }
    
    public function handleSubscriptionCreated(SubscriptionCreated $event): void
    {
        broadcast(new SubscriptionCreatedEvent(
            $event->subscriptionId,
            $event->name,
            $event->amount,
            $event->billingCycle,
            $event->metadata['commandId'] ?? null
        ));
    }
    
    public function handleSubscriptionCancelled(SubscriptionCancelled $event): void
    {
        broadcast(new SubscriptionCancelledEvent(
            $event->subscriptionId,
            $event->reason,
            $event->metadata['commandId'] ?? null
        ));
    }
    
    public function handleSubscriptionUpdated(SubscriptionUpdated $event): void
    {
        broadcast(new SubscriptionUpdatedEvent(
            $event->subscriptionId,
            $event->updates,
            $event->metadata['commandId'] ?? null
        ));
    }
}
```

Register the event subscriber in the `EventServiceProvider`:

```php
// app/Providers/EventServiceProvider.php
protected $subscribe = [
    SubscriptionEventSubscriber::class,
];
```

### 4. Configure Channel Authorization

Set up channel authentication in your `routes/channels.php` file:

```php
// routes/channels.php
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
```

This ensures that users can only subscribe to their own channels.

### 5. Add Command IDs for Event Correlation

To correlate frontend actions with backend events (for optimistic UI updates), include a command ID:

```php
// app/Commands/CreateSubscriptionCommand.php
class CreateSubscriptionCommand
{
    public function __construct(
        public string $subscriptionId,
        public string $userId,
        public string $name,
        public float $amount, 
        public string $billingCycle,
        public string $startDate,
        public ?string $commandId = null
    ) {
        $this->commandId ??= (string) Str::uuid();
    }
}
```

Modify the aggregate to include command IDs in event metadata:

```php
// app/Domain/Subscriptions/SubscriptionAggregate.php
public function createSubscription(
    string $id, 
    string $userId,
    string $name, 
    float $amount, 
    string $billingCycle,
    string $startDate,
    ?string $commandId = null
): self {
    // Business rule validation
    if ($amount <= 0) {
        throw new InvalidAmountException("Amount must be positive");
    }
    
    // Create the event with metadata
    $event = new SubscriptionCreated(
        $id, $userId, $name, $amount, $billingCycle, $startDate
    );
    
    // Add command ID to metadata if provided
    if ($commandId) {
        $event->metadata['commandId'] = $commandId;
    }
    
    // Record the event
    $this->recordThat($event);
    
    return $this;
}
```

### 6. Start the Reverb Server

Run the Reverb server:

```bash
php artisan reverb:start
```

For production, configure a supervisor process to keep Reverb running:

```
[program:reverb]
process_name=%(program_name)s
command=php /path/to/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/reverb.log
```

### 7. Frontend Integration

Use the Reverb client library to connect from your React frontend:

```jsx
// Install the client library
// npm install laravel-reverb

import { ReverbConnection } from 'laravel-reverb';
import { useState, useEffect } from 'react';

function SubscriptionList() {
    const [subscriptions, setSubscriptions] = useState([]);
    
    // Set up Reverb connection
    useEffect(() => {
        const reverb = new ReverbConnection({
            host: process.env.REVERB_HOST,
            appId: process.env.REVERB_APP_ID,
            key: process.env.REVERB_APP_KEY
        });
        
        // Authenticate with Laravel Sanctum
        reverb.withAuthToken(window.csrfToken);
        
        // Connect to the user's private channel
        const channel = reverb.subscribe(`private-user.${window.userId}`);
        
        // Listen for subscription events
        channel.listen('SubscriptionCreatedEvent', (event) => {
            setSubscriptions(current => [...current, event]);
        });
        
        channel.listen('SubscriptionCancelledEvent', (event) => {
            setSubscriptions(current => 
                current.map(sub => 
                    sub.id === event.id 
                        ? { ...sub, status: 'cancelled' } 
                        : sub
                )
            );
        });
        
        channel.listen('SubscriptionUpdatedEvent', (event) => {
            setSubscriptions(current => 
                current.map(sub => 
                    sub.id === event.id 
                        ? { ...sub, ...event.updates } 
                        : sub
                )
            );
        });
        
        // Clean up connection when component unmounts
        return () => {
            reverb.disconnect();
        };
    }, []);
    
    // Rest of component...
}
```

## Implementing Optimistic UI Updates

For a better user experience, implement optimistic UI updates:

### 1. Create a Command with ID

```jsx
// Generate a command ID
const createSubscription = async (data) => {
    const commandId = uuidv4();
    
    // Optimistically update the UI
    const optimisticSubscription = {
        id: uuidv4(), // Temporary ID
        name: data.name,
        amount: data.amount,
        billingCycle: data.billingCycle,
        status: 'pending', // Special status for optimistic update
        commandId: commandId
    };
    
    // Add to state immediately
    setSubscriptions(current => [...current, optimisticSubscription]);
    
    try {
        // Send command to backend
        const response = await api.post('/api/subscriptions', {
            ...data,
            command_id: commandId
        });
        
        // Update the temporary ID with the real one
        setSubscriptions(current => 
            current.map(sub => 
                sub.commandId === commandId 
                    ? { ...sub, id: response.data.id } 
                    : sub
            )
        );
        
        // The final state will be updated when the real event comes in via Reverb
    } catch (error) {
        // Revert optimistic update on error
        setSubscriptions(current => 
            current.filter(sub => sub.commandId !== commandId)
        );
        
        // Show error message
        showErrorNotification(error.message);
    }
};
```

### 2. Match Incoming Events with Commands

```jsx
// Listener for subscription events
channel.listen('SubscriptionCreatedEvent', (event) => {
    if (event.commandId) {
        // This is a response to a command we sent
        setSubscriptions(current => 
            current.map(sub => 
                sub.commandId === event.commandId
                    ? { 
                        ...sub,
                        id: event.id,
                        status: 'active',
                        isOptimistic: false
                      }
                    : sub
            )
        );
    } else {
        // This is a new subscription created elsewhere
        setSubscriptions(current => [...current, {
            ...event,
            status: 'active'
        }]);
    }
});
```

## Advanced Patterns

### 1. Event Throttling and Batching

For high-frequency events, implement throttling and batching:

```php
class ThrottledBroadcaster
{
    private $cache;
    private $pendingEvents = [];
    private $lastBatchTime;
    
    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
        $this->lastBatchTime = now();
    }
    
    public function queueBroadcast(string $channel, $event): void
    {
        $cacheKey = "broadcast_{$channel}_" . get_class($event);
        
        // If similar event was broadcast recently, skip
        if ($this->cache->has($cacheKey)) {
            $this->pendingEvents[$channel][] = $event;
            return;
        }
        
        // Queue event for batch sending
        $this->pendingEvents[$channel][] = $event;
        
        // Set throttle cache
        $this->cache->put($cacheKey, true, now()->addSeconds(2));
        
        // Check if it's time to send a batch
        if (count($this->pendingEvents[$channel]) >= 10 || 
            now()->diffInSeconds($this->lastBatchTime) >= 5) {
            $this->flush($channel);
        }
    }
    
    public function flush(string $channel): void
    {
        if (empty($this->pendingEvents[$channel])) {
            return;
        }
        
        // Send events as a batch
        broadcast(new BatchedEventsEvent(
            $channel,
            $this->pendingEvents[$channel]
        ));
        
        $this->pendingEvents[$channel] = [];
        $this->lastBatchTime = now();
    }
}
```

### 2. Presence Channels for Collaborative Features

For collaborative features, use presence channels:

```php
// routes/channels.php
Broadcast::channel('subscription.{subscriptionId}', function ($user, $subscriptionId) {
    $subscription = Subscription::find($subscriptionId);
    
    if (!$subscription) {
        return false;
    }
    
    if ($user->id === $subscription->user_id) {
        return ['id' => $user->id, 'name' => $user->name];
    }
    
    // Check if user is an admin or collaborator
    return $user->can('view', $subscription) 
        ? ['id' => $user->id, 'name' => $user->name]
        : false;
});
```

Frontend implementation:

```jsx
const presenceChannel = reverb.subscribe(`presence-subscription.${subscriptionId}`);

presenceChannel.here((users) => {
    setActiveUsers(users);
});

presenceChannel.joining((user) => {
    setActiveUsers(current => [...current, user]);
});

presenceChannel.leaving((user) => {
    setActiveUsers(current => current.filter(u => u.id !== user.id));
});
```

### 3. Handling Reconnection and Missed Events

Implement strategies for handling reconnection and missed events:

```jsx
// Track connection state
reverb.on('disconnected', () => {
    setConnectionStatus('disconnected');
    
    // Start a timestamp of when we lost connection
    setDisconnectedAt(new Date());
});

reverb.on('connected', async () => {
    setConnectionStatus('connected');
    
    // If we were previously disconnected, fetch any missed events
    if (disconnectedAt) {
        try {
            const response = await api.get('/api/events/since', {
                params: { timestamp: disconnectedAt.toISOString() }
            });
            
            // Process missed events
            response.data.events.forEach(event => {
                handleEvent(event);
            });
        } catch (error) {
            console.error('Failed to fetch missed events', error);
        }
        
        setDisconnectedAt(null);
    }
});
```

## Performance Considerations

### 1. Message Size Optimization

Keep broadcast events small:

```php
// Instead of sending the entire object
broadcast(new SubscriptionUpdatedEvent($subscription));

// Send only the necessary fields
broadcast(new SubscriptionUpdatedEvent(
    $subscription->id,
    [
        'amount' => $subscription->amount,
        'next_billing_date' => $subscription->next_billing_date
    ]
));
```

### 2. Channel Design

Group related entities into channels to reduce connection overhead:

```php
// Instead of many entity-specific channels
// user.123.subscription.456
// user.123.expense.789

// Use broader entity-type channels
// user.123.subscriptions
// user.123.expenses
```

### 3. Load Testing WebSockets

Perform load testing on your WebSocket implementation:

```bash
# Using Artillery for WebSocket load testing
npm install -g artillery
artillery run -e production websocket-load-test.yml
```

Example test configuration:

```yaml
# websocket-load-test.yml
config:
  target: "wss://reverb.example.com"
  phases:
    - duration: 60
      arrivalRate: 5
      rampTo: 50
      name: "Ramp up to 50 users"
  ws:
    headers:
      X-App-ID: "lifeos"
      X-App-Key: "your-app-key"
      
scenarios:
  - engine: "ws"
    flow:
      - connect: "/"
      - think: 5
      - send: 
          channel: "subscribe"
          data: 
            channel: "private-user.123"
            auth: "{{ authToken }}"
      - think: 30
```

## Error Handling and Monitoring

### 1. Client-Side Retry Logic

Implement client-side retry logic for reconnection:

```jsx
function createReverbConnection() {
    const reverb = new ReverbConnection({
        host: process.env.REVERB_HOST,
        appId: process.env.REVERB_APP_ID,
        key: process.env.REVERB_APP_KEY
    });
    
    // Configure connection with exponential backoff
    reverb.withRetry({
        maxRetries: 10,
        minDelay: 1000,
        maxDelay: 30000,
        factor: 2
    });
    
    return reverb;
}
```

### 2. Server-Side Monitoring

Monitor your Reverb server:

```php
// app/Providers/ReverbServiceProvider.php
public function boot()
{
    Reverb::measuring(function ($measurement) {
        // Log or send metrics to monitoring system
        Log::channel('reverb')->info('Reverb measurement', [
            'connections' => $measurement->connections,
            'channels' => $measurement->channels,
            'memory' => $measurement->memory,
        ]);
        
        // Send to metrics system
        Metrics::gauge('reverb.connections', $measurement->connections);
        Metrics::gauge('reverb.channels', $measurement->channels);
        Metrics::gauge('reverb.memory', $measurement->memory);
    });
}
```

### 3. Circuit Breaker for Broadcast Service

Implement a circuit breaker for broadcast functionality:

```php
class CircuitBreakedBroadcaster
{
    private $cache;
    private $failureThreshold = 5;
    private $resetTimeout = 60; // seconds
    
    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }
    
    public function broadcast($event)
    {
        $circuitKey = 'broadcast_circuit';
        
        // Check if circuit is open
        if ($this->cache->get($circuitKey) === 'open') {
            // Log that we're skipping broadcasting
            Log::warning('Broadcast circuit is open, skipping event', [
                'event' => get_class($event)
            ]);
            return;
        }
        
        try {
            // Attempt to broadcast
            broadcast($event);
            
            // Reset failure count on success
            $this->cache->put($circuitKey . '_failures', 0, now()->addDay());
        } catch (\Throwable $e) {
            // Increment failure count
            $failures = ($this->cache->get($circuitKey . '_failures') ?? 0) + 1;
            $this->cache->put($circuitKey . '_failures', $failures, now()->addDay());
            
            // If threshold reached, open circuit
            if ($failures >= $this->failureThreshold) {
                $this->cache->put($circuitKey, 'open', now()->addSeconds($this->resetTimeout));
                
                Log::error('Broadcast circuit opened due to failures', [
                    'failures' => $failures,
                    'reset_after' => $this->resetTimeout . ' seconds'
                ]);
            }
            
            throw $e;
        }
    }
}
```

## Security Considerations

### 1. Channel Authorization

Always authenticate users and authorize channel access:

```php
// Ensure users can only access their data
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Authorize more complex permissions
Broadcast::channel('team.{teamId}', function ($user, $teamId) {
    return $user->teams->contains($teamId);
});
```

### 2. Sensitive Data

Never broadcast sensitive data:

```php
// DON'T:
broadcast(new PaymentProcessedEvent(
    $payment->id,
    $payment->card_number, // Sensitive!
    $payment->amount
));

// DO:
broadcast(new PaymentProcessedEvent(
    $payment->id,
    // Only show last 4 digits
    '****' . substr($payment->card_number, -4),
    $payment->amount
));
```

### 3. Rate Limiting

Implement rate limiting for WebSocket connections:

```php
// app/Http/Middleware/RateLimitWebsockets.php
class RateLimitWebsockets
{
    public function handle($request, Closure $next)
    {
        // Limit to 60 connection attempts per minute
        $key = 'websocket_connection:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 60)) {
            return response('Too many connection attempts', 429);
        }
        
        RateLimiter::hit($key, 60);
        
        return $next($request);
    }
}
```

## Conclusion

Laravel Reverb provides a powerful real-time communication layer that integrates seamlessly with our event-sourced architecture. By translating domain events to broadcast events, we maintain the separation of concerns while providing instant updates to clients.

By following the patterns outlined in this document, you can create a responsive, real-time application experience that enhances user engagement and provides immediate feedback on their actions.

Remember to carefully consider security, performance, and error handling to ensure a robust real-time implementation. 
