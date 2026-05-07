<?php

declare(strict_types=1);

namespace App\Services\CycleMenu;

use App\Enums\MealType;
use App\Models\CycleMenu;
use App\Models\CycleMenuDay;
use App\Models\CycleMenuItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CycleMenuService
{
    /**
     * Add a single item to a specific day of a cycle menu, creating the
     * CycleMenuDay row if it doesn't exist. Returns the created item.
     *
     * @param  array<string, mixed>  $data
     */
    public function addItem(CycleMenu $menu, int $dayIndex, array $data): CycleMenuItem
    {
        $this->guardDayIndex($menu, $dayIndex);

        $day = $this->findOrCreateDay($menu, $dayIndex);
        $maxPosition = (int) (CycleMenuItem::query()
            ->where('cycle_menu_day_id', $day->id)
            ->max('position') ?? -1);

        return CycleMenuItem::create([
            'cycle_menu_day_id' => $day->id,
            'title' => (string) $data['title'],
            'meal_type' => $data['meal_type'] ?? MealType::Other,
            'time_of_day' => $data['time_of_day'] ?? null,
            'quantity' => $data['quantity'] ?? null,
            'position' => array_key_exists('position', $data) ? (int) $data['position'] : $maxPosition + 1,
        ]);
    }

    /**
     * Replace the entire content of one day with the supplied items. Existing
     * items on the day are deleted in the same transaction. Returns the
     * created items.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, CycleMenuItem>
     */
    public function replaceDay(CycleMenu $menu, int $dayIndex, array $items): array
    {
        $this->guardDayIndex($menu, $dayIndex);

        return DB::transaction(function () use ($menu, $dayIndex, $items): array {
            $day = $this->findOrCreateDay($menu, $dayIndex);

            CycleMenuItem::query()->where('cycle_menu_day_id', $day->id)->delete();

            $created = [];
            $position = 0;

            foreach ($items as $itemData) {
                $row = (array) $itemData;
                $row['position'] = $row['position'] ?? $position++;
                $created[] = CycleMenuItem::create([
                    'cycle_menu_day_id' => $day->id,
                    'title' => (string) $row['title'],
                    'meal_type' => $row['meal_type'] ?? MealType::Other,
                    'time_of_day' => $row['time_of_day'] ?? null,
                    'quantity' => $row['quantity'] ?? null,
                    'position' => (int) $row['position'],
                ]);
            }

            return $created;
        });
    }

    /**
     * Replace items across multiple consecutive day indices in one transaction.
     *
     * @param  array<int, array<int, array<string, mixed>>>  $itemsByDayIndex
     *   Outer key = day_index; value = array of items for that day.
     * @return array<int, array<int, CycleMenuItem>>
     */
    public function replaceWeek(CycleMenu $menu, array $itemsByDayIndex): array
    {
        return DB::transaction(function () use ($menu, $itemsByDayIndex): array {
            $result = [];

            foreach ($itemsByDayIndex as $dayIndex => $items) {
                $result[(int) $dayIndex] = $this->replaceDay($menu, (int) $dayIndex, $items);
            }

            return $result;
        });
    }

    /**
     * Build a shopping-list aggregation for the next $window days starting
     * from the active menu's mapped "today" index. Aggregates by normalized
     * title + meal_type and counts occurrences. Quantity strings are returned
     * as a list (not summed) since they're free-text in this schema.
     *
     * @return array<int, array<string, mixed>>
     */
    public function shoppingList(CycleMenu $menu, int $window): array
    {
        $cycleLength = max(1, (int) $menu->cycle_length_days);
        $window = max(1, $window);

        $startsOn = $menu->starts_on?->startOfDay() ?? now()->startOfDay();
        $today = now()->startOfDay();
        $daysSinceStart = (int) $today->diffInDays($startsOn, false);
        $todayIndex = (($daysSinceStart % $cycleLength) + $cycleLength) % $cycleLength;

        $daysByIndex = $menu->days()->with('items')->get()->keyBy('day_index');

        $aggregate = [];

        for ($offset = 0; $offset < $window; $offset++) {
            $dayIndex = ($todayIndex + $offset) % $cycleLength;
            $day = $daysByIndex->get($dayIndex);

            if (! $day instanceof CycleMenuDay) {
                continue;
            }

            $date = $today->copy()->addDays($offset)->toDateString();

            foreach ($day->items as $item) {
                $title = trim((string) $item->title);
                $titleKey = strtolower($title);
                $mealType = is_object($item->meal_type) ? $item->meal_type?->value : (string) $item->meal_type;
                $key = $titleKey.'|'.($mealType ?? '');

                if (! isset($aggregate[$key])) {
                    $aggregate[$key] = [
                        'title' => $title,
                        'meal_type' => $mealType,
                        'count' => 0,
                        'quantities' => [],
                        'days' => [],
                    ];
                }

                $aggregate[$key]['count']++;
                if ($item->quantity !== null && $item->quantity !== '') {
                    $aggregate[$key]['quantities'][] = (string) $item->quantity;
                }
                $aggregate[$key]['days'][] = ['day_index' => $dayIndex, 'date' => $date];
            }
        }

        return array_values($aggregate);
    }

    private function findOrCreateDay(CycleMenu $menu, int $dayIndex): CycleMenuDay
    {
        $day = CycleMenuDay::query()
            ->where('cycle_menu_id', $menu->id)
            ->where('day_index', $dayIndex)
            ->first();

        if ($day === null) {
            $day = CycleMenuDay::create([
                'cycle_menu_id' => $menu->id,
                'day_index' => $dayIndex,
            ]);
        }

        return $day;
    }

    private function guardDayIndex(CycleMenu $menu, int $dayIndex): void
    {
        $max = (int) $menu->cycle_length_days - 1;

        if ($dayIndex < 0 || $dayIndex > $max) {
            throw new RuntimeException("day_index {$dayIndex} is outside menu cycle (0..{$max}).");
        }
    }
}
