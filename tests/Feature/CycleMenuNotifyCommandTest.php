<?php

use App\Enums\MealType;
use App\Jobs\SendCycleMenuDailyNotifications;
use App\Models\CycleMenu;
use App\Models\CycleMenuDay;
use App\Models\CycleMenuItem;
use App\Models\User;
use App\Notifications\DailyMenuNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function seedActiveMenuForToday(): CycleMenu
{
    $menu = CycleMenu::factory()->active()->create([
        'cycle_length_days' => 7,
        'starts_on' => now()->toDateString(),
        'name' => 'Test Active Menu',
    ]);

    for ($i = 0; $i < 7; $i++) {
        CycleMenuDay::firstOrCreate([
            'cycle_menu_id' => $menu->id,
            'day_index' => $i,
        ]);
    }

    $todayDay = $menu->days()->where('day_index', 0)->first();

    CycleMenuItem::create([
        'cycle_menu_day_id' => $todayDay->id,
        'title' => 'Oatmeal with berries',
        'meal_type' => MealType::Breakfast->value,
        'time_of_day' => '08:00:00',
        'quantity' => '1 bowl',
        'position' => 0,
    ]);

    CycleMenuItem::create([
        'cycle_menu_day_id' => $todayDay->id,
        'title' => 'Chicken salad wrap',
        'meal_type' => MealType::Lunch->value,
        'time_of_day' => '12:30:00',
        'quantity' => '1 wrap',
        'position' => 1,
    ]);

    return $menu->refresh();
}

it('cycle-menus:notify-today --dry-run outputs payload and does not send notifications', function () {
    // Given an active menu and at least one user
    User::factory()->create();
    $menu = seedActiveMenuForToday();

    // When
    Notification::fake();
    $this->artisan('cycle-menus:notify-today --dry-run')
        ->expectsOutputToContain('DRY RUN:')
        ->assertExitCode(0);

    // Then no notification actually sent
    Notification::assertNothingSent();
});

it('cycle-menus:notify-today sends DailyMenuNotification to all users when not dry run', function () {
    // Given two users
    $u1 = User::factory()->create();
    $u2 = User::factory()->create();
    $menu = seedActiveMenuForToday();

    Notification::fake();

    // When
    $this->artisan('cycle-menus:notify-today')
        ->expectsOutputToContain('✅ Cycle menu daily notifications processed')
        ->assertExitCode(0);

    // Then
    Notification::assertSentTo([$u1, $u2], DailyMenuNotification::class, function (DailyMenuNotification $notification, array $channels) use ($menu, $u1) {
        $data = $notification->toArray($u1);
        return ($channels === ['database'])
            && ($data['type'] ?? null) === 'cycle_menu_daily'
            && ($data['menu_id'] ?? null) === $menu->id
            && is_array($data['items'] ?? null)
            && Str::contains($data['message'] ?? '', 'Today\'s menu');
    });
});

it('cycle-menus:notify-today --dispatch-job dispatches job to queue', function () {
    // Given
    User::factory()->create();
    seedActiveMenuForToday();

    Bus::fake();

    // When
    $this->artisan('cycle-menus:notify-today --dispatch-job')
        ->expectsOutputToContain('📤 Cycle menu daily notification job dispatched to queue')
        ->assertExitCode(0);

    // Then
    Bus::assertDispatched(SendCycleMenuDailyNotifications::class);
});

it('SendCycleMenuDailyNotifications job sends notifications to all users', function () {
    // Given two users and an active menu
    $u1 = User::factory()->create();
    $u2 = User::factory()->create();
    $menu = seedActiveMenuForToday();

    Notification::fake();

    // When
    $job = new SendCycleMenuDailyNotifications();
    $job->handle();

    // Then
    Notification::assertSentTo([$u1, $u2], DailyMenuNotification::class, function (DailyMenuNotification $notification, array $channels) use ($menu, $u1) {
        $data = $notification->toArray($u1);
        return ($channels === ['database'])
            && ($data['type'] ?? null) === 'cycle_menu_daily'
            && ($data['menu_id'] ?? null) === $menu->id
            && is_array($data['items'] ?? null)
            && Str::contains($data['message'] ?? '', 'Today\'s menu');
    });
});
