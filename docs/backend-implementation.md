# LifeOS Backend Implementation Guide

This guide outlines the step-by-step process for implementing backend features in the LifeOS application following the event sourcing and vertical slice architecture.

## Setting Up the Development Environment

### Prerequisites

- PHP 8.1+
- Composer
- MySQL/PostgreSQL
- Laravel Sail (Docker) or Laravel Herd (macOS)

### Initial Setup

1. Clone the repository
2. Install dependencies with Composer
3. Set up environment variables
4. Run database migrations
5. Start the development server

## Feature Implementation Workflow

Each feature in LifeOS follows this implementation workflow:

1. Define the domain model and events
2. Implement command handlers
3. Create projections for read models
4. Build API endpoints
5. Set up real-time broadcasting
6. Write tests

Let's walk through an example implementation of the Subscription Management feature:

## Example: Implementing Subscription Management

### 1. Define Domain Model and Events

First, define the domain events that will be used in this feature:

```php
// app/Domain/Subscriptions/Events/SubscriptionCreated.php
namespace App\Domain\Subscriptions\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

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

// app/Domain/Subscriptions/Events/SubscriptionCancelled.php
namespace App\Domain\Subscriptions\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class SubscriptionCancelled implements ShouldBeStored
{
    public function __construct(
        public string $subscriptionId,
        public string $reason,
        public string $cancelledAt
    ) {
    }
}
```

Next, create the aggregate root to encapsulate business logic:

```php
// app/Domain/Subscriptions/SubscriptionAggregate.php
namespace App\Domain\Subscriptions;

use App\Domain\Subscriptions\Events\SubscriptionCreated;
use App\Domain\Subscriptions\Events\SubscriptionCancelled;
use App\Domain\Subscriptions\Exceptions\SubscriptionAlreadyCancelledException;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class SubscriptionAggregate extends AggregateRoot
{
    private string $status = '';
    
    public function createSubscription(
        string $subscriptionId,
        string $name,
        float $amount,
        string $billingCycle,
        string $startDate
    ): self {
        $this->recordThat(new SubscriptionCreated(
            $subscriptionId,
            $name,
            $amount,
            $billingCycle,
            $startDate
        ));
        
        return $this;
    }
    
    public function cancelSubscription(string $reason): self
    {
        if ($this->status === 'cancelled') {
            throw new SubscriptionAlreadyCancelledException();
        }
        
        $this->recordThat(new SubscriptionCancelled(
            $this->aggregateRootUuid(),
            $reason,
            now()->toIso8601String()
        ));
        
        return $this;
    }
    
    protected function applySubscriptionCreated(SubscriptionCreated $event): void
    {
        $this->status = 'active';
    }
    
    protected function applySubscriptionCancelled(SubscriptionCancelled $event): void
    {
        $this->status = 'cancelled';
    }
}
```

### 2. Implement Command Handlers

Define commands that represent user actions:

```php
// app/Application/Subscriptions/Commands/CreateSubscriptionCommand.php
namespace App\Application\Subscriptions\Commands;

class CreateSubscriptionCommand
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

// app/Application/Subscriptions/Commands/CancelSubscriptionCommand.php
namespace App\Application\Subscriptions\Commands;

class CancelSubscriptionCommand
{
    public function __construct(
        public string $subscriptionId,
        public ?string $reason
    ) {
    }
}
```

Create command handlers:

```php
// app/Application/Subscriptions/CommandHandlers/CreateSubscriptionHandler.php
namespace App\Application\Subscriptions\CommandHandlers;

use App\Application\Subscriptions\Commands\CreateSubscriptionCommand;
use App\Domain\Subscriptions\SubscriptionAggregate;

class CreateSubscriptionHandler
{
    public function handle(CreateSubscriptionCommand $command): void
    {
        SubscriptionAggregate::retrieve($command->subscriptionId)
            ->createSubscription(
                $command->subscriptionId,
                $command->name,
                $command->amount,
                $command->billingCycle,
                $command->startDate
            )
            ->persist();
    }
}

// app/Application/Subscriptions/CommandHandlers/CancelSubscriptionHandler.php
namespace App\Application\Subscriptions\CommandHandlers;

use App\Application\Subscriptions\Commands\CancelSubscriptionCommand;
use App\Domain\Subscriptions\SubscriptionAggregate;

class CancelSubscriptionHandler
{
    public function handle(CancelSubscriptionCommand $command): void
    {
        SubscriptionAggregate::retrieve($command->subscriptionId)
            ->cancelSubscription($command->reason ?? '')
            ->persist();
    }
}
```

