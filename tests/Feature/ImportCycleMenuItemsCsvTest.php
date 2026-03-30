<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\ImportCycleMenuItemsCsv;
use App\Models\CycleMenu;
use App\Models\CycleMenuDay;
use App\Models\CycleMenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportCycleMenuItemsCsvTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_form_requires_authentication(): void
    {
        $response = $this->get('/cycle-menu-items/import');

        $response->assertRedirect('/login');
    }

    public function test_import_form_renders_for_authenticated_user(): void
    {
        $this->setupTenantContext();

        $response = $this->get('/cycle-menu-items/import');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('CycleMenus/Import'));
    }

    public function test_import_requires_a_file(): void
    {
        ['tenant' => $tenant] = $this->setupTenantContext();

        $menu = CycleMenu::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->post('/cycle-menu-items/import', ['cycle_menu_id' => $menu->id]);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_requires_cycle_menu_id(): void
    {
        $this->setupTenantContext();

        $csvContent = "day_index,meal_type,title\n1,Breakfast,Oatmeal";
        $file = UploadedFile::fake()->createWithContent('items.csv', $csvContent);

        $response = $this->post('/cycle-menu-items/import', ['file' => $file]);

        $response->assertSessionHasErrors('cycle_menu_id');
    }

    public function test_import_rejects_non_csv_file(): void
    {
        ['tenant' => $tenant] = $this->setupTenantContext();

        $menu = CycleMenu::factory()->create(['tenant_id' => $tenant->id]);
        $file = UploadedFile::fake()->create('items.pdf', 100, 'application/pdf');

        $response = $this->post('/cycle-menu-items/import', [
            'file' => $file,
            'cycle_menu_id' => $menu->id,
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_rejects_file_exceeding_max_size(): void
    {
        ['tenant' => $tenant] = $this->setupTenantContext();

        $menu = CycleMenu::factory()->create(['tenant_id' => $tenant->id]);
        $file = UploadedFile::fake()->create('items.csv', 11000, 'text/csv');

        $response = $this->post('/cycle-menu-items/import', [
            'file' => $file,
            'cycle_menu_id' => $menu->id,
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_dispatches_job_on_imports_queue(): void
    {
        Queue::fake();
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $menu = CycleMenu::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
        ]);

        $csvContent = "day_index,meal_type,title\n1,Breakfast,Oatmeal";
        $file = UploadedFile::fake()->createWithContent('items.csv', $csvContent);

        $response = $this->postJson('/cycle-menu-items/import', [
            'file' => $file,
            'cycle_menu_id' => $menu->id,
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'queued']);

        Queue::assertPushedOn('imports', ImportCycleMenuItemsCsv::class, function ($job) use ($user, $tenant, $menu) {
            return $job->userId === $user->id
                && $job->tenantId === $tenant->id
                && $job->cycleMenuId === $menu->id;
        });
    }

    public function test_job_creates_items_from_valid_csv(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $menu = CycleMenu::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'cycle_length_days' => 7,
        ]);

        $csvContent = implode("\n", [
            'day_index,meal_type,title,time_of_day,quantity,notes',
            '1,Breakfast,Oatmeal with berries,08:00,1 serving,High fiber',
            '1,Lunch,Grilled chicken salad,12:30,1 serving,',
            '2,Breakfast,Scrambled eggs,08:00,2 servings,With toast',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_items.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportCycleMenuItemsCsv($menu->id, $user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('cycle_menu_items', 3);

        // Day 1 (day_index=0) should exist with 2 items
        $day0 = CycleMenuDay::where('cycle_menu_id', $menu->id)->where('day_index', 0)->first();
        $this->assertNotNull($day0);
        $this->assertEquals(2, CycleMenuItem::where('cycle_menu_day_id', $day0->id)->count());

        // Day 2 (day_index=1) should exist with 1 item
        $day1 = CycleMenuDay::where('cycle_menu_id', $menu->id)->where('day_index', 1)->first();
        $this->assertNotNull($day1);
        $this->assertEquals(1, CycleMenuItem::where('cycle_menu_day_id', $day1->id)->count());

        $this->assertDatabaseHas('cycle_menu_items', [
            'cycle_menu_day_id' => $day0->id,
            'title' => 'Oatmeal with berries',
            'meal_type' => 'breakfast',
            'time_of_day' => '08:00',
            'quantity' => '1 serving',
        ]);

        $this->assertDatabaseHas('cycle_menu_items', [
            'cycle_menu_day_id' => $day1->id,
            'title' => 'Scrambled eggs',
            'meal_type' => 'breakfast',
        ]);

        Storage::assertMissing($storedPath);
    }

    public function test_job_skips_duplicate_rows(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $menu = CycleMenu::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
        ]);

        $day = CycleMenuDay::factory()->create([
            'cycle_menu_id' => $menu->id,
            'day_index' => 0,
            'tenant_id' => $tenant->id,
        ]);

        CycleMenuItem::factory()->create([
            'cycle_menu_day_id' => $day->id,
            'title' => 'Oatmeal with berries',
            'meal_type' => 'breakfast',
            'tenant_id' => $tenant->id,
        ]);

        $csvContent = implode("\n", [
            'day_index,meal_type,title',
            '1,Breakfast,Oatmeal with berries',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_items.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportCycleMenuItemsCsv($menu->id, $user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('cycle_menu_items', 1);
    }

    public function test_job_skips_rows_missing_required_fields(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $menu = CycleMenu::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
        ]);

        $csvContent = implode("\n", [
            'day_index,meal_type,title',
            ',Breakfast,Oatmeal',        // missing day_index
            '1,,Oatmeal',                // missing meal_type
            '1,Breakfast,',              // missing title
            '1,Breakfast,Valid item',    // valid row
        ]);

        $storedPath = 'imports/'.$user->id.'/test_items.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportCycleMenuItemsCsv($menu->id, $user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('cycle_menu_items', 1);
        $this->assertDatabaseHas('cycle_menu_items', [
            'title' => 'Valid item',
        ]);
    }

    public function test_job_skips_invalid_meal_type(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $menu = CycleMenu::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
        ]);

        $csvContent = implode("\n", [
            'day_index,meal_type,title',
            '1,InvalidMeal,Oatmeal',
            '1,Breakfast,Valid item',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_items.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportCycleMenuItemsCsv($menu->id, $user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('cycle_menu_items', 1);
        $this->assertDatabaseHas('cycle_menu_items', ['title' => 'Valid item']);
    }

    public function test_job_supports_header_aliases(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $menu = CycleMenu::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
        ]);

        $csvContent = implode("\n", [
            'day,meal,name,time,qty',
            '1,Breakfast,Oatmeal with berries,08:00,1 serving',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_items.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportCycleMenuItemsCsv($menu->id, $user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('cycle_menu_items', 1);
        $this->assertDatabaseHas('cycle_menu_items', [
            'title' => 'Oatmeal with berries',
            'meal_type' => 'breakfast',
            'time_of_day' => '08:00',
            'quantity' => '1 serving',
        ]);
    }

    public function test_job_finds_or_creates_cycle_menu_days(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $menu = CycleMenu::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'cycle_length_days' => 7,
        ]);

        // Pre-create day 0
        CycleMenuDay::factory()->create([
            'cycle_menu_id' => $menu->id,
            'day_index' => 0,
            'tenant_id' => $tenant->id,
        ]);

        $csvContent = implode("\n", [
            'day_index,meal_type,title',
            '1,Breakfast,Day 1 item',
            '3,Lunch,Day 3 item',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_items.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportCycleMenuItemsCsv($menu->id, $user->id, $tenant->id, $storedPath);
        $job->handle();

        // Day 0 already existed, day 2 should be created for CSV day_index=3
        $this->assertDatabaseHas('cycle_menu_days', [
            'cycle_menu_id' => $menu->id,
            'day_index' => 0,
        ]);
        $this->assertDatabaseHas('cycle_menu_days', [
            'cycle_menu_id' => $menu->id,
            'day_index' => 2,
        ]);
        $this->assertDatabaseCount('cycle_menu_items', 2);
    }

    public function test_job_handles_empty_csv_gracefully(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $menu = CycleMenu::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
        ]);

        $storedPath = 'imports/'.$user->id.'/test_items.csv';
        Storage::put($storedPath, '');

        $job = new ImportCycleMenuItemsCsv($menu->id, $user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('cycle_menu_items', 0);
    }

    public function test_job_handles_missing_file_gracefully(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $menu = CycleMenu::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
        ]);

        $job = new ImportCycleMenuItemsCsv($menu->id, $user->id, $tenant->id, 'imports/nonexistent.csv');
        $job->handle();

        $this->assertDatabaseCount('cycle_menu_items', 0);
    }

    public function test_job_writes_progress_to_cache(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $menu = CycleMenu::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
        ]);

        $csvContent = implode("\n", [
            'day_index,meal_type,title',
            '1,Breakfast,Oatmeal',
            '1,Lunch,Salad',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_items.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportCycleMenuItemsCsv($menu->id, $user->id, $tenant->id, $storedPath);
        $job->handle();

        $progress = Cache::get('cycle_menu_items_import_progress:'.$user->id);
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

        Cache::put('cycle_menu_items_import_progress:'.$user->id, [
            'status' => 'processing',
            'total' => 10,
            'created' => 5,
            'skipped' => 1,
            'failed' => 0,
        ], 300);

        $response = $this->getJson('/cycle-menu-items/import/progress');

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

        $response = $this->getJson('/cycle-menu-items/import/progress');

        $response->assertOk();
        $response->assertJson(['status' => 'idle']);
    }

    public function test_progress_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/cycle-menu-items/import/progress');

        $response->assertUnauthorized();
    }

    public function test_job_auto_assigns_position_when_not_provided(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $menu = CycleMenu::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
        ]);

        $csvContent = implode("\n", [
            'day_index,meal_type,title',
            '1,Breakfast,First item',
            '1,Lunch,Second item',
            '1,Dinner,Third item',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_items.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportCycleMenuItemsCsv($menu->id, $user->id, $tenant->id, $storedPath);
        $job->handle();

        $items = CycleMenuItem::orderBy('position')->get();
        $this->assertCount(3, $items);
        $this->assertEquals(0, $items[0]->position);
        $this->assertEquals(1, $items[1]->position);
        $this->assertEquals(2, $items[2]->position);
    }

    public function test_job_handles_nonexistent_cycle_menu(): void
    {
        Storage::fake();

        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();

        $csvContent = implode("\n", [
            'day_index,meal_type,title',
            '1,Breakfast,Oatmeal',
        ]);

        $storedPath = 'imports/'.$user->id.'/test_items.csv';
        Storage::put($storedPath, $csvContent);

        $job = new ImportCycleMenuItemsCsv(99999, $user->id, $tenant->id, $storedPath);
        $job->handle();

        $this->assertDatabaseCount('cycle_menu_items', 0);

        $progress = Cache::get('cycle_menu_items_import_progress:'.$user->id);
        $this->assertNotNull($progress);
        $this->assertEquals('failed', $progress['status']);
    }
}
