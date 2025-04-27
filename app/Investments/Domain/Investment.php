<?php

namespace App\Investments\Domain;

use App\Core\EventSourcing\AggregateRoot;
use App\Investments\Events\InvestmentCreated;
use App\Investments\Events\TransactionRecorded;
use App\Investments\Events\ValuationUpdated;
use DateTimeImmutable;
use InvalidArgumentException;

class Investment extends AggregateRoot
{
    private string $name;
    private string $type;
    private string $institution;
    private ?string $accountNumber;
    private float $initialInvestment;
    private ?string $startDate;
    private ?string $endDate;
    private ?string $description;
    private array $transactions = [];
    private array $valuations = [];
    private float $currentValue;
    private float $totalInvested;
    private float $totalWithdrawn;

    public static function create(
        string $investmentId,
        string $name,
        string $type,
        string $institution,
        ?string $accountNumber,
        float $initialInvestment,
        string $startDate,
        ?string $endDate = null,
        ?string $description = null
    ): self {
        // Validate investment type
        if (!in_array($type, ['stock', 'bond', 'mutual_fund', 'etf', 'real_estate', 'retirement', 'life_insurance', 'other'])) {
            throw new InvalidArgumentException('Invalid investment type');
        }

        // Validate initial investment
        if ($initialInvestment <= 0) {
            throw new InvalidArgumentException('Initial investment must be positive');
        }

        $investment = new self($investmentId);

        $investment->recordEvent(new InvestmentCreated(
            $investmentId,
            $name,
            $type,
            $institution,
            $accountNumber,
            $initialInvestment,
            $startDate,
            $endDate,
            $description
        ));

        return $investment;
    }

    public function recordTransaction(
        string $transactionId,
        string $type,
        float $amount,
        string $date,
        ?string $notes = null
    ): void {
        if (!in_array($type, ['deposit', 'withdrawal', 'dividend', 'fee', 'interest'])) {
            throw new InvalidArgumentException('Invalid transaction type');
        }

        if ($amount <= 0) {
            throw new InvalidArgumentException('Transaction amount must be positive');
        }

        // For withdrawals, check if there's enough balance
        if ($type === 'withdrawal' && $amount > $this->currentValue) {
            throw new InvalidArgumentException('Insufficient funds for withdrawal');
        }

        $this->recordEvent(new TransactionRecorded(
            $this->aggregateId,
            $transactionId,
            $type,
            $amount,
            $date,
            $notes
        ));
    }

    public function updateValuation(
        float $newValue,
        string $valuationDate,
        ?string $notes = null
    ): void {
        $this->recordEvent(new ValuationUpdated(
            $this->aggregateId,
            $newValue,
            $valuationDate,
            $notes
        ));
    }

    protected function applyInvestmentCreated(InvestmentCreated $event): void
    {
        $this->name = $event->name;
        $this->type = $event->type;
        $this->institution = $event->institution;
        $this->accountNumber = $event->accountNumber;
        $this->initialInvestment = $event->initialInvestment;
        $this->startDate = $event->startDate;
        $this->endDate = $event->endDate;
        $this->description = $event->description;
        $this->currentValue = $event->initialInvestment;
        $this->totalInvested = $event->initialInvestment;
        $this->totalWithdrawn = 0;

        // Record initial investment as first transaction
        $this->transactions[] = [
            'type' => 'deposit',
            'amount' => $event->initialInvestment,
            'date' => $event->startDate,
            'notes' => 'Initial investment'
        ];

        // Record initial valuation
        $this->valuations[] = [
            'value' => $event->initialInvestment,
            'date' => $event->startDate,
            'notes' => 'Initial valuation'
        ];
    }

    protected function applyTransactionRecorded(TransactionRecorded $event): void
    {
        $this->transactions[] = [
            'id' => $event->transactionId,
            'type' => $event->type,
            'amount' => $event->amount,
            'date' => $event->date,
            'notes' => $event->notes
        ];

        // Update totals
        switch ($event->type) {
            case 'deposit':
            case 'dividend':
            case 'interest':
                $this->currentValue += $event->amount;
                if ($event->type === 'deposit') {
                    $this->totalInvested += $event->amount;
                }
                break;
            case 'withdrawal':
            case 'fee':
                $this->currentValue -= $event->amount;
                if ($event->type === 'withdrawal') {
                    $this->totalWithdrawn += $event->amount;
                }
                break;
        }
    }

    protected function applyValuationUpdated(ValuationUpdated $event): void
    {
        $this->valuations[] = [
            'value' => $event->newValue,
            'date' => $event->valuationDate,
            'notes' => $event->notes
        ];

        $this->currentValue = $event->newValue;
    }

    // Helper method to calculate return on investment (ROI)
    public function calculateROI(): float
    {
        if ($this->totalInvested <= 0) {
            return 0;
        }

        $netGain = $this->currentValue + $this->totalWithdrawn - $this->totalInvested;
        return ($netGain / $this->totalInvested) * 100;
    }
}
