<?php

namespace Database\Factories;

use App\Enums\InterviewOutcome;
use App\Enums\InterviewType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobApplicationInterview>
 */
class JobApplicationInterviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $interviewers = [
            'John Smith - Engineering Manager',
            'Sarah Johnson - Senior Developer',
            'Michael Chen - Tech Lead',
            'Emily Davis - HR Manager',
            'David Wilson - CTO',
            'Lisa Anderson - Product Manager',
        ];

        $videoLinks = [
            'https://zoom.us/j/123456789',
            'https://meet.google.com/abc-defg-hij',
            'https://teams.microsoft.com/l/meetup-join/...',
        ];

        $locations = [
            'Company Office - Main Building',
            'Remote',
            'Coffee Shop Downtown',
            'Conference Room A',
        ];

        $isCompleted = $this->faker->boolean(60);
        $isPast = $this->faker->boolean(50);

        $scheduledAt = $isPast
            ? $this->faker->dateTimeBetween('-30 days', '-1 day')
            : $this->faker->dateTimeBetween('now', '+30 days');

        return [
            'user_id' => \App\Models\User::factory(),
            'job_application_id' => \App\Models\JobApplication::factory(),
            'type' => $this->faker->randomElement(InterviewType::cases()),
            'scheduled_at' => $scheduledAt,
            'duration_minutes' => $this->faker->randomElement([30, 45, 60, 90, 120]),
            'location' => $this->faker->optional()->randomElement($locations),
            'video_link' => $this->faker->optional()->randomElement($videoLinks),
            'interviewer_name' => $this->faker->optional()->randomElement($interviewers),
            'notes' => $this->faker->optional()->paragraph(),
            'feedback' => $isCompleted ? $this->faker->optional()->paragraph() : null,
            'outcome' => $isCompleted ? $this->faker->randomElement(InterviewOutcome::cases()) : InterviewOutcome::PENDING,
            'completed' => $isCompleted,
        ];
    }

    /**
     * Indicate that the interview is upcoming.
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => $this->faker->dateTimeBetween('now', '+30 days'),
            'completed' => false,
            'feedback' => null,
            'outcome' => InterviewOutcome::PENDING,
        ]);
    }

    /**
     * Indicate that the interview is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
            'completed' => true,
            'feedback' => $this->faker->paragraph(),
            'outcome' => $this->faker->randomElement([
                InterviewOutcome::POSITIVE,
                InterviewOutcome::NEUTRAL,
                InterviewOutcome::NEGATIVE,
            ]),
        ]);
    }

    /**
     * Indicate that the interview went well.
     */
    public function positive(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => true,
            'outcome' => InterviewOutcome::POSITIVE,
            'feedback' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Indicate that the interview is a phone screen.
     */
    public function phoneScreen(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => InterviewType::PHONE,
            'duration_minutes' => 30,
            'location' => null,
            'video_link' => null,
        ]);
    }

    /**
     * Indicate that the interview is a video call.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => InterviewType::VIDEO,
            'location' => 'Remote',
            'video_link' => 'https://zoom.us/j/'.$this->faker->numerify('##########'),
        ]);
    }

    /**
     * Indicate that the interview is onsite.
     */
    public function onsite(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => InterviewType::ONSITE,
            'location' => 'Company Office - Main Building',
            'video_link' => null,
        ]);
    }
}