Register the command handlers in a service provider:

```php
// app/Providers/CommandServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Application\Subscriptions\Commands\CreateSubscriptionCommand;
use App\Application\Subscriptions\Commands\CancelSubscriptionCommand;
use App\Application\Subscriptions\CommandHandlers\CreateSubscriptionHandler;
use App\Application\Subscriptions\CommandHandlers\CancelSubscriptionHandler;

class CommandServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag([
            CreateSubscriptionHandler::class,
            CancelSubscriptionHandler::class,
        ], 'command_handlers');
        
        $this->app->bind(CreateSubscriptionCommand::class . '@handler', CreateSubscriptionHandler::class);
        $this->app->bind(CancelSubscriptionCommand::class . '@handler', CancelSubscriptionHandler::class);
    }
}
```

### 3. Create Projections for Read Models

Define database models for read models:

```php
// app/Domain/Subscriptions/Models/Subscription.php
namespace App\Domain\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'id',
        'name',
        'amount',
        'billing_cycle',
        'start_date',
        'next_billing_date',
        'status',
    ];
    
    protected $casts = [
        'amount' => 'float',
        'start_date' => 'date',
        'next_billing_date' => 'date',
    ];
}
```

Create projectors to update read models:

```php
// app/Domain/Subscriptions/Projections/SubscriptionProjector.php
namespace App\Domain\Subscriptions\Projections;

use App\Domain\Subscriptions\Events\SubscriptionCreated;
use App\Domain\Subscriptions\Events\SubscriptionCancelled;
use App\Domain\Subscriptions\Models\Subscription;
use Carbon\Carbon;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class SubscriptionProjector extends Projector
{
    public function onSubscriptionCreated(SubscriptionCreated $event): void
    {
        $nextBillingDate = $this->calculateNextBillingDate(
            $event->startDate,
            $event->billingCycle
        );
        
        Subscription::create([
            'id' => $event->subscriptionId,
            'name' => $event->name,
            'amount' => $event->amount,
            'billing_cycle' => $event->billingCycle,
            'start_date' => $event->startDate,
            'next_billing_date' => $nextBillingDate,
            'status' => 'active',
        ]);
    }
    
    public function onSubscriptionCancelled(SubscriptionCancelled $event): void
    {
        Subscription::where('id', $event->subscriptionId)
            ->update(['status' => 'cancelled']);
    }
    
    private function calculateNextBillingDate(string $startDate, string $billingCycle): string
    {
        $start = Carbon::parse($startDate);
        
        return match($billingCycle) {
            'monthly' => $start->addMonth(),
            'yearly' => $start->addYear(),
            'weekly' => $start->addWeek(),
            default => $start->addMonth(),
        };
    }
}
```

Register the projector in the event sourcing configuration:

```php
// config/event-sourcing.php
return [
    // ...
    'projectors' => [
        \App\Domain\Subscriptions\Projections\SubscriptionProjector::class,
    ],
    // ...
];
```

### 4. Build API Endpoints

Create query objects for retrieving data:

```php
// app/Application/Subscriptions/Queries/GetSubscriptionsQuery.php
namespace App\Application\Subscriptions\Queries;

class GetSubscriptionsQuery
{
    public function __construct(
        public ?string $status = null
    ) {
    }
}

// app/Application/Subscriptions/Queries/GetSubscriptionByIdQuery.php
namespace App\Application\Subscriptions\Queries;

class GetSubscriptionByIdQuery
{
    public function __construct(
        public string $id
    ) {
    }
}
```

Implement query handlers:

