<?php

namespace Tests\Feature;

use App\Jobs\ImportSubscriptionsCsv;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportSubscriptionsCsvTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_form_requires_authentication(): void
    {
        $response = $this->get('/subscriptions/import');

        $response->assertRedirect('/login');
    }

    public function test_import_form_renders_for_authenticated_user(): void
    {
        $this->setupTenantContext();

        $response = $this->get('/subscriptions/import');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Subscriptions/Import'));
    }

    public function test_import_requires_a_file(): void
    {
        $this->setupTenantContext();

        $response = $this->post('/subscriptions/import', []);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_rejects_non_csv_file(): void
    {
        $this->setupTenantContext();

        $file = UploadedFile::fake()->create('subscriptions.pdf', 100, 'application/pdf');

        $response = $this->post('/subscriptions/import', ['file' => $file]);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_rejects_file_exceeding_max_size(): void
    {
        $this->setupTenantContext();

        $file = UploadedFile::fake()->create('subscriptions.csv', 11000, 'text/csv');

        $response = $this->post('/subscriptions/import', ['file' => $file]);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_dispatches_job_on_imports_queue(): void
    {
        Queue::fake();
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = "service_name,cost,billing_cycle,next_billing_date\nNetflix,15.99,monthly,2026-04-15";
        $file = UploadedFile::fake()->createWithContent('subscriptions.csv', $csvContent);

        $response = $this->postJson('/subscriptions/import', ['file' => $file]);

        $response->assertOk();
        $response->assertJson(['status' => 'queued']);

        Queue::assertPushedOn('imports', ImportSubscriptionsCsv::class, function ($job) use ($user, $tenant) {
            return $job->userId === $user->id && $job->tenantId === $tenant->id;
        });
    }

    public function test_job_creates_subscriptions_from_valid_csv(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = implode("\n", [
            'service_name,cost,billing_cycle,next_billing_date,currency,category,status,auto_renewal,payment_method',
            'Netflix,15.99,monthly,2026-04-15,USD,Entertainment,active,true,Credit Card',
            'Spotify,9.99,monthly,2026-04-20,USD,Entertainment,active,true,PayPal',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_subscriptions.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportSubscriptionsCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('subscriptions', 2);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'service_name' => 'Netflix',
            'cost' => 15.99,
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'category' => 'Entertainment',
            'status' => 'active',
            'auto_renewal' => true,
            'payment_method' => 'Credit Card',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'service_name' => 'Spotify',
            'cost' => 9.99,
            'payment_method' => 'PayPal',
        ]);

        Storage::assertMissing($storedPath);
    }

    public function test_job_skips_duplicate_rows_via_unique_key(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = implode("\n", [
            'service_name,cost,billing_cycle,next_billing_date',
            'Netflix,15.99,monthly,2026-04-15',
        ]);

        $uniqueKey = 'csv-import:'.md5('Netflix15.99monthly2026-04-15');

        Subscription::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'service_name' => 'Netflix',
            'cost' => 15.99,
            'billing_cycle' => 'monthly',
            'currency' => 'USD',
            'category' => 'Entertainment',
            'start_date' => '2026-04-15',
            'next_billing_date' => '2026-04-15',
            'status' => 'active',
            'unique_key' => $uniqueKey,
        ]);

        $storedPath = 'imports/'.$user->id.'/test_subscriptions.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportSubscriptionsCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('subscriptions', 1);
    }

    public function test_job_skips_duplicate_rows_matching_manually_created_subscriptions(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = implode("\n", [
            'service_name,cost,billing_cycle,next_billing_date',
            'Netflix,15.99,monthly,2026-04-15',
        ]);

        Subscription::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'service_name' => 'Netflix',
            'cost' => 15.99,
            'billing_cycle' => 'monthly',
            'currency' => 'USD',
            'category' => 'Entertainment',
            'start_date' => '2026-04-15',
            'next_billing_date' => '2026-04-15',
            'status' => 'active',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_subscriptions.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportSubscriptionsCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('subscriptions', 1);
    }

    public function test_job_skips_rows_missing_required_fields(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = implode("\n", [
            'service_name,cost,billing_cycle,next_billing_date',
            ',15.99,monthly,2026-04-15',             // missing service_name
            'Netflix,,monthly,2026-04-15',            // missing cost
            'Netflix,15.99,,2026-04-15',              // missing billing_cycle
            'Netflix,15.99,monthly,',                 // missing next_billing_date
            'Spotify,9.99,monthly,2026-04-20',        // valid row
        ]);

        $storedPath = 'imports/'.$user->id.'/test_subscriptions.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportSubscriptionsCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseHas('subscriptions', [
            'service_name' => 'Spotify',
        ]);
    }

    public function test_job_applies_defaults_for_optional_columns(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = implode("\n", [
            'service_name,cost,billing_cycle,next_billing_date',
            'Netflix,15.99,monthly,2026-04-15',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_subscriptions.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportSubscriptionsCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $subscription = Subscription::first();
        $this->assertEquals('MKD', $subscription->currency);
        $this->assertEquals('Other', $subscription->category);
        $this->assertEquals('active', $subscription->status);
        $this->assertTrue($subscription->auto_renewal);
        $this->assertNull($subscription->payment_method);
        $this->assertNull($subscription->notes);
        $this->assertNull($subscription->description);
    }

    public function test_job_supports_header_aliases(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = implode("\n", [
            'name,price,frequency,renewal_date',
            'Netflix,15.99,monthly,2026-04-15',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_subscriptions.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportSubscriptionsCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseHas('subscriptions', [
            'service_name' => 'Netflix',
            'cost' => 15.99,
            'billing_cycle' => 'monthly',
        ]);
    }

    public function test_job_handles_empty_csv_gracefully(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $storedPath = 'imports/'.$user->id.'/test_subscriptions.csv';
        Storage::put($storedPath, '');

        $job = new ImportSubscriptionsCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_job_handles_missing_file_gracefully(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $job = new ImportSubscriptionsCsv($user->id, $tenant->id, 'imports/nonexistent.csv');
        $job->handle();

        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_job_writes_progress_to_cache(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = implode("\n", [
            'service_name,cost,billing_cycle,next_billing_date',
            'Netflix,15.99,monthly,2026-04-15',
            'Spotify,9.99,monthly,2026-04-20',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_subscriptions.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportSubscriptionsCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $progress = Cache::get('subscription_import_progress:'.$user->id);
        $this->assertNotNull($progress);
        $this->assertEquals('completed', $progress['status']);
        $this->assertEquals(2, $progress['total']);
        $this->assertEquals(2, $progress['created']);
        $this->assertEquals(0, $progress['skipped']);
        $this->assertEquals(0, $progress['failed']);
    }

    public function test_progress_endpoint_returns_cached_data(): void
    {
        ['user' => $user] = $this->setupTenantContext();

        Cache::put('subscription_import_progress:'.$user->id, [
            'status' => 'processing',
            'total' => 10,
            'created' => 5,
            'skipped' => 1,
            'failed' => 0,
        ], 300);

        $response = $this->getJson('/subscriptions/import/progress');

        $response->assertOk();
        $response->assertJson([
            'status' => 'processing',
            'total' => 10,
            'created' => 5,
            'skipped' => 1,
            'failed' => 0,
        ]);
    }

    public function test_progress_endpoint_returns_idle_when_no_import(): void
    {
        $this->setupTenantContext();

        $response = $this->getJson('/subscriptions/import/progress');

        $response->assertOk();
        $response->assertJson(['status' => 'idle']);
    }

    public function test_progress_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/subscriptions/import/progress');

        $response->assertUnauthorized();
    }
}
