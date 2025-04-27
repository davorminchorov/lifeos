<?php

namespace App\UtilityBills\UI\Api;

use App\Http\Controllers\Controller;
use App\UtilityBills\Commands\AddBill;
use App\UtilityBills\Commands\PayBill;
use App\UtilityBills\Commands\ScheduleReminder;
use App\UtilityBills\Commands\UpdateBill;
use App\UtilityBills\Queries\GetBillById;
use App\UtilityBills\Queries\GetBills;
use App\UtilityBills\Queries\GetPendingBills;
use App\UtilityBills\Queries\GetUpcomingReminders;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Core\Commands\CommandBus;
use App\Core\Queries\QueryBus;

class UtilityBillsController extends Controller
{
    protected ?CommandBus $commandBus = null;
    protected ?QueryBus $queryBus = null;

    public function __construct(CommandBus $commandBus = null, QueryBus $queryBus = null)
    {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }

    public function index()
    {
        $bills = $this->queryBus->handle(new GetBills());

        return response()->json($bills);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'category' => 'required|string|max:255',
            'is_recurring' => 'boolean',
            'recurrence_period' => 'required_if:is_recurring,true|string|in:monthly,bimonthly,quarterly,annually',
            'notes' => 'nullable|string',
        ]);

        $billId = Str::uuid()->toString();

        $command = new AddBill(
            $billId,
            $validated['name'],
            $validated['provider'],
            $validated['amount'],
            $validated['due_date'],
            $validated['category'],
            $validated['is_recurring'] ?? false,
            $validated['recurrence_period'] ?? null,
            $validated['notes'] ?? null
        );

        $this->commandBus->handle($command);

        return response()->json(['id' => $billId], Response::HTTP_CREATED);
    }

    public function show(string $id)
    {
        $bill = $this->queryBus->handle(new GetBillById($id));

        if (!$bill) {
            return response()->json(['message' => 'Bill not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($bill);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'provider' => 'string|max:255',
            'amount' => 'numeric|min:0',
            'due_date' => 'date',
            'category' => 'string|max:255',
            'is_recurring' => 'boolean',
            'recurrence_period' => 'required_if:is_recurring,true|string|in:monthly,bimonthly,quarterly,annually',
            'notes' => 'nullable|string',
        ]);

        $command = new UpdateBill(
            $id,
            $validated['name'] ?? null,
            $validated['provider'] ?? null,
            $validated['amount'] ?? null,
            $validated['due_date'] ?? null,
            $validated['category'] ?? null,
            $validated['is_recurring'] ?? null,
            $validated['recurrence_period'] ?? null,
            $validated['notes'] ?? null
        );

        $this->commandBus->handle($command);

        return response()->json(['message' => 'Bill updated successfully']);
    }

    public function pay(Request $request, string $id)
    {
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $command = new PayBill(
            $id,
            $validated['payment_date'],
            $validated['payment_amount'],
            $validated['payment_method'],
            $validated['notes'] ?? null
        );

        $this->commandBus->handle($command);

        return response()->json(['message' => 'Bill payment recorded successfully']);
    }

    public function scheduleReminder(Request $request, string $id)
    {
        $validated = $request->validate([
            'reminder_date' => 'required|date',
            'reminder_message' => 'nullable|string',
        ]);

        $command = new ScheduleReminder(
            $id,
            $validated['reminder_date'],
            $validated['reminder_message'] ?? "Don't forget to pay your bill!"
        );

        $this->commandBus->handle($command);

        return response()->json(['message' => 'Reminder scheduled successfully']);
    }

    public function pendingBills()
    {
        $pendingBills = $this->queryBus->handle(new GetPendingBills());

        return response()->json($pendingBills);
    }

    public function upcomingReminders()
    {
        $reminders = $this->queryBus->handle(new GetUpcomingReminders());

        return response()->json($reminders);
    }
}
