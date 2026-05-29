<?php

namespace Tests\Feature;

use App\Models\DataExport;
use App\Models\Expense;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\DataExportReady;
use App\Scopes\TenantScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class DataExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_queue_a_single_module_export(): void
    {
        Storage::fake('local');
        Notification::fake();

        ['user' => $user] = $this->setupTenantContext();
        Expense::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->post('/exports', ['modules' => ['expenses']]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $export = DataExport::withoutGlobalScope(TenantScope::class)->firstOrFail();
        $this->assertSame(DataExport::STATUS_COMPLETED, $export->status);
        $this->assertStringEndsWith('.csv', $export->file_name);
        $this->assertSame(['expenses' => 3], $export->row_counts);
        Storage::disk('local')->assertExists($export->file_path);

        Notification::assertSentTo($user, DataExportReady::class);
    }

    public function test_export_only_includes_current_tenant_and_user_data(): void
    {
        Storage::fake('local');
        Notification::fake();

        // Data belonging to a different tenant/user must never leak.
        $otherUser = User::factory()->create();
        $otherTenant = Tenant::factory()->create(['owner_id' => $otherUser->id]);
        $otherUser->update(['current_tenant_id' => $otherTenant->id]);
        $this->actingAs($otherUser);
        Expense::factory()->create([
            'user_id' => $otherUser->id,
            'description' => 'OTHER-TENANT-SECRET',
        ]);

        ['user' => $user] = $this->setupTenantContext();
        Expense::factory()->create([
            'user_id' => $user->id,
            'description' => 'MINE-VISIBLE',
        ]);

        $this->post('/exports', ['modules' => ['expenses']])->assertRedirect();

        $export = DataExport::withoutGlobalScope(TenantScope::class)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $contents = Storage::disk('local')->get($export->file_path);

        $this->assertStringContainsString('MINE-VISIBLE', $contents);
        $this->assertStringNotContainsString('OTHER-TENANT-SECRET', $contents);
        $this->assertSame(['expenses' => 1], $export->row_counts);
    }

    public function test_exporting_multiple_modules_produces_a_zip_archive(): void
    {
        Storage::fake('local');
        Notification::fake();

        ['user' => $user] = $this->setupTenantContext();
        Expense::factory()->count(2)->create(['user_id' => $user->id]);
        Subscription::factory()->count(2)->create(['user_id' => $user->id]);

        $this->post('/exports', ['modules' => ['expenses', 'subscriptions']])->assertRedirect();

        $export = DataExport::withoutGlobalScope(TenantScope::class)->firstOrFail();

        $this->assertStringEndsWith('.zip', $export->file_name);
        $this->assertSame(['expenses' => 2, 'subscriptions' => 2], $export->row_counts);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open(Storage::disk('local')->path($export->file_path)) === true);
        $names = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $names[] = $zip->getNameIndex($i);
        }
        $zip->close();

        $this->assertEqualsCanonicalizing(['expenses.csv', 'subscriptions.csv'], $names);
    }

    public function test_store_rejects_unknown_module(): void
    {
        $this->setupTenantContext();

        $this->post('/exports', ['modules' => ['not-a-real-module']])
            ->assertSessionHasErrors('modules.0');

        $this->assertDatabaseCount('data_exports', 0);
    }

    public function test_store_requires_at_least_one_module(): void
    {
        $this->setupTenantContext();

        $this->post('/exports', ['modules' => []])
            ->assertSessionHasErrors('modules');
    }

    public function test_owner_can_download_a_completed_export(): void
    {
        Storage::fake('local');
        Notification::fake();

        ['user' => $user] = $this->setupTenantContext();
        Expense::factory()->create(['user_id' => $user->id]);

        $this->post('/exports', ['modules' => ['expenses']])->assertRedirect();
        $export = DataExport::withoutGlobalScope(TenantScope::class)->firstOrFail();

        $this->get("/exports/{$export->id}/download")
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename='.$export->file_name);
    }

    public function test_user_cannot_download_another_users_export(): void
    {
        Storage::fake('local');
        Notification::fake();

        // Owner in a shared tenant creates an export.
        ['user' => $owner, 'tenant' => $tenant] = $this->setupTenantContext();
        Expense::factory()->create(['user_id' => $owner->id]);
        $this->post('/exports', ['modules' => ['expenses']])->assertRedirect();
        $export = DataExport::withoutGlobalScope(TenantScope::class)->firstOrFail();

        // A different member of the same tenant must not access it.
        $intruder = User::factory()->create(['current_tenant_id' => $tenant->id]);
        $tenant->members()->attach($intruder->id, ['role' => 'member']);
        $this->actingAs($intruder);

        $this->get("/exports/{$export->id}/download")->assertForbidden();
    }

    public function test_owner_can_delete_an_export(): void
    {
        Storage::fake('local');
        Notification::fake();

        ['user' => $user] = $this->setupTenantContext();
        Expense::factory()->create(['user_id' => $user->id]);
        $this->post('/exports', ['modules' => ['expenses']])->assertRedirect();
        $export = DataExport::withoutGlobalScope(TenantScope::class)->firstOrFail();
        $filePath = $export->file_path;

        $this->delete("/exports/{$export->id}")->assertRedirect();

        $this->assertModelMissing($export);
        Storage::disk('local')->assertMissing($filePath);
    }

    public function test_prune_command_removes_expired_exports(): void
    {
        Storage::fake('local');
        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        Storage::disk('local')->put('exports/expired.csv', 'data');
        $expired = DataExport::factory()->completed()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'file_path' => 'exports/expired.csv',
            'expires_at' => now()->subDay(),
        ]);

        $fresh = DataExport::factory()->completed()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'expires_at' => now()->addDay(),
        ]);

        $this->artisan('exports:prune')->assertSuccessful();

        $this->assertModelMissing($expired);
        $this->assertModelExists($fresh);
        Storage::disk('local')->assertMissing('exports/expired.csv');
    }
}
