<?php

namespace App\UtilityBills\Domain;

use App\Core\EventSourcing\AggregateRoot;
use App\UtilityBills\Events\BillAdded;
use App\UtilityBills\Events\BillPaid;
use App\UtilityBills\Events\BillUpdated;
use App\UtilityBills\Events\ReminderScheduled;
use App\UtilityBills\Events\ReminderSent;
use DateTimeImmutable;
use InvalidArgumentException;

class UtilityBill extends AggregateRoot
{
    private string $name;
    private string $provider;
    private float $amount;
    private string $dueDate;
    private string $category;
    private bool $isRecurring;
    private ?string $recurrencePeriod;
    private ?string $notes;
    private string $status = 'pending';
    private array $payments = [];
    private array $reminders = [];

    public static function create(
        string $billId,
        string $name,
        string $provider,
        float $amount,
        string $dueDate,
        string $category,
        bool $isRecurring,
        ?string $recurrencePeriod,
        ?string $notes
    ): self {
        // Validate recurrence period if bill is recurring
        if ($isRecurring && !in_array($recurrencePeriod, ['monthly', 'bimonthly', 'quarterly', 'annually'])) {
            throw new InvalidArgumentException('Invalid recurrence period');
        }

        $bill = new self($billId);

        $bill->recordEvent(new BillAdded(
            $billId,
            $name,
            $provider,
            $amount,
            $dueDate,
            $category,
            $isRecurring,
            $recurrencePeriod,
            $notes
        ));

        return $bill;
    }

    public function update(
        ?string $name,
        ?string $provider,
        ?float $amount,
        ?string $dueDate,
        ?string $category,
        ?bool $isRecurring,
        ?string $recurrencePeriod,
        ?string $notes
    ): void {
        // Validate recurrence period if changing to recurring
        if ($isRecurring === true && $recurrencePeriod !== null) {
            if (!in_array($recurrencePeriod, ['monthly', 'bimonthly', 'quarterly', 'annually'])) {
                throw new InvalidArgumentException('Invalid recurrence period');
            }
        }

        // Check if any actual change
        if ($name === null && $provider === null && $amount === null && $dueDate === null &&
            $category === null && $isRecurring === null && $recurrencePeriod === null && $notes === null) {
            return;
        }

        $this->recordEvent(new BillUpdated(
            $this->aggregateId,
            $name,
            $provider,
            $amount,
            $dueDate,
            $category,
            $isRecurring,
            $recurrencePeriod,
            $notes
        ));
    }

    public function pay(
        string $paymentDate,
        float $paymentAmount,
        string $paymentMethod,
        ?string $notes
    ): void {
        if ($this->status === 'paid') {
            throw new InvalidArgumentException('Bill has already been paid');
        }

        $this->recordEvent(new BillPaid(
            $this->aggregateId,
            $paymentDate,
            $paymentAmount,
            $paymentMethod,
            $notes
        ));
    }

    public function scheduleReminder(
        string $reminderDate,
        string $reminderMessage
    ): void {
        if ($this->status === 'paid') {
            throw new InvalidArgumentException('Cannot schedule reminder for paid bill');
        }

        $this->recordEvent(new ReminderScheduled(
            $this->aggregateId,
            $reminderDate,
            $reminderMessage
        ));
    }

    public function sendReminder(string $sentAt, string $reminderMessage): void
    {
        if ($this->status === 'paid') {
            throw new InvalidArgumentException('Cannot send reminder for paid bill');
        }

        $this->recordEvent(new ReminderSent(
            $this->aggregateId,
            $sentAt,
            $reminderMessage
        ));
    }

    protected function applyBillAdded(BillAdded $event): void
    {
        $this->name = $event->name;
        $this->provider = $event->provider;
        $this->amount = $event->amount;
        $this->dueDate = $event->dueDate;
        $this->category = $event->category;
        $this->isRecurring = $event->isRecurring;
        $this->recurrencePeriod = $event->recurrencePeriod;
        $this->notes = $event->notes;
        $this->status = 'pending';
    }

    protected function applyBillUpdated(BillUpdated $event): void
    {
        if ($event->name !== null) {
            $this->name = $event->name;
        }

        if ($event->provider !== null) {
            $this->provider = $event->provider;
        }

        if ($event->amount !== null) {
            $this->amount = $event->amount;
        }

        if ($event->dueDate !== null) {
            $this->dueDate = $event->dueDate;
        }

        if ($event->category !== null) {
            $this->category = $event->category;
        }

        if ($event->isRecurring !== null) {
            $this->isRecurring = $event->isRecurring;
        }

        if ($event->recurrencePeriod !== null) {
            $this->recurrencePeriod = $event->recurrencePeriod;
        }

        if ($event->notes !== null) {
            $this->notes = $event->notes;
        }
    }

    protected function applyBillPaid(BillPaid $event): void
    {
        $this->payments[] = [
            'date' => $event->paymentDate,
            'amount' => $event->paymentAmount,
            'method' => $event->paymentMethod,
            'notes' => $event->notes,
        ];

        $this->status = 'paid';

        // If bill is recurring, we need to update the due date for the next payment
        if ($this->isRecurring && $this->recurrencePeriod) {
            $paymentDate = new DateTimeImmutable($event->paymentDate);
            $this->dueDate = match($this->recurrencePeriod) {
                'monthly' => $paymentDate->modify('+1 month')->format('Y-m-d'),
                'bimonthly' => $paymentDate->modify('+2 months')->format('Y-m-d'),
                'quarterly' => $paymentDate->modify('+3 months')->format('Y-m-d'),
                'annually' => $paymentDate->modify('+1 year')->format('Y-m-d'),
            };

            // Reset status for recurring bills
            $this->status = 'pending';
        }
    }

    protected function applyReminderScheduled(ReminderScheduled $event): void
    {
        $this->reminders[] = [
            'date' => $event->reminderDate,
            'message' => $event->reminderMessage,
            'status' => 'scheduled',
        ];
    }

    protected function applyReminderSent(ReminderSent $event): void
    {
        // Find the associated reminder and mark as sent
        foreach ($this->reminders as $key => $reminder) {
            if ($reminder['status'] === 'scheduled') {
                $this->reminders[$key]['status'] = 'sent';
                $this->reminders[$key]['sent_at'] = $event->sentAt;
                break;
            }
        }
    }
}
