<?php

namespace App\Jobs;

use App\Models\CycleMenu;
use App\Models\CycleMenuDay;
use App\Models\User;
use App\Notifications\DailyMenuNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SendCycleMenuDailyNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting cycle menu daily notification job');

        $tz = config('app.timezone');
        $today = Carbon::now($tz)->startOfDay();

        $menus = CycleMenu::query()->active()->get();
        if ($menus->isEmpty()) {
            Log::info('No active cycle menus found');
            return;
        }

        $users = User::query()->get();
        if ($users->isEmpty()) {
            Log::info('No users to notify');
            return;
        }

        $notificationCount = 0;

        foreach ($menus as $menu) {
            if (empty($menu->cycle_length_days) || $menu->cycle_length_days < 1) {
                continue;
            }

            $startsOn = $menu->starts_on ? Carbon::parse($menu->starts_on, $tz)->startOfDay() : $today;
            $daysDiff = $startsOn->diffInDays($today, false);
            $index = $daysDiff >= 0
                ? $daysDiff % $menu->cycle_length_days
                : (($menu->cycle_length_days + ($daysDiff % $menu->cycle_length_days)) % $menu->cycle_length_days);

            /** @var CycleMenuDay|null $day */
            $day = CycleMenuDay::query()
                ->where('cycle_menu_id', $menu->id)
                ->where('day_index', $index)
                ->with(['items' => function ($q) {
                    $q->orderBy('position');
                }])
                ->first();

            $itemsPayload = [];
            if ($day) {
                foreach ($day->items as $item) {
                    $parts = [ucfirst($item->meal_type->value), $item->title];
                    $meta = [];
                    if ($item->quantity) {
                        $meta[] = $item->quantity;
                    }
                    if ($item->time_of_day) {
                        try {
                            $meta[] = Carbon::createFromFormat('H:i:s', $item->time_of_day)->format('H:i');
                        } catch (\Throwable) {
                            // ignore bad time format
                        }
                    }
                    if ($meta) {
                        $parts[] = '(' . implode(', ', $meta) . ')';
                    }

                    $itemsPayload[] = [
                        'id' => $item->id,
                        'display' => implode(' • ', $parts),
                    ];
                }
            }

            $url = route('cycle-menus.show', $menu);
            $payload = [
                'type' => 'cycle_menu_daily',
                'menu_id' => $menu->id,
                'menu_name' => $menu->name,
                'day_index' => $index,
                'items' => $itemsPayload,
                'url' => $url,
                'message' => 'Today\'s menu (' . $menu->name . ')',
            ];

            foreach ($users as $user) {
                try {
                    $user->notify(new DailyMenuNotification($payload));
                    $notificationCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to send cycle menu notification to user {$user->id}: {$e->getMessage()}");
                }
            }

            Log::info("Notified users for menu '{$menu->name}' (day index {$index})");
        }

        Log::info("Completed cycle menu daily notification job. Sent {$notificationCount} notifications.");
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Cycle menu daily notification job failed: '.$exception->getMessage());
    }
}
