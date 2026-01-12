<?php

namespace App\Services;

use App\Models\Holiday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class WorkingDaysService
{
    /**
     * Calculate the number of working days in the current month.
     * Working days are Monday through Friday, excluding holidays.
     */
    public function getWorkingDaysInCurrentMonth(): int
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        return $this->getWorkingDaysBetween($startOfMonth, $endOfMonth);
    }

    /**
     * Calculate the number of working days between two dates.
     * Working days are Monday through Friday, excluding holidays.
     */
    public function getWorkingDaysBetween(Carbon $startDate, Carbon $endDate): int
    {
        $workingDays = 0;
        $holidays = $this->getHolidaysBetween($startDate, $endDate);

        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            // Check if it's a weekday (Monday-Friday)
            if ($date->isWeekday()) {
                // Check if it's not a holiday
                if (!$holidays->contains($date->format('Y-m-d'))) {
                    $workingDays++;
                }
            }
        }

        return $workingDays;
    }

    /**
     * Get all holidays between two dates.
     */
    protected function getHolidaysBetween(Carbon $startDate, Carbon $endDate)
    {
        return Holiday::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->pluck('date')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'));
    }

    /**
     * Get the current month name.
     */
    public function getCurrentMonthName(): string
    {
        return Carbon::now()->format('F Y');
    }

    /**
     * Get working days information for the current month.
     */
    public function getCurrentMonthWorkingDaysInfo(): array
    {
        $workingDays = $this->getWorkingDaysInCurrentMonth();
        $monthName = $this->getCurrentMonthName();

        return [
            'working_days' => $workingDays,
            'month_name' => $monthName,
        ];
    }
}
