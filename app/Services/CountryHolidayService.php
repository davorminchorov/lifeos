<?php

namespace App\Services;

use App\Models\Holiday;
use App\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CountryHolidayService
{
    /**
     * Get the default country code.
     */
    public function getDefaultCountry(): string
    {
        return config('countries.default', 'MK');
    }

    /**
     * Get the default country for a specific tenant.
     */
    public function getTenantDefaultCountry(?Tenant $tenant): string
    {
        if ($tenant && $tenant->default_country) {
            return $tenant->default_country;
        }

        return $this->getDefaultCountry();
    }

    /**
     * Get all supported countries.
     */
    public function getSupportedCountries(): array
    {
        return config('countries.supported', []);
    }

    /**
     * Get country information by code.
     */
    public function getCountryInfo(string $countryCode): ?array
    {
        return config("countries.supported.{$countryCode}");
    }

    /**
     * Get country name by code.
     */
    public function getCountryName(string $countryCode): string
    {
        $countryInfo = $this->getCountryInfo($countryCode);

        return $countryInfo['name'] ?? $countryCode;
    }

    /**
     * Get holidays for a specific country from configuration.
     */
    public function getCountryHolidays(string $countryCode): array
    {
        $countryInfo = $this->getCountryInfo($countryCode);

        return $countryInfo['holidays'] ?? [];
    }

    /**
     * Sync holidays for a tenant based on their default country.
     */
    public function syncTenantHolidays(Tenant $tenant, ?string $countryCode = null): int
    {
        $countryCode = $countryCode ?? $this->getTenantDefaultCountry($tenant);
        $holidays = $this->getCountryHolidays($countryCode);

        if (empty($holidays)) {
            Log::warning("No holidays found for country: {$countryCode}");
            return 0;
        }

        $currentYear = now()->year;
        $synced = 0;

        foreach ($holidays as $holiday) {
            try {
                // Create holiday for current year
                Holiday::updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'country' => $countryCode,
                        'date' => "{$currentYear}-{$holiday['date']}",
                    ],
                    [
                        'name' => $holiday['name'],
                        'description' => $holiday['description'] ?? null,
                    ]
                );

                $synced++;
            } catch (\Exception $e) {
                Log::error("Failed to sync holiday: {$holiday['name']}", [
                    'tenant_id' => $tenant->id,
                    'country' => $countryCode,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $synced;
    }

    /**
     * Get holidays for a tenant.
     */
    public function getTenantHolidays(Tenant $tenant, ?int $year = null): Collection
    {
        $year = $year ?? now()->year;
        $countryCode = $this->getTenantDefaultCountry($tenant);

        return Holiday::where('tenant_id', $tenant->id)
            ->where('country', $countryCode)
            ->whereYear('date', $year)
            ->orderBy('date')
            ->get();
    }

    /**
     * Get holidays for the current year for a tenant.
     */
    public function getTenantCurrentYearHolidays(Tenant $tenant): Collection
    {
        return $this->getTenantHolidays($tenant, now()->year);
    }

    /**
     * Check if a date is a holiday for a tenant.
     */
    public function isHoliday(Tenant $tenant, string $date): bool
    {
        $countryCode = $this->getTenantDefaultCountry($tenant);

        return Holiday::where('tenant_id', $tenant->id)
            ->where('country', $countryCode)
            ->where('date', $date)
            ->exists();
    }

    /**
     * Get countries as options for select dropdown.
     */
    public function getCountryOptions(): array
    {
        $countries = $this->getSupportedCountries();
        $options = [];

        foreach ($countries as $code => $info) {
            $options[$code] = $info['name'];
        }

        return $options;
    }

    /**
     * Check if country is supported.
     */
    public function isSupported(string $countryCode): bool
    {
        return array_key_exists($countryCode, $this->getSupportedCountries());
    }

    /**
     * Get formatted country list for display.
     */
    public function getFormattedCountryList(): array
    {
        $countries = $this->getSupportedCountries();
        $formatted = [];

        foreach ($countries as $code => $info) {
            $formatted[] = [
                'code' => $code,
                'name' => $info['name'],
                'is_default' => $code === $this->getDefaultCountry(),
            ];
        }

        return $formatted;
    }
}
