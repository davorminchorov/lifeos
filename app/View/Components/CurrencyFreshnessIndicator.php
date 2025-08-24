<?php

namespace App\View\Components;

use App\Services\CurrencyService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CurrencyFreshnessIndicator extends Component
{
    public string $fromCurrency;
    public string $toCurrency;
    public array $rateInfo;
    public bool $showRefreshButton;
    public bool $showAge;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $fromCurrency,
        string $toCurrency,
        bool $showRefreshButton = true,
        bool $showAge = true
    ) {
        $this->fromCurrency = $fromCurrency;
        $this->toCurrency = $toCurrency;
        $this->showRefreshButton = $showRefreshButton;
        $this->showAge = $showAge;

        $currencyService = app(CurrencyService::class);
        $this->rateInfo = $currencyService->getExchangeRateWithFreshness($fromCurrency, $toCurrency);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        if (!config('currency.freshness.show_indicators', true)) {
            return '';
        }

        return view('components.currency-freshness-indicator');
    }

    /**
     * Get the freshness badge color class for Tailwind.
     */
    public function getBadgeColorClass(): string
    {
        $freshness = $this->rateInfo['freshness'];

        return match($freshness) {
            'fresh' => 'bg-green-100 text-green-800 border-green-200',
            'stale' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'warning' => 'bg-red-100 text-red-800 border-red-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    /**
     * Get the freshness icon.
     */
    public function getFreshnessIcon(): string
    {
        $freshness = $this->rateInfo['freshness'];

        return match($freshness) {
            'fresh' => '✓',
            'stale' => '⚠',
            'warning' => '⚠',
            default => '?',
        };
    }

    /**
     * Get the formatted age string.
     */
    public function getFormattedAge(): string
    {
        $currencyService = app(CurrencyService::class);
        return $currencyService->getFormattedAge($this->rateInfo['age_seconds']);
    }

    /**
     * Get the freshness label.
     */
    public function getFreshnessLabel(): string
    {
        $currencyService = app(CurrencyService::class);
        return $currencyService->getFreshnessLabel($this->rateInfo['freshness']);
    }

    /**
     * Check if rate needs attention (stale or warning).
     */
    public function needsAttention(): bool
    {
        return in_array($this->rateInfo['freshness'], ['warning', 'unknown']);
    }
}
