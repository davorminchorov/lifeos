<?php

namespace App\Investments\Projections;

use App\Core\EventSourcing\Projector;
use App\Investments\Events\InvestmentCreated;
use App\Investments\Events\TransactionRecorded;
use App\Investments\Events\ValuationUpdated;
use Carbon\Carbon;
use Illuminate\Support\Str;

class InvestmentProjector extends Projector
{
    public function handleInvestmentCreated(InvestmentCreated $event): void
    {
        // Create the investment record
        InvestmentList::create([
            'id' => $event->investmentId,
            'name' => $event->name,
            'type' => $event->type,
            'institution' => $event->institution,
            'account_number' => $event->accountNumber,
            'initial_investment' => $event->initialInvestment,
            'current_value' => $event->initialInvestment,
            'roi' => 0, // Initial ROI is 0%
            'start_date' => $event->startDate,
            'end_date' => $event->endDate,
            'description' => $event->description,
            'total_invested' => $event->initialInvestment,
            'total_withdrawn' => 0,
            'last_valuation_date' => $event->startDate,
        ]);

        // Create initial transaction record
        TransactionList::create([
            'id' => (string) Str::uuid(),
            'investment_id' => $event->investmentId,
            'type' => 'deposit',
            'amount' => $event->initialInvestment,
            'date' => $event->startDate,
            'notes' => 'Initial investment',
        ]);

        // Create initial valuation record
        ValuationList::create([
            'id' => (string) Str::uuid(),
            'investment_id' => $event->investmentId,
            'value' => $event->initialInvestment,
            'date' => $event->startDate,
            'notes' => 'Initial valuation',
        ]);
    }

    public function handleTransactionRecorded(TransactionRecorded $event): void
    {
        // Create the transaction record
        TransactionList::create([
            'id' => $event->transactionId,
            'investment_id' => $event->investmentId,
            'type' => $event->type,
            'amount' => $event->amount,
            'date' => $event->date,
            'notes' => $event->notes,
        ]);

        // Update the investment record
        $investment = InvestmentList::findOrFail($event->investmentId);

        // Update totals based on transaction type
        switch ($event->type) {
            case 'deposit':
            case 'dividend':
            case 'interest':
                $investment->current_value += $event->amount;
                if ($event->type === 'deposit') {
                    $investment->total_invested += $event->amount;
                }
                break;
            case 'withdrawal':
            case 'fee':
                $investment->current_value -= $event->amount;
                if ($event->type === 'withdrawal') {
                    $investment->total_withdrawn += $event->amount;
                }
                break;
        }

        // Recalculate ROI
        $this->updateROI($investment);

        $investment->save();
    }

    public function handleValuationUpdated(ValuationUpdated $event): void
    {
        // Create the valuation record
        ValuationList::create([
            'id' => (string) Str::uuid(),
            'investment_id' => $event->investmentId,
            'value' => $event->newValue,
            'date' => $event->valuationDate,
            'notes' => $event->notes,
        ]);

        // Update the investment record
        $investment = InvestmentList::findOrFail($event->investmentId);
        $investment->current_value = $event->newValue;
        $investment->last_valuation_date = $event->valuationDate;

        // Recalculate ROI
        $this->updateROI($investment);

        $investment->save();
    }

    private function updateROI(InvestmentList $investment): void
    {
        if ($investment->total_invested <= 0) {
            $investment->roi = 0;
        } else {
            $netGain = $investment->current_value + $investment->total_withdrawn - $investment->total_invested;
            $investment->roi = ($netGain / $investment->total_invested) * 100;
        }
    }
}
