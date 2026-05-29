<?php

namespace Database\Factories;

use App\Models\DataExport;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DataExport>
 */
class DataExportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'modules' => ['expenses'],
            'filters' => [],
            'status' => DataExport::STATUS_PENDING,
            'disk' => 'local',
            'file_path' => null,
            'file_name' => null,
            'row_counts' => null,
            'error' => null,
            'completed_at' => null,
            'expires_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => DataExport::STATUS_COMPLETED,
            'file_path' => 'exports/example/export_example.csv',
            'file_name' => 'expenses-export.csv',
            'row_counts' => ['expenses' => 3],
            'completed_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);
    }
}
