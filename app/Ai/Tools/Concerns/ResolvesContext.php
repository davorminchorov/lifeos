<?php

namespace App\Ai\Tools\Concerns;

trait ResolvesContext
{
    protected function tenantId(): int
    {
        return (int) config('telegram.tenant_id', 1);
    }

    protected function userId(): int
    {
        return (int) config('telegram.user_id', 1);
    }

    protected function defaultCurrency(): string
    {
        return config('currency.default', 'MKD');
    }

    protected function formatAmount(float $amount, ?string $currency = null): string
    {
        $currency = $currency ?? $this->defaultCurrency();

        return number_format($amount, 2).' '.$currency;
    }
}
