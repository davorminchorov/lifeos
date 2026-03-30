<?php

namespace Tests\Feature;

use App\Jobs\ImportExpensesCsv;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportExpensesCsvTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_form_requires_authentication(): void
    {
        $response = $this->get('/expenses/import');

        $response->assertRedirect('/login');
    }

    public function test_import_form_renders_for_authenticated_user(): void
    {
        $this->setupTenantContext();

        $response = $this->get('/expenses/import');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Expenses/Import'));
    }

    public function test_import_requires_a_file(): void
    {
        $this->setupTenantContext();

        $response = $this->post('/expenses/import', []);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_rejects_non_csv_file(): void
    {
        $this->setupTenantContext();

        $file = UploadedFile::fake()->create('expenses.pdf', 100, 'application/pdf');

        $response = $this->post('/expenses/import', ['file' => $file]);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_rejects_file_exceeding_max_size(): void
    {
        $this->setupTenantContext();

        $file = UploadedFile::fake()->create('expenses.csv', 11000, 'text/csv');

        $response = $this->post('/expenses/import', ['file' => $file]);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_dispatches_job_on_imports_queue(): void
    {
        Queue::fake();
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = "expense_date,amount,category,description\n2026-03-15,25.50,Food & Dining,Lunch";
        $file = UploadedFile::fake()->createWithContent('expenses.csv', $csvContent);

        $response = $this->post('/expenses/import', ['file' => $file]);

        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHas('success');

        Queue::assertPushedOn('imports', ImportExpensesCsv::class, function ($job) use ($user, $tenant) {
            return $job->userId === $user->id && $job->tenantId === $tenant->id;
        });
    }

    public function test_job_creates_expenses_from_valid_csv(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = implode("\n", [
            'expense_date,amount,currency,category,description,merchant,payment_method,expense_type',
            '2026-03-15,25.50,MKD,Food & Dining,Lunch at cafe,Cafe Central,card,personal',
            '2026-03-16,1200.00,MKD,Transportation,Monthly bus pass,City Transit,cash,personal',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_expenses.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportExpensesCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('expenses', 2);

        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'amount' => 25.50,
            'currency' => 'MKD',
            'category' => 'Food & Dining',
            'description' => 'Lunch at cafe',
            'merchant' => 'Cafe Central',
            'payment_method' => 'card',
            'expense_type' => 'personal',
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'amount' => 1200.00,
            'category' => 'Transportation',
            'description' => 'Monthly bus pass',
        ]);

        Storage::assertMissing($storedPath);
    }

    public function test_job_skips_duplicate_rows_via_unique_key(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = implode("\n", [
            'expense_date,amount,category,description,merchant',
            '2026-03-15,25.50,Food & Dining,Lunch at cafe,Cafe Central',
        ]);

        $uniqueKey = 'csv-import:'.md5('2026-03-1525.50Cafe CentralLunch at cafe');

        Expense::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'amount' => 25.50,
            'currency' => 'MKD',
            'category' => 'Food & Dining',
            'description' => 'Lunch at cafe',
            'merchant' => 'Cafe Central',
            'expense_date' => '2026-03-15',
            'status' => 'confirmed',
            'unique_key' => $uniqueKey,
        ]);

        $storedPath = 'imports/'.$user->id.'/test_expenses.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportExpensesCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('expenses', 1);
    }

    public function test_job_skips_rows_missing_required_fields(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = implode("\n", [
            'expense_date,amount,category,description',
            '2026-03-15,,Food & Dining,Lunch at cafe',   // missing amount
            ',25.50,Food & Dining,Lunch at cafe',         // missing date
            '2026-03-15,25.50,,Lunch at cafe',            // missing category
            '2026-03-15,25.50,Food & Dining,',            // missing description
            '2026-03-15,30.00,Shopping,New book',          // valid row
        ]);

        $storedPath = 'imports/'.$user->id.'/test_expenses.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportExpensesCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('expenses', 1);
        $this->assertDatabaseHas('expenses', [
            'description' => 'New book',
        ]);
    }

    public function test_job_applies_defaults_for_optional_columns(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = implode("\n", [
            'expense_date,amount,category,description',
            '2026-03-15,25.50,Food & Dining,Lunch at cafe',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_expenses.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportExpensesCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $expense = Expense::first();
        $this->assertEquals('MKD', $expense->currency);
        $this->assertEquals('personal', $expense->expense_type);
        $this->assertFalse($expense->is_tax_deductible);
        $this->assertFalse($expense->is_recurring);
        $this->assertEquals('confirmed', $expense->status);
        $this->assertNull($expense->merchant);
        $this->assertNull($expense->payment_method);
        $this->assertNull($expense->notes);
    }

    public function test_job_supports_header_aliases(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = implode("\n", [
            'date,total,type,memo,vendor',
            '2026-03-15,25.50,Food & Dining,Lunch at cafe,Cafe Central',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_expenses.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportExpensesCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('expenses', 1);
        $this->assertDatabaseHas('expenses', [
            'amount' => 25.50,
            'category' => 'Food & Dining',
            'description' => 'Lunch at cafe',
            'merchant' => 'Cafe Central',
        ]);
    }

    public function test_job_parses_tags_from_comma_separated_string(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = "expense_date,amount,category,description,tags\n2026-03-15,25.50,Food & Dining,Lunch,\"lunch,work,daily\"";

        $storedPath = 'imports/'.$user->id.'/test_expenses.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportExpensesCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $expense = Expense::first();
        $this->assertEquals(['lunch', 'work', 'daily'], $expense->tags);
    }

    public function test_job_handles_empty_csv_gracefully(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $storedPath = 'imports/'.$user->id.'/test_expenses.csv';
        Storage::put($storedPath, '');

        $job = new ImportExpensesCsv($user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_job_handles_missing_file_gracefully(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $job = new ImportExpensesCsv($user->id, $tenant->id, 'imports/nonexistent.csv');
        $job->handle();

        $this->assertDatabaseCount('expenses', 0);
    }
}