```php
// app/Application/Subscriptions/QueryHandlers/GetSubscriptionsHandler.php
namespace App\Application\Subscriptions\QueryHandlers;

use App\Application\Subscriptions\Queries\GetSubscriptionsQuery;
use App\Application\Subscriptions\ViewModels\SubscriptionViewModel;
use App\Domain\Subscriptions\Models\Subscription;
use Illuminate\Support\Collection;

class GetSubscriptionsHandler
{
    public function handle(GetSubscriptionsQuery $query): Collection
    {
        $subscriptions = Subscription::query();
        
        if ($query->status) {
            $subscriptions->where('status', $query->status);
        }
        
        return $subscriptions->get()
            ->map(fn ($subscription) => new SubscriptionViewModel($subscription));
    }
}

// app/Application/Subscriptions/QueryHandlers/GetSubscriptionByIdHandler.php
namespace App\Application\Subscriptions\QueryHandlers;

use App\Application\Subscriptions\Queries\GetSubscriptionByIdQuery;
use App\Application\Subscriptions\ViewModels\SubscriptionViewModel;
use App\Domain\Subscriptions\Models\Subscription;

class GetSubscriptionByIdHandler
{
    public function handle(GetSubscriptionByIdQuery $query): ?SubscriptionViewModel
    {
        $subscription = Subscription::find($query->id);
        
        if (!$subscription) {
            return null;
        }
        
        return new SubscriptionViewModel($subscription);
    }
}
```

Create view models to format data for API responses:

```php
// app/Application/Subscriptions/ViewModels/SubscriptionViewModel.php
namespace App\Application\Subscriptions\ViewModels;

use App\Domain\Subscriptions\Models\Subscription;

class SubscriptionViewModel
{
    public string $id;
    public string $name;
    public float $amount;
    public string $billingCycle;
    public string $status;
    public string $startDate;
    public string $nextBillingDate;
    
    public function __construct(Subscription $subscription)
    {
        $this->id = $subscription->id;
        $this->name = $subscription->name;
        $this->amount = $subscription->amount;
        $this->billingCycle = $subscription->billing_cycle;
        $this->status = $subscription->status;
        $this->startDate = $subscription->start_date->toIso8601String();
        $this->nextBillingDate = $subscription->next_billing_date->toIso8601String();
    }
}
```

Create API controllers to handle HTTP requests:

```php
// app/Interface/Api/SubscriptionController.php
namespace App\Interface\Api;

use App\Application\Subscriptions\Commands\CreateSubscriptionCommand;
use App\Application\Subscriptions\Commands\CancelSubscriptionCommand;
use App\Application\Subscriptions\Queries\GetSubscriptionsQuery;
use App\Application\Subscriptions\Queries\GetSubscriptionByIdQuery;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        
        $query = new GetSubscriptionsQuery($status);
        $subscriptions = app()->make(GetSubscriptionsQuery::class . '@handler')->handle($query);
        
        return response()->json($subscriptions);
    }
    
    public function show(string $id)
    {
        $query = new GetSubscriptionByIdQuery($id);
        $subscription = app()->make(GetSubscriptionByIdQuery::class . '@handler')->handle($query);
        
        if (!$subscription) {
            return response()->json(['message' => 'Subscription not found'], 404);
        }
        
        return response()->json($subscription);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly,weekly',
            'start_date' => 'required|date',
        ]);
        
        $id = (string) Str::uuid();
        
        $command = new CreateSubscriptionCommand(
            $id,
            $validated['name'],
            $validated['amount'],
            $validated['billing_cycle'],
            $validated['start_date']
        );
        
        app()->make(CreateSubscriptionCommand::class . '@handler')->handle($command);
        
        return response()->json(['id' => $id], 201);
    }
    
    public function cancel(Request $request, string $id)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);
        
        $command = new CancelSubscriptionCommand($id, $validated['reason'] ?? null);
        
        try {
            app()->make(CancelSubscriptionCommand::class . '@handler')->handle($command);
            return response()->noContent();
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
```

Define routes in the API routes file:

```php
// routes/api.php
use App\Interface\Api\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::prefix('subscriptions')->group(function () {
    Route::get('/', [SubscriptionController::class, 'index']);
    Route::post('/', [SubscriptionController::class, 'store']);
    Route::get('/{id}', [SubscriptionController::class, 'show']);
    Route::post('/{id}/cancel', [SubscriptionController::class, 'cancel']);
});
```

### 5. Set Up Real-time Broadcasting

Create event listeners for broadcasting:

```php
// app/Infrastructure/EventListeners/BroadcastSubscriptionEvents.php
namespace App\Infrastructure\EventListeners;

use App\Domain\Subscriptions\Events\SubscriptionCreated;
use App\Domain\Subscriptions\Events\SubscriptionCancelled;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Event;

class BroadcastSubscriptionEvents implements ShouldBroadcast
{
    private $event;
    private $channels = [];
    
    public function handle($event): void
    {
        $this->event = $event;
        
        if ($event instanceof SubscriptionCreated) {
            $this->channels = ['subscriptions'];
            Event::dispatch('subscription.created', $event);
        }
        
        if ($event instanceof SubscriptionCancelled) {
            $this->channels = ['subscriptions', 'subscription.' . $event->subscriptionId];
            Event::dispatch('subscription.cancelled', $event);
        }
    }
    
    public function broadcastOn()
    {
        return $this->channels;
    }
}
```

Register the event listener in the event sourcing configuration:

```php
// config/event-sourcing.php
return [
    // ...
    'listeners' => [
        \App\Infrastructure\EventListeners\BroadcastSubscriptionEvents::class,
    ],
    // ...
];
```

Configure Laravel Reverb:

```php
// config/broadcasting.php
'reverb' => [
    'driver' => 'reverb',
    'app_id' => env('REVERB_APP_ID'),
    'key' => env('REVERB_APP_KEY'),
    'secret' => env('REVERB_APP_SECRET'),
    'app_host' => env('REVERB_HOST', 'localhost'),
    'host' => env('REVERB_SERVER_HOST', 'reverb.localhost'),
    'port' => env('REVERB_SERVER_PORT', 8080),
    'scheme' => env('REVERB_SERVER_SCHEME', 'http'),
    'options' => [
        'cluster' => env('REVERB_APP_CLUSTER', 'local'),
        'encrypted' => true,
        'host' => env('REVERB_HOST'),
        'port' => env('REVERB_PORT', 443),
        'scheme' => env('REVERB_SCHEME', 'https'),
    ],
],
```

### 6. Write Tests

Create feature tests for the API endpoints:

```php
// tests/Feature/Api/SubscriptionApiTest.php
namespace Tests\Feature\Api;

use App\Domain\Subscriptions\Events\SubscriptionCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubscriptionApiTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function can_create_a_subscription()
    {
        $response = $this->postJson('/api/subscriptions', [
            'name' => 'Netflix',
            'amount' => 14.99,
            'billing_cycle' => 'monthly',
            'start_date' => '2023-01-01',
        ]);
        
        $response->assertStatus(201)
            ->assertJsonStructure(['id']);
            
        $this->assertDatabaseHas('subscriptions', [
            'name' => 'Netflix',
            'amount' => 14.99,
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);
    }
    
    /** @test */
    public function can_cancel_a_subscription()
    {
        $id = (string) Str::uuid();
        
        event(new SubscriptionCreated(
            $id,
            'Netflix',
            14.99,
            'monthly',
            '2023-01-01'
        ));
        
        $response = $this->postJson("/api/subscriptions/{$id}/cancel", [
            'reason' => 'Too expensive',
        ]);
        
        $response->assertStatus(204);
        
        $this->assertDatabaseHas('subscriptions', [
            'id' => $id,
            'status' => 'cancelled',
        ]);
    }
    
    /** @test */
    public function can_get_subscriptions_list()
    {
        $id1 = (string) Str::uuid();
        $id2 = (string) Str::uuid();
        
        event(new SubscriptionCreated($id1, 'Netflix', 14.99, 'monthly', '2023-01-01'));
        event(new SubscriptionCreated($id2, 'Spotify', 9.99, 'monthly', '2023-01-15'));
        
        $response = $this->getJson('/api/subscriptions');
        
        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonPath('0.name', 'Netflix')
            ->assertJsonPath('1.name', 'Spotify');
    }
    
    /** @test */
    public function can_filter_subscriptions_by_status()
    {
        $id1 = (string) Str::uuid();
        $id2 = (string) Str::uuid();
        
        event(new SubscriptionCreated($id1, 'Netflix', 14.99, 'monthly', '2023-01-01'));
        event(new SubscriptionCreated($id2, 'Spotify', 9.99, 'monthly', '2023-01-15'));
        
        // Cancel Netflix
        $this->postJson("/api/subscriptions/{$id1}/cancel");
        
        $response = $this->getJson('/api/subscriptions?status=active');
        
        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'Spotify');
    }
}
```

Create unit tests for domain logic:

