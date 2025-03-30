# LifeOS Projection Rebuilding Strategy

This document outlines the strategy for rebuilding projections in the LifeOS application with minimal downtime using a blue/green deployment approach.

## The Challenge with Projection Rebuilds

In event-sourced systems, projections (read models) are derived from event streams. Over time, these projections may need to be rebuilt due to:

1. **Schema Changes**: Modifications to the projection structure
2. **Bug Fixes**: Correcting errors in projector logic
3. **New Requirements**: Adding new fields or relationships
4. **Performance Tuning**: Optimizing the projection for specific queries
5. **Data Recovery**: Restoring projections after failure

The challenge is that traditional rebuilds require dropping tables and recreating them, which causes downtime. During this downtime, users cannot access the data.

## Blue/Green Projection Strategy Overview

LifeOS uses a blue/green deployment strategy for projection rebuilds to eliminate downtime:

1. **Blue Environment**: Current production projections that users are actively querying
2. **Green Environment**: New projections being rebuilt in parallel
3. **Atomic Switch**: Quick switch from blue to green once rebuild is complete

This approach ensures continuous availability while allowing complete projection rebuilds.

## Implementation Details

### 1. Table Duplication

When a rebuild is needed, we create duplicate tables with a suffix:

```php
Schema::create('subscriptions_rebuild', function (Blueprint $table) {
    // Copy structure from original table
    $this->copyTableStructure('subscriptions', 'subscriptions_rebuild');
});
```

Depending on your database system, the implementation of `copyTableStructure` varies:

```php
// For MySQL
DB::statement("CREATE TABLE {$targetTable} LIKE {$sourceTable}");

// For PostgreSQL
DB::statement("CREATE TABLE {$targetTable} (LIKE {$sourceTable} INCLUDING ALL)");
```

### 2. Configuring Projectors

We temporarily redirect projectors to write to the rebuild tables:

```php
// Set configuration to use suffix during rebuild
config(['event-sourcing.projection_suffix' => '_rebuild']);
```

This ensures that:
- Projectors read from the event store and write to the rebuild tables
- New events occurring during rebuild are captured in both sets of tables

### 3. The Rebuild Process

The actual rebuild involves replaying events:

```php
// Use the Projectionist to replay events
app(Projectionist::class)->replay(
    // Optionally specify which projectors to rebuild
    collect([
        app(SubscriptionProjector::class),
        app(CategorySummaryProjector::class)
    ])
);
```

You can monitor progress during rebuilds with a custom progress tracker:

```php
class RebuildProgressTracker implements EventHandler
{
    private $total;
    private $processed = 0;
    private $startTime;
    
    public function __construct(StoredEventRepository $repository)
    {
        $this->total = $repository->countAllEvents();
        $this->startTime = microtime(true);
    }
    
    public function handleEvent(StoredEvent $event): void
    {
        $this->processed++;
        
        if ($this->processed % 1000 === 0) {
            $percentage = round(($this->processed / $this->total) * 100, 2);
            $elapsed = microtime(true) - $this->startTime;
            $estimatedTotal = ($elapsed / $this->processed) * $this->total;
            $estimatedRemaining = $estimatedTotal - $elapsed;
            
            Log::info("Projection rebuild progress: {$percentage}% ({$this->processed}/{$this->total}) - Est. time remaining: " . gmdate('H:i:s', $estimatedRemaining));
        }
    }
}
```

### 4. The Atomic Switch

Once the rebuild is complete, we switch tables atomically:

```php
DB::transaction(function () {
    // Rename the old table to _old
    Schema::rename('subscriptions', 'subscriptions_old');
    
    // Rename the new table to the primary name
    Schema::rename('subscriptions_rebuild', 'subscriptions');
    
    // Optionally clean up the old table when everything is verified
    // Schema::dropIfExists('subscriptions_old');
});
```

Using a transaction ensures that the rename operations are atomic and no queries will fail.

### 5. Reset Configuration

After the switch, we reset the configuration:

```php
// Reset configuration
config(['event-sourcing.projection_suffix' => '']);
```

## Implementation as a Command

Here's the complete implementation as an Artisan command:

```php
class RebuildProjectionsCommand extends Command
{
    protected $signature = 'lifeos:rebuild-projections 
                            {--projector=* : Specific projector classes to rebuild}
                            {--keep-old-tables : Keep old tables after rebuild}';
    
    protected $description = 'Rebuild projections using blue/green strategy for zero downtime';
    
    public function handle()
    {
        $this->info('Starting blue/green projection rebuild');
        
        // Identify tables to rebuild
        $tables = $this->identifyTablesToRebuild();
        
        // Create rebuild tables
        $this->createRebuildTables($tables);
        
        // Configure projectors to use rebuild tables
        config(['event-sourcing.projection_suffix' => '_rebuild']);
        
        // Determine which projectors to rebuild
        $projectors = $this->determineProjectors();
        
        // Begin replay
        $this->info('Beginning event replay to rebuild projections...');
        $progressBar = $this->output->createProgressBar(
            app(StoredEventRepository::class)->countAllEvents()
        );
        
        // Register progress tracker
        Event::listen(function (StartingEventReplay $event) use ($progressBar) {
            $progressBar->start();
        });
        
        Event::listen(function (EventHandled $event) use ($progressBar) {
            $progressBar->advance();
        });
        
        Event::listen(function (FinishedEventReplay $event) use ($progressBar) {
            $progressBar->finish();
            $this->newLine();
        });
        
        // Perform the replay
        app(Projectionist::class)->replay(collect($projectors));
        
        // Switch tables
        $this->info('Replay complete, switching tables...');
        $this->switchTables($tables);
        
        // Reset configuration
        config(['event-sourcing.projection_suffix' => '']);
        
        $this->info('Projection rebuild completed successfully!');
        
        return Command::SUCCESS;
    }
    
    private function identifyTablesToRebuild(): array
    {
        // Identify tables based on projector models or configuration
        // This is a simplified example - in practice, you'd derive this from projector classes
        return [
            'subscriptions',
            'expenses',
            'category_summaries',
            'monthly_summaries',
        ];
    }
    
    private function createRebuildTables(array $tables): void
    {
        foreach ($tables as $table) {
            $rebuildTable = "{$table}_rebuild";
            
            if (Schema::hasTable($rebuildTable)) {
                Schema::dropIfExists($rebuildTable);
            }
            
            $this->info("Creating rebuild table: {$rebuildTable}");
            $this->copyTableStructure($table, $rebuildTable);
        }
    }
    
    private function copyTableStructure(string $source, string $target): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement("CREATE TABLE {$target} LIKE {$source}");
        } elseif (config('database.default') === 'pgsql') {
            DB::statement("CREATE TABLE {$target} (LIKE {$source} INCLUDING ALL)");
        } else {
            // SQLite or other database systems
            $this->createTableByInspection($source, $target);
        }
    }
    
    private function createTableByInspection(string $source, string $target): void
    {
        // Get table structure and recreate it
        $columns = Schema::getColumnListing($source);
        
        Schema::create($target, function (Blueprint $table) use ($source, $columns) {
            foreach ($columns as $column) {
                $type = DB::getSchemaBuilder()->getColumnType($source, $column);
                
                // Create the column with the same type
                // Note: This is a simplified approach and might need more complex logic
                // for indexes, constraints, defaults, etc.
                $table->{$type}($column);
            }
        });
    }
    
    private function determineProjectors(): array
    {
        if ($this->option('projector')) {
            return array_map(function ($class) {
                return app($class);
            }, $this->option('projector'));
        }
        
        // Get all registered projectors from config
        return app(Projectionist::class)->getProjectors();
    }
    
    private function switchTables(array $tables): void
    {
        DB::transaction(function () use ($tables) {
            foreach ($tables as $table) {
                $oldTable = "{$table}_old";
                $rebuildTable = "{$table}_rebuild";
                
                // Drop old table if it exists from previous rebuild
                if (Schema::hasTable($oldTable)) {
                    Schema::dropIfExists($oldTable);
                }
                
                $this->info("Renaming {$table} to {$oldTable}");
                Schema::rename($table, $oldTable);
                
                $this->info("Renaming {$rebuildTable} to {$table}");
                Schema::rename($rebuildTable, $table);
                
                if (!$this->option('keep-old-tables')) {
                    $this->info("Dropping old table: {$oldTable}");
                    Schema::dropIfExists($oldTable);
                } else {
                    $this->info("Keeping old table for reference: {$oldTable}");
                }
            }
        });
    }
}
```

