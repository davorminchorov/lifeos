# Tenancy Production Fix

## Problem Summary

The previous TenantScope implementation included all records with `NULL tenant_id`, which caused:
1. **Security risk**: Users could see other users' data if records had NULL tenant_id
2. **Data visibility issues**: Inconsistent data showing based on database state

## Changes Made

### 1. Fixed TenantScope (app/Scopes/TenantScope.php)
- **Removed** the insecure `orWhereNull()` clause
- **Enforced** strict tenant isolation - users now only see data for their current tenant
- Maintains fail-closed security when no tenant is set

### 2. Added CheckTenancyStatus Command
```bash
php artisan tenants:check-status
php artisan tenants:check-status --user-id=123
```
Use this to diagnose tenancy issues in production.

### 3. Improved AssignMissingTenantIds Command
- Better error handling for users without `current_tenant_id`
- Reports records that couldn't be assigned
- Safer with `whereExists` checks

## Production Deployment Steps

### Step 1: Check Current State
```bash
php artisan tenants:check-status
```

This will show:
- How many users have `current_tenant_id` set
- How many tenants exist
- Which tables have NULL `tenant_id` records

### Step 2: Ensure Migrations Have Run

Check that these migrations have been executed:
- `2026_01_19_*_create_tenants_table`
- `2026_01_19_*_create_tenant_members_table`
- `2026_01_19_*_add_current_tenant_id_to_users_table`
- `2026_01_19_*_add_tenant_id_to_all_tables`
- `2026_01_19_*_assign_existing_data_to_default_tenants`

```bash
php artisan migrate:status
```

### Step 3: Fix Users Without Tenants

If users don't have `current_tenant_id` set:

**Option A: Use Tinker (for small numbers)**
```bash
php artisan tinker
```
```php
User::whereNull('current_tenant_id')->each(function($user) {
    $tenant = $user->ownedTenants()->first() ?? $user->tenants()->first();
    if ($tenant) {
        $user->update(['current_tenant_id' => $tenant->id]);
        echo "Set user {$user->id} to tenant {$tenant->id}\n";
    } else {
        echo "User {$user->id} has no tenants!\n";
    }
});
```

**Option B: Create Default Tenants (if users have no tenants)**
```bash
php artisan tinker
```
```php
use Illuminate\Support\Str;

User::whereNull('current_tenant_id')->each(function($user) {
    // Create personal tenant
    $tenant = \App\Models\Tenant::create([
        'name' => "{$user->name}'s Personal Account",
        'slug' => Str::slug($user->name) . '-personal-' . Str::random(6),
        'owner_id' => $user->id,
    ]);

    // Add as admin member
    $tenant->members()->attach($user->id, ['role' => 'admin']);

    // Set as current
    $user->update(['current_tenant_id' => $tenant->id]);

    echo "Created tenant {$tenant->id} for user {$user->id}\n";
});
```

### Step 4: Assign Data to Tenants

```bash
# Preview what will be updated
php artisan tenants:assign-missing --dry-run

# Apply the changes
php artisan tenants:assign-missing
```

### Step 5: Verify Everything

```bash
php artisan tenants:check-status
```

Should show:
- ✓ All users have `current_tenant_id`
- ✓ All data tables have `tenant_id` assigned
- ✓ No warnings

### Step 6: Test in Production

1. Log in as a test user
2. Verify they can see their data (expenses, invoices, etc.)
3. Check that data counts match expected values
4. Verify users can switch between tenants if they're members of multiple

## Rollback Plan (if needed)

If you need to rollback temporarily:

```bash
# This will temporarily allow NULL tenant_id records again
# Edit app/Scopes/TenantScope.php and add back orWhereNull
```

However, **do not do this** unless absolutely necessary as it's a security risk.

## Common Issues

### Issue: "No data showing after deployment"
**Cause**: Users have `current_tenant_id` set, but their data has NULL `tenant_id`
**Fix**: Run `php artisan tenants:assign-missing`

### Issue: "User can't see any data but used to"
**Cause**: User's `current_tenant_id` doesn't match their data's `tenant_id`
**Fix**: Check with `php artisan tenants:check-status --user-id=X` and reassign if needed

### Issue: "Some tables still have NULL tenant_id"
**Cause**: Data created after migration but before tenant assignment
**Fix**: Run `php artisan tenants:assign-missing` again

## Long-term Solution

Going forward, all new records will automatically get `tenant_id` assigned via the `BelongsToTenant` trait. The NULL `tenant_id` issue should not recur.

## Support Commands Reference

```bash
# Check overall tenancy status
php artisan tenants:check-status

# Check specific user
php artisan tenants:check-status --user-id=123

# Preview tenant assignment
php artisan tenants:assign-missing --dry-run

# Fix missing tenant assignments
php artisan tenants:assign-missing
```
