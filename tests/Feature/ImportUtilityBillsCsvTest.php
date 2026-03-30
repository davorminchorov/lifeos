<?php

namespace Tests\Feature;

use App\Jobs\ImportUtilityBillsCsv;
use App\Models\UtilityBill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportUtilityBillsCsvTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_form_requires_authentication(): void
    {
        $response = $this->get('/utility-bills/import');

        $response->assertRedirect('/login');
    }

    public function test_import_form_renders_for_authenticated_user(): void
    {
        $this->setupTenantContext();

        $response = $this->get('/utility-bills/import');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('UtilityBills/Import'));
    }

    public function test_import_requires_a_file(): void
    {
        $this->setupTenantContext();

        $response = $this->post('/utility-bills/import', []);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_rejects_non_csv_file(): void
    {
        $this->setupTenantContext();

        $file = UploadedFile::fake()->create('bills.pdf', 100, 'application/pdf');

        $response = $this->post('/utility-bills/import', ['file' => $file]);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_rejects_file_exceeding_max_size(): void
    {
        $this->setupTenantContext();

        $file = UploadedFile::fake()->create('bills.csv', 11000, 'text/csv');

        $response = $this->post('/utility-bills/import', ['file' => $file]);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_dispatches_job_on_imports_queue(): void
    {
        Queue::fake();
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = "utility_type,service_provider,bill_amount,due_date\nelectricity,PG&E,125.50,2026-04-15";
        $file = UploadedFile::fake()->createWithContent('bills.csv', $csvContent);

        $response = $this->postJson('/utility-bills/import', ['file' => $file]);

        $response->assertOk();
        $response->assertJson(['status' => 'queued']);

        Queue::assertPushedOn('imports', ImportUtilityBillsCsv::class, function ($job) use ($user, $tenant) {
            return $job->userId === $user->id && $job->tenantId === $tenant->id;
        });
    }

    public function test_job_creates_utility_bills_from_valid_csv(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = implode("\n", [
            'utility_type,service_provider,bill_amount,currency,due_date,bill_period_start,bill_period_end,payment_status,auto_pay_enabled',
            'electricity,PG&E,125.50,MKD,2026-04-15,2026-03-01,2026-03-31,pending,false',
            'water,City Water,45.00,MKD,2026-04-20,2026-03-01,2026-03-31,paid,true',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_bills.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportUtilityBillsCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('utility_bills', 2);

        $this->assertDatabaseHas('utility_bills', [
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'utility_type' => 'electricity',
            'service_provider' => 'PG&E',
            'bill_amount' => 125.50,
            'currency' => 'MKD',
            'payment_status' => 'pending',
        ]);

        $this->assertDatabaseHas('utility_bills', [
            'user_id' => $user->id,
            'utility_type' => 'water',
            'service_provider' => 'City Water',
            'bill_amount' => 45.00,
            'payment_status' => 'paid',
        ]);

        Storage::assertMissing($storedPath);
    }

    public function test_job_skips_duplicate_rows_via_unique_key(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = implode("\n", [
            'utility_type,service_provider,bill_amount,due_date,bill_period_start',
            'electricity,PG&E,125.50,2026-04-15,2026-03-01',
        ]);

        $uniqueKey = 'csv-import:'.md5('PG&E2026-03-01125.50electricity');

        UtilityBill::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'utility_type' => 'electricity',
            'service_provider' => 'PG&E',
            'account_number' => '',
            'service_address' => '',
            'bill_amount' => 125.50,
            'currency' => 'MKD',
            'due_date' => '2026-04-15',
            'bill_period_start' => '2026-03-01',
            'bill_period_end' => '2026-03-31',
            'payment_status' => 'pending',
            'unique_key' => $uniqueKey,
        ]);

        $storedPath = 'imports/'.$user->id.'/test_bills.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportUtilityBillsCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('utility_bills', 1);
    }

    public function test_job_skips_duplicate_rows_matching_manually_created_bills(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = implode("\n", [
            'utility_type,service_provider,bill_amount,due_date',
            'electricity,PG&E,125.50,2026-04-15',
        ]);

        UtilityBill::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'utility_type' => 'electricity',
            'service_provider' => 'PG&E',
            'account_number' => '',
            'service_address' => '',
            'bill_amount' => 125.50,
            'currency' => 'MKD',
            'due_date' => '2026-04-15',
            'bill_period_start' => '2026-03-01',
            'bill_period_end' => '2026-03-31',
            'payment_status' => 'pending',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_bills.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportUtilityBillsCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('utility_bills', 1);
    }

    public function test_job_skips_rows_missing_required_fields(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = implode("\n", [
            'utility_type,service_provider,bill_amount,due_date',
            ',PG&E,125.50,2026-04-15',                       // missing utility_type
            'electricity,,125.50,2026-04-15',                  // missing service_provider
            'electricity,PG&E,,2026-04-15',                    // missing bill_amount
            'electricity,PG&E,125.50,',                        // missing due_date
            'water,City Water,45.00,2026-04-20',               // valid row
        ]);

        $storedPath = 'imports/'.$user->id.'/test_bills.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportUtilityBillsCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('utility_bills', 1);
        $this->assertDatabaseHas('utility_bills', [
            'utility_type' => 'water',
            'service_provider' => 'City Water',
        ]);
    }

    public function test_job_applies_defaults_for_optional_columns(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = implode("\n", [
            'utility_type,service_provider,bill_amount,due_date',
            'electricity,PG&E,125.50,2026-04-15',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_bills.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportUtilityBillsCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $bill = UtilityBill::first();
        $this->assertNotNull($bill);
        $this->assertEquals('MKD', $bill->currency);
        $this->assertEquals('pending', $bill->payment_status);
        $this->assertFalse($bill->auto_pay_enabled);
        $this->assertEquals('', $bill->account_number);
        $this->assertEquals('', $bill->service_address);
        $this->assertNull($bill->usage_amount);
        $this->assertNull($bill->notes);
    }

    public function test_job_supports_header_aliases(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = implode("\n", [
            'type,provider,amount,due',
            'electricity,PG&E,125.50,2026-04-15',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_bills.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportUtilityBillsCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('utility_bills', 1);
        $this->assertDatabaseHas('utility_bills', [
            'utility_type' => 'electricity',
            'service_provider' => 'PG&E',
            'bill_amount' => 125.50,
        ]);
    }

    public function test_job_handles_empty_csv_gracefully(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $storedPath = 'imports/'.$user->id.'/test_bills.csv';
        Storage::put($storedPath, '');

        $job = new ImportUtilityBillsCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('utility_bills', 0);
    }

    public function test_job_handles_missing_file_gracefully(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $job = new ImportUtilityBillsCsv($user->id, $tenant->id, 'imports/nonexistent.csv');
        $job->handle();

        $this->assertDatabaseCount('utility_bills', 0);
    }

    public function test_job_writes_progress_to_cache(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = implode("\n", [
            'utility_type,service_provider,bill_amount,due_date',
            'electricity,PG&E,125.50,2026-04-15',
            'water,City Water,45.00,2026-04-20',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_bills.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportUtilityBillsCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $progress = Cache::get('utility_bill_import_progress:'.$user->id);
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

        Cache::put('utility_bill_import_progress:'.$user->id, [
            'status' => 'processing',
            'total' => 10,
            'created' => 5,
            'skipped' => 1,
            'failed' => 0,
        ], 300);

        $response = $this->getJson('/utility-bills/import/progress');

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

        $response = $this->getJson('/utility-bills/import/progress');

        $response->assertOk();
        $response->assertJson(['status' => 'idle']);
    }

    public function test_progress_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/utility-bills/import/progress');

        $response->assertUnauthorized();
    }
}