```php
// tests/Unit/Domain/Subscriptions/SubscriptionAggregateTest.php
namespace Tests\Unit\Domain\Subscriptions;

use App\Domain\Subscriptions\Events\SubscriptionCreated;
use App\Domain\Subscriptions\Events\SubscriptionCancelled;
use App\Domain\Subscriptions\Exceptions\SubscriptionAlreadyCancelledException;
use App\Domain\Subscriptions\SubscriptionAggregate;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubscriptionAggregateTest extends TestCase
{
    /** @test */
    public function it_can_create_a_subscription()
    {
        $id = (string) Str::uuid();
        
        $aggregate = SubscriptionAggregate::retrieve($id)
            ->createSubscription(
                $id, 
                'Netflix', 
                14.99, 
                'monthly', 
                '2023-01-01'
            );
            
        $aggregate->persist();
        
        $recordedEvents = $aggregate->getRecordedEvents();
        
        $this->assertCount(1, $recordedEvents);
        $this->assertInstanceOf(SubscriptionCreated::class, $recordedEvents[0]);
        $this->assertEquals('Netflix', $recordedEvents[0]->name);
    }
    
    /** @test */
    public function it_can_cancel_a_subscription()
    {
        $id = (string) Str::uuid();
        
        $aggregate = SubscriptionAggregate::retrieve($id)
            ->createSubscription(
                $id, 
                'Netflix', 
                14.99, 
                'monthly', 
                '2023-01-01'
            )
            ->cancelSubscription('Too expensive');
            
        $recordedEvents = $aggregate->getRecordedEvents();
        
        $this->assertCount(2, $recordedEvents);
        $this->assertInstanceOf(SubscriptionCancelled::class, $recordedEvents[1]);
        $this->assertEquals('Too expensive', $recordedEvents[1]->reason);
    }
    
    /** @test */
    public function it_cannot_cancel_an_already_cancelled_subscription()
    {
        $id = (string) Str::uuid();
        
        $aggregate = SubscriptionAggregate::retrieve($id)
            ->createSubscription(
                $id, 
                'Netflix', 
                14.99, 
                'monthly', 
                '2023-01-01'
            )
            ->cancelSubscription('Too expensive');
            
        $this->expectException(SubscriptionAlreadyCancelledException::class);
        
        $aggregate->cancelSubscription('Changed my mind');
    }
}
```

## Automatic Reminders and Scheduled Tasks

For features requiring scheduled tasks, such as subscription renewal reminders:

```php
// app/Infrastructure/Console/Commands/SendSubscriptionReminders.php
namespace App\Infrastructure\Console\Commands;

use App\Domain\Subscriptions\Models\Subscription;
use App\Domain\Subscriptions\Services\SubscriptionReminderService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendSubscriptionReminders extends Command
{
    protected $signature = 'subscriptions:send-reminders';
    protected $description = 'Send reminders for upcoming subscription renewals';
    
    public function handle(SubscriptionReminderService $reminderService)
    {
        $tomorrow = Carbon::tomorrow();
        
        $upcomingRenewals = Subscription::query()
            ->where('status', 'active')
            ->whereDate('next_billing_date', $tomorrow)
            ->get();
            
        foreach ($upcomingRenewals as $subscription) {
            $reminderService->sendRenewalReminder($subscription);
            $this->info("Sent reminder for {$subscription->name}");
        }
        
        return Command::SUCCESS;
    }
}
```

Register the command in the scheduler:

```php
// app/Infrastructure/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('subscriptions:send-reminders')
        ->dailyAt('09:00')
        ->withoutOverlapping();
}
```

## Migration to Production

When ready for production deployment:

1. Set up production environment
2. Configure environment variables
3. Run database migrations
4. Deploy application code
5. Start Laravel Reverb server
6. Set up scheduler cron job

## Common Challenges and Solutions

### Handling Event Version Migrations

As your domain events evolve, you may need to migrate older event versions:

```php
// In your projector
public function onSubscriptionCreated(SubscriptionCreated $event): void
{
    // Handle newer event versions with additional properties
    $category = $event->category ?? 'entertainment'; // Default for old events
    
    // Update read model with the data...
}
```

### Dealing with Eventual Consistency

When working with event sourcing, be aware of eventual consistency delays:

1. Use loading states in the UI
2. Implement retry mechanisms
3. Consider updating projections synchronously for critical operations

### Optimizing Event Store Performance

For large-scale applications:

1. Consider event snapshotting
2. Implement event versioning
3. Set up archiving for old events
4. Use database indexing strategies for the event store 
