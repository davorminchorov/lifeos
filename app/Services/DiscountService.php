<?php

namespace App\Services;

use App\Enums\DiscountType;
use App\Models\Discount;

class DiscountService
{
    /**
     * Calculate discount amount for a line item.
     *
     * @param Discount|null $discount
     * @param int $amount Line item amount in cents (before discount)
     * @param string $currency Currency code
     * @return int Discount amount in cents
     */
    public function calculateLineDiscount(?Discount $discount, int $amount, string $currency): int
    {
        if (!$discount || !$this->isValid($discount)) {
            return 0;
        }

        // Check minimum amount requirement
        if ($discount->minimum_amount && $amount < $discount->minimum_amount) {
            return 0;
        }

        if ($discount->type === DiscountType::PERCENT) {
            // Percentage discount: value is stored as integer (e.g., 20 for 20%)
            $percentage = $discount->value / 100;
            return (int) round($amount * $percentage);
        } else {
            // Fixed amount discount
            // Ensure discount currency matches
            if ($discount->currency && $discount->currency !== $currency) {
                return 0;
            }

            // Don't allow discount to exceed line amount
            return min($discount->value, $amount);
        }
    }

    /**
     * Calculate discount for an entire invoice.
     *
     * @param Discount|null $discount
     * @param int $subtotal Invoice subtotal in cents
     * @param string $currency Currency code
     * @return int Discount amount in cents
     */
    public function calculateInvoiceDiscount(?Discount $discount, int $subtotal, string $currency): int
    {
        if (!$discount || !$this->isValid($discount)) {
            return 0;
        }

        // Check minimum amount requirement
        if ($discount->minimum_amount && $subtotal < $discount->minimum_amount) {
            return 0;
        }

        if ($discount->type === DiscountType::PERCENT) {
            $percentage = $discount->value / 100;
            return (int) round($subtotal * $percentage);
        } else {
            // Fixed amount discount
            if ($discount->currency && $discount->currency !== $currency) {
                return 0;
            }

            // Don't allow discount to exceed subtotal
            return min($discount->value, $subtotal);
        }
    }

    /**
     * Validate that a discount is currently valid and can be used.
     *
     * @param Discount $discount
     * @return bool
     */
    public function isValid(Discount $discount): bool
    {
        if (!$discount->active) {
            return false;
        }

        $now = now();

        // Check start date
        if ($discount->starts_at && $now->isBefore($discount->starts_at)) {
            return false;
        }

        // Check end date
        if ($discount->ends_at && $now->isAfter($discount->ends_at)) {
            return false;
        }

        // Check redemption limits
        if ($discount->max_redemptions && $discount->current_redemptions >= $discount->max_redemptions) {
            return false;
        }

        return true;
    }

    /**
     * Validate discount code and return the discount if valid.
     *
     * @param string $code
     * @param int $userId
     * @param int $customerId
     * @param int $amount Amount to check against minimum
     * @param string $currency
     * @return Discount|null
     */
    public function validateCode(string $code, int $userId, int $customerId, int $amount, string $currency): ?Discount
    {
        $discount = Discount::where('user_id', $userId)
            ->where('code', $code)
            ->first();

        if (!$discount) {
            return null;
        }

        if (!$this->isValid($discount)) {
            return null;
        }

        // Check minimum amount
        if ($discount->minimum_amount && $amount < $discount->minimum_amount) {
            return null;
        }

        // Check per-customer redemption limit
        if ($discount->max_redemptions_per_customer) {
            // Count how many times this customer has used this discount
            $usageCount = \DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->where('invoices.customer_id', $customerId)
                ->where('invoice_items.discount_id', $discount->id)
                ->count();

            if ($usageCount >= $discount->max_redemptions_per_customer) {
                return null;
            }
        }

        // Check currency match for fixed discounts
        if ($discount->type === DiscountType::FIXED && $discount->currency && $discount->currency !== $currency) {
            return null;
        }

        return $discount;
    }

    /**
     * Increment the redemption count for a discount.
     *
     * @param Discount $discount
     * @return void
     */
    public function incrementRedemption(Discount $discount): void
    {
        $discount->increment('current_redemptions');
    }

    /**
     * Decrement the redemption count for a discount (e.g., when invoice is voided).
     *
     * @param Discount $discount
     * @return void
     */
    public function decrementRedemption(Discount $discount): void
    {
        if ($discount->current_redemptions > 0) {
            $discount->decrement('current_redemptions');
        }
    }

    /**
     * Format discount value as a string for display.
     *
     * @param Discount $discount
     * @return string
     */
    public function formatValue(Discount $discount): string
    {
        if ($discount->type === DiscountType::PERCENT) {
            return $discount->value . '%';
        } else {
            $amount = $discount->value / 100;
            return app(CurrencyService::class)->format($amount, $discount->currency);
        }
    }

    /**
     * Get remaining redemptions for a discount.
     *
     * @param Discount $discount
     * @return int|null Null if unlimited
     */
    public function getRemainingRedemptions(Discount $discount): ?int
    {
        if (!$discount->max_redemptions) {
            return null; // Unlimited
        }

        return max(0, $discount->max_redemptions - $discount->current_redemptions);
    }
}
