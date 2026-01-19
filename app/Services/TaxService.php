<?php

namespace App\Services;

use App\Enums\TaxBehavior;
use App\Models\TaxRate;

class TaxService
{
    /**
     * Calculate tax amount for a line item.
     *
     * @param TaxRate|null $taxRate
     * @param int $amount Amount in cents (before tax)
     * @param TaxBehavior $taxBehavior
     * @return int Tax amount in cents
     */
    public function calculateLineTax(?TaxRate $taxRate, int $amount, TaxBehavior $taxBehavior): int
    {
        if (!$taxRate || !$taxRate->active) {
            return 0;
        }

        $percentage = $taxRate->percentage_basis_points / 10000; // Convert basis points to percentage

        if ($taxBehavior === TaxBehavior::EXCLUSIVE) {
            // Tax is added on top: tax = amount * rate
            return (int) round($amount * $percentage);
        } else {
            // Tax is included in price: tax = amount * (rate / (1 + rate))
            return (int) round($amount * ($percentage / (1 + $percentage)));
        }
    }

    /**
     * Calculate the pre-tax amount from a tax-inclusive amount.
     *
     * @param int $inclusiveAmount Amount including tax (cents)
     * @param TaxRate|null $taxRate
     * @return int Amount before tax (cents)
     */
    public function calculatePreTaxAmount(int $inclusiveAmount, ?TaxRate $taxRate): int
    {
        if (!$taxRate || !$taxRate->active) {
            return $inclusiveAmount;
        }

        $percentage = $taxRate->percentage_basis_points / 10000;
        return (int) round($inclusiveAmount / (1 + $percentage));
    }

    /**
     * Calculate the total amount including tax for an exclusive tax.
     *
     * @param int $amount Amount before tax (cents)
     * @param TaxRate|null $taxRate
     * @return int Total amount including tax (cents)
     */
    public function calculateTotalWithTax(int $amount, ?TaxRate $taxRate): int
    {
        if (!$taxRate || !$taxRate->active) {
            return $amount;
        }

        $taxAmount = $this->calculateLineTax($taxRate, $amount, TaxBehavior::EXCLUSIVE);
        return $amount + $taxAmount;
    }

    /**
     * Get effective tax rate as a decimal (e.g., 0.20 for 20%).
     *
     * @param TaxRate|null $taxRate
     * @return float
     */
    public function getEffectiveRate(?TaxRate $taxRate): float
    {
        if (!$taxRate || !$taxRate->active) {
            return 0.0;
        }

        return $taxRate->percentage_basis_points / 10000;
    }

    /**
     * Format tax rate as a percentage string (e.g., "20%").
     *
     * @param TaxRate|null $taxRate
     * @return string
     */
    public function formatRate(?TaxRate $taxRate): string
    {
        if (!$taxRate) {
            return '0%';
        }

        $percentage = $taxRate->percentage_basis_points / 100;
        return number_format($percentage, 2) . '%';
    }

    /**
     * Validate that a tax rate is applicable for a given date.
     *
     * @param TaxRate $taxRate
     * @param \DateTime|null $date
     * @return bool
     */
    public function isValidForDate(TaxRate $taxRate, ?\DateTime $date = null): bool
    {
        $date = $date ?? new \DateTime();

        if (!$taxRate->active) {
            return false;
        }

        if ($taxRate->valid_from && $date < $taxRate->valid_from) {
            return false;
        }

        if ($taxRate->valid_to && $date > $taxRate->valid_to) {
            return false;
        }

        return true;
    }
}
