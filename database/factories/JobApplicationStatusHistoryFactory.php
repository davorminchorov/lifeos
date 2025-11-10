<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobApplicationStatusHistory>
 */
class JobApplicationStatusHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'job_application_id' => \App\Models\JobApplication::factory(),
            'from_status' => $this->faker->optional()->randomElement(ApplicationStatus::cases()),
            'to_status' => $this->faker->randomElement(ApplicationStatus::cases()),
            'changed_at' => $this->faker->dateTimeBetween('-60 days', 'now'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate this is an initial status (no from_status).
     */
    public function initial(): static
    {
        return $this->state(fn (array $attributes) => [
            'from_status' => null,
            'to_status' => ApplicationStatus::WISHLIST,
        ]);
    }

    /**
     * Status change to applied.
     */
    public function toApplied(): static
    {
        return $this->state(fn (array $attributes) => [
            'from_status' => ApplicationStatus::WISHLIST,
            'to_status' => ApplicationStatus::APPLIED,
        ]);
    }

    /**
     * Status change to interview.
     */
    public function toInterview(): static
    {
        return $this->state(fn (array $attributes) => [
            'from_status' => ApplicationStatus::SCREENING,
            'to_status' => ApplicationStatus::INTERVIEW,
        ]);
    }

    /**
     * Status change to offer.
     */
    public function toOffer(): static
    {
        return $this->state(fn (array $attributes) => [
            'from_status' => ApplicationStatus::INTERVIEW,
            'to_status' => ApplicationStatus::OFFER,
        ]);
    }

    /**
     * Status change to rejected.
     */
    public function toRejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'to_status' => ApplicationStatus::REJECTED,
        ]);
    }
}