## Advanced Strategies

### Handling Dependencies Between Projections

Some projections may depend on others. In these cases:

1. Identify the dependency graph
2. Rebuild projections in order of dependencies
3. Ensure foreign keys are properly handled during the switch

Example dependency handling:

```php
// Define projection dependencies
$dependencies = [
    'subscription_items' => ['subscriptions'],
    'payment_history' => ['subscriptions'],
];

// Sort projections based on dependencies
$sorted = $this->topologicalSort($tables, $dependencies);
```

### Partial Rebuilds

For very large event stores, you might want to rebuild projections incrementally:

```php
// Rebuild only events from a specific date range
app(Projectionist::class)->replayUntil(
    collect([app(SubscriptionProjector::class)]),
    function (StoredEvent $storedEvent) {
        // Only replay events from the last 30 days
        return $storedEvent->created_at->isAfter(now()->subDays(30));
    }
);
```

### Monitoring Rebuild Health

Implement health checks to monitor the rebuild process:

```php
// Verify rebuild tables are consistent
private function verifyRebuildTables(array $tables): bool
{
    foreach ($tables as $table) {
        $originalCount = DB::table($table)->count();
        $rebuildCount = DB::table("{$table}_rebuild")->count();
        
        // Allow for some difference due to ongoing operations
        $difference = abs($originalCount - $rebuildCount);
        $percentageDiff = ($originalCount > 0) 
            ? ($difference / $originalCount) * 100 
            : 0;
            
        if ($percentageDiff > 5) {
            $this->error("Rebuild table count differs by more than 5%: {$table}");
            $this->error("Original: {$originalCount}, Rebuild: {$rebuildCount}");
            return false;
        }
    }
    
    return true;
}
```

## Troubleshooting Common Issues

### 1. Foreign Key Constraints

When working with foreign keys, you may encounter issues during the switch:

**Solution**:
- Temporarily disable foreign key checks during the switch
- Ensure that related tables are switched in the correct order

```php
// For MySQL
DB::statement('SET FOREIGN_KEY_CHECKS=0');
// Perform table renames
DB::statement('SET FOREIGN_KEY_CHECKS=1');
```

### 2. Long-Running Queries

If users execute long-running queries, they might be affected during the switch:

**Solution**:
- Schedule rebuilds during low-usage periods
- Add timeout handling for queries that might span the switch period

### 3. Inconsistent Data

If events arrive during the rebuild process, they might not be processed in the rebuild:

**Solution**:
- Ensure that the event handlers are registered correctly
- Implement a reconciliation process after the switch

## Best Practices

1. **Test in Staging First**: Always test your rebuild strategy in a staging environment
2. **Monitor Both Tables**: During the rebuild, monitor both current and rebuild tables
3. **Schedule During Low Traffic**: Schedule rebuilds during periods of low traffic
4. **Keep Backup of Old Tables**: Keep the old tables for a period after the switch
5. **Transactions**: Always use transactions for the atomic switch
6. **Progressive Rebuilds**: For very large systems, consider rebuilding projections in stages
7. **Alerting**: Set up alerting for rebuild failures or inconsistencies
8. **Documentation**: Document the rebuild process and results each time

## Conclusion

The blue/green projection rebuilding strategy allows for zero-downtime maintenance of read models in the LifeOS application. By creating duplicate tables and performing an atomic switch, we ensure that users always have access to the data they need, even during major projection overhauls. 
