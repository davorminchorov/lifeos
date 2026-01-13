<?php

namespace App\Services;

use App\Models\Sequence;
use Illuminate\Support\Facades\DB;

class NumberingService
{
    /**
     * Reserve the next invoice number for a user.
     *
     * @param int $userId
     * @return array ['number' => 'INV-2026-000001', 'year' => 2026, 'sequence' => 1]
     */
    public function reserveInvoiceNumber(int $userId): array
    {
        return $this->reserveNumber($userId, 'invoice');
    }

    /**
     * Reserve the next credit note number for a user.
     *
     * @param int $userId
     * @return array ['number' => 'CN-2026-000001', 'year' => 2026, 'sequence' => 1]
     */
    public function reserveCreditNoteNumber(int $userId): array
    {
        return $this->reserveNumber($userId, 'credit_note');
    }

    /**
     * Reserve the next sequence number atomically.
     *
     * @param int $userId
     * @param string $scope 'invoice' or 'credit_note'
     * @return array
     */
    protected function reserveNumber(int $userId, string $scope): array
    {
        $year = now()->year;

        return DB::transaction(function () use ($userId, $scope, $year) {
            // Find or create sequence for this user, scope, and year
            $sequence = Sequence::where('user_id', $userId)
                ->where('scope', $scope)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                $sequence = Sequence::create([
                    'user_id' => $userId,
                    'scope' => $scope,
                    'year' => $year,
                    'current_value' => 0,
                ]);
            }

            // Increment the sequence
            $sequence->increment('current_value');
            $sequence->refresh();

            // Get prefix
            $prefix = $this->getPrefix($sequence, $scope);

            // Format the number
            $formattedNumber = $this->formatNumber($prefix, $year, $sequence->current_value);

            return [
                'number' => $formattedNumber,
                'year' => $year,
                'sequence' => $sequence->current_value,
            ];
        });
    }

    /**
     * Get the prefix for a sequence.
     *
     * @param Sequence $sequence
     * @param string $scope
     * @return string
     */
    protected function getPrefix(Sequence $sequence, string $scope): string
    {
        // Use custom prefix if set, otherwise use default from config
        if ($sequence->prefix) {
            return $sequence->prefix;
        }

        return match ($scope) {
            'invoice' => config('invoicing.prefix', 'INV'),
            'credit_note' => config('invoicing.credit_note_prefix', 'CN'),
            default => 'INV',
        };
    }

    /**
     * Format the number with prefix, year, and padded sequence.
     *
     * @param string $prefix
     * @param int $year
     * @param int $sequence
     * @return string
     */
    protected function formatNumber(string $prefix, int $year, int $sequence): string
    {
        // Pad sequence to 6 digits
        $paddedSequence = str_pad($sequence, 6, '0', STR_PAD_LEFT);

        return "{$prefix}-{$year}-{$paddedSequence}";
    }

    /**
     * Preview what the next invoice number would be (without reserving it).
     *
     * @param int $userId
     * @return string
     */
    public function previewNextInvoiceNumber(int $userId): string
    {
        return $this->previewNextNumber($userId, 'invoice');
    }

    /**
     * Preview what the next credit note number would be (without reserving it).
     *
     * @param int $userId
     * @return string
     */
    public function previewNextCreditNoteNumber(int $userId): string
    {
        return $this->previewNextNumber($userId, 'credit_note');
    }

    /**
     * Preview the next sequence number without reserving it.
     *
     * @param int $userId
     * @param string $scope
     * @return string
     */
    protected function previewNextNumber(int $userId, string $scope): string
    {
        $year = now()->year;

        $sequence = Sequence::where('user_id', $userId)
            ->where('scope', $scope)
            ->where('year', $year)
            ->first();

        $nextValue = $sequence ? $sequence->current_value + 1 : 1;
        $prefix = $sequence && $sequence->prefix
            ? $sequence->prefix
            : $this->getDefaultPrefix($scope);

        return $this->formatNumber($prefix, $year, $nextValue);
    }

    /**
     * Get default prefix for scope.
     *
     * @param string $scope
     * @return string
     */
    protected function getDefaultPrefix(string $scope): string
    {
        return match ($scope) {
            'invoice' => config('invoicing.prefix', 'INV'),
            'credit_note' => config('invoicing.credit_note_prefix', 'CN'),
            default => 'INV',
        };
    }
}
