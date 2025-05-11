# Database Migrations Documentation

## Migration Fix: Duplicate Subscription Tables

The application had duplicate migrations that created conflicting database tables. Two separate migrations
were attempting to create the `subscriptions` and `payments` tables with different structures:

1. Original migrations (`2023_07_01_000000_create_subscriptions_table.php` and `2023_08_01_000000_create_payments_table.php`) 
   - Used auto-incrementing `id` (bigint)
   - Had fewer columns
   - Did not support the Event Sourcing architecture

2. Newer migrations (`2025_03_31_000000_create_subscriptions_tables.php`)
   - Used UUIDs as primary keys
   - Included additional tables for event sourcing projections
   - Had potentially conflicting relationships

### The Fix

To resolve this issue, we've implemented the following changes:

1. Modified the newer migration to check if the original tables exist, and skip running if they do
2. Created an adaptive migration (`2025_05_10_000000_fix_duplicate_subscriptions_migrations.php`) that:
   - Adds event sourcing support through a separate `subscription_events` table
   - Safely adds the missing columns to the original tables if needed

3. Updated the reminder/notification/tags migrations to work with either table structure:
   - Created a new adaptive migration (`2025_05_15_000000_adapt_subscription_reminders_for_existing_tables.php`)
   - Modified the original reminder/notification/tags migrations to skip if the adaptive one has run

### Foreign Key Type Compatibility Fix

After implementing the initial fix, we encountered a foreign key type compatibility issue. The error was:

```
SQLSTATE[HY000]: General error: 3780 Referencing column 'subscription_id' and referenced column 'id' in foreign key constraint are incompatible.
```

To fix this issue, we made the following additional changes:

1. Updated all migrations to detect the subscription ID type (UUID vs bigint) at runtime
2. Made each migration create the appropriate type for the subscription_id foreign key column
3. Simplified the foreign key creation logic
4. Ensured that all related tables would have compatible column types to match subscriptions.id

This ensures that regardless of which migration runs first, the foreign key types will be compatible.

### Database Schema Structure

After running these migrations, you'll have one of two possible database structures:

#### Scenario 1: Original migrations ran first
- `subscriptions` (with auto-incrementing id)
- `payments` (with auto-incrementing id and foreign key to subscriptions)
- `subscription_events` (for event sourcing)
- `subscription_reminders` (with unsignedBigInteger subscription_id)
- `subscription_notifications` (with unsignedBigInteger subscription_id)
- `subscription_tags` and `subscription_tag` (with unsignedBigInteger related keys)

#### Scenario 2: Newer migrations ran first
- `subscriptions` (with UUID primary key)
- `payments` (with UUID primary key and foreign key to subscriptions)
- `upcoming_payments` (projection table)
- `subscription_reminders` (with UUID subscription_id)
- `subscription_notifications` (with UUID subscription_id)
- `subscription_tags` and `subscription_tag` (with UUID related keys)

### Event Sourcing Implementation

The event sourcing architecture is now supported in both scenarios:

- In Scenario 1, events are stored in the `subscription_events` table
- In Scenario 2, the original event sourcing design is preserved

## Important Notes

- If you're setting up a new instance, the system should use Scenario 1 (auto-incrementing IDs)
- Existing applications with UUID-based tables will continue to work with Scenario 2
- The application code has been updated to handle both scenarios

## Migration Order

For a new installation, migrations should run in the following order:

1. Original subscription and payment tables
2. Fix for duplicate migrations
3. Adaptive reminder/notification/tags tables
4. Other domain tables (expenses, investments, etc.) 
