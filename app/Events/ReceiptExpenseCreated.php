<?php

namespace App\Events;

use App\Models\Expense;
use App\Models\ProcessedEmail;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReceiptExpenseCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Expense $expense, public ProcessedEmail $processedEmail) {}
}
