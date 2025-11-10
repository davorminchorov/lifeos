<?php

namespace Database\Factories;

use App\Enums\ApplicationSource;
use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobApplication>
 */
class JobApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companies = [
            'Google', 'Apple', 'Microsoft', 'Amazon', 'Meta', 'Netflix', 'Tesla',
            'Stripe', 'Shopify', 'Airbnb', 'Uber', 'Spotify', 'Adobe', 'Salesforce',
            'Oracle', 'IBM', 'Intel', 'NVIDIA', 'PayPal', 'Square', 'Dropbox',
        ];

        $jobTitles = [
            'Senior Software Engineer', 'Full Stack Developer', 'Backend Engineer',
            'Frontend Developer', 'DevOps Engineer', 'Data Engineer', 'ML Engineer',
            'Product Manager', 'Engineering Manager', 'UX Designer', 'QA Engineer',
            'Security Engineer', 'Cloud Architect', 'Software Architect',
        ];

        $cities = [
            'San Francisco, CA', 'New York, NY', 'Seattle, WA', 'Austin, TX',
            'Boston, MA', 'Denver, CO', 'Remote', 'London, UK', 'Berlin, Germany',
            'Toronto, Canada', 'Amsterdam, Netherlands', 'Singapore',
        ];

        $currencies = ['USD', 'EUR', 'GBP', 'CAD'];
        $priorities = [0, 1, 2, 3];

        $isRemote = $this->faker->boolean(40);
        $location = $isRemote ? 'Remote' : $this->faker->randomElement($cities);
        $currency = $this->faker->randomElement($currencies);

        $hasApplied = $this->faker->boolean(70);
        $appliedAt = $hasApplied ? $this->faker->dateTimeBetween('-60 days', 'now') : null;

        return [
            'user_id' => \App\Models\User::factory(),
            'company_name' => $this->faker->randomElement($companies),
            'company_website' => $this->faker->optional()->url(),
            'job_title' => $this->faker->randomElement($jobTitles),
            'job_description' => $this->faker->optional()->paragraph(3),
            'job_url' => $this->faker->optional()->url(),
            'location' => $location,
            'remote' => $isRemote,
            'salary_min' => $this->faker->optional()->numberBetween(60000, 120000),
            'salary_max' => $this->faker->optional()->numberBetween(120000, 200000),
            'currency' => $currency,
            'status' => $this->faker->randomElement(ApplicationStatus::cases()),
            'source' => $this->faker->randomElement(ApplicationSource::cases()),
            'applied_at' => $appliedAt,
            'next_action_at' => $this->faker->optional()->dateTimeBetween('now', '+30 days'),
            'priority' => $this->faker->randomElement($priorities),
            'contact_name' => $this->faker->optional()->name(),
            'contact_email' => $this->faker->optional()->email(),
            'contact_phone' => $this->faker->optional()->phoneNumber(),
            'notes' => $this->faker->optional()->paragraph(),
            'tags' => $this->faker->optional()->randomElements([
                'senior', 'remote', 'startup', 'enterprise', 'frontend', 'backend',
                'urgent', 'dream-job', 'referral', 'competitive-salary',
            ], $this->faker->numberBetween(1, 3)),
            'file_attachments' => $this->faker->optional(0.3)->randomElements([
                'resume_2024.pdf',
                'cover_letter.pdf',
                'portfolio.pdf',
            ], $this->faker->numberBetween(1, 2)),
            'archived_at' => null,
        ];
    }

    /**
     * Indicate that the application is in wishlist status.
     */
    public function wishlist(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApplicationStatus::WISHLIST,
            'applied_at' => null,
        ]);
    }

    /**
     * Indicate that the application has been applied.
     */
    public function applied(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApplicationStatus::APPLIED,
            'applied_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the application is in interview stage.
     */
    public function interview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApplicationStatus::INTERVIEW,
            'applied_at' => $this->faker->dateTimeBetween('-45 days', '-15 days'),
        ]);
    }

    /**
     * Indicate that the application has received an offer.
     */
    public function offer(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApplicationStatus::OFFER,
            'applied_at' => $this->faker->dateTimeBetween('-60 days', '-20 days'),
        ]);
    }

    /**
     * Indicate that the application is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'archived_at' => now(),
        ]);
    }

    /**
     * Indicate that the application is high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 2,
        ]);
    }

    /**
     * Indicate that the application is remote.
     */
    public function remote(): static
    {
        return $this->state(fn (array $attributes) => [
            'remote' => true,
            'location' => 'Remote',
        ]);
    }
}
