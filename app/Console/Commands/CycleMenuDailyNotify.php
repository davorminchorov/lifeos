<?php

namespace App\Console\Commands;

use App\Models\CycleMenu;
use App\Models\CycleMenuDay;
use App\Notifications\DailyMenuNotification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CycleMenuDailyNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cycle-menus:notify-today {--dry-run : Output the message instead of notifying users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a 09:00 notification with today\'s cycle menu items for active menus';

    public function handle(): int
    {
        $tz = config('app.timezone');
        $today = Carbon::now($tz)->startOfDay();

        $menus = CycleMenu::query()->active()->get();
        if ($menus->isEmpty()) {
            $this->info('No active cycle menus.');
            return self::SUCCESS;
        }

        $users = User::query()->get();
        if ($users->isEmpty()) {
            $this->info('No users to notify.');
            return self::SUCCESS;
        }

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

            if ($this->option('dry-run')) {
                $this->line('DRY RUN: ' . json_encode($payload));
                continue;
            }

            foreach ($users as $user) {
                $user->notify(new DailyMenuNotification($payload));
            }

            $this->info("Notified users for menu '{$menu->name}' (day index {$index}).");
        }

        return self::SUCCESS;
    }
}
