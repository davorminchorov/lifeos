<?php

namespace App\Subscriptions\UI\Api;

use App\Core\EventSourcing\CommandBus;
use App\Core\EventSourcing\QueryBus;
use App\Subscriptions\Commands\AddSubscription;
use App\Subscriptions\Commands\CancelSubscription;
use App\Subscriptions\Commands\RecordPayment;
use App\Subscriptions\Commands\UpdateSubscription;
use App\Subscriptions\Commands\ConfigureReminders;
use App\Subscriptions\Queries\GetSubscriptionDetail;
use App\Subscriptions\Queries\GetSubscriptionList;
use App\Subscriptions\Queries\GetUpcomingPayments;
use App\Subscriptions\Queries\GetUpcomingReminders;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = new GetSubscriptionList(
            status: $request->get('status'),
            category: $request->get('category'),
            search: $request->get('search'),
            sortBy: $request->get('sort_by', 'name'),
            sortDirection: $request->get('sort_direction', 'asc'),
            perPage: (int) $request->get('per_page', 10),
            page: (int) $request->get('page', 1)
        );

        $result = $this->queryBus->handle($query);

        return response()->json($result);
    }

    public function show(string $id): JsonResponse
    {
        $query = new GetSubscriptionDetail($id);
        $result = $this->queryBus->handle($query);

        if (!$result) {
            return response()->json(['error' => 'Subscription not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($result);
    }

    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'billing_cycle' => 'required|string|in:daily,weekly,biweekly,monthly,bimonthly,quarterly,semiannually,annually',
            'start_date' => 'required|date_format:Y-m-d',
            'website' => 'nullable|url|max:255',
            'category' => 'nullable|string|max:50',
        ]);

        $command = AddSubscription::create(
            name: $request->input('name'),
            description: $request->input('description'),
            amount: (float) $request->input('amount'),
            currency: $request->input('currency'),
            billingCycle: $request->input('billing_cycle'),
            startDate: $request->input('start_date'),
            website: $request->input('website'),
            category: $request->input('category')
        );

        $this->commandBus->dispatch($command);

        return response()->json([
            'message' => 'Subscription created successfully',
            'subscription_id' => $command->subscriptionId
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'billing_cycle' => 'required|string|in:daily,weekly,biweekly,monthly,bimonthly,quarterly,semiannually,annually',
            'website' => 'nullable|url|max:255',
            'category' => 'nullable|string|max:50',
        ]);

        $command = new UpdateSubscription(
            subscriptionId: $id,
            name: $request->input('name'),
            description: $request->input('description'),
            amount: (float) $request->input('amount'),
            currency: $request->input('currency'),
            billingCycle: $request->input('billing_cycle'),
            website: $request->input('website'),
            category: $request->input('category')
        );

        try {
            $this->commandBus->dispatch($command);
            return response()->json(['message' => 'Subscription updated successfully']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        $this->validate($request, [
            'end_date' => 'required|date_format:Y-m-d',
        ]);

        $command = new CancelSubscription(
            subscriptionId: $id,
            endDate: $request->input('end_date')
        );

        try {
            $this->commandBus->dispatch($command);
            return response()->json(['message' => 'Subscription cancelled successfully']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function recordPayment(Request $request, string $id): JsonResponse
    {
        $this->validate($request, [
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date_format:Y-m-d',
            'notes' => 'nullable|string',
        ]);

        $command = RecordPayment::create(
            subscriptionId: $id,
            amount: (float) $request->input('amount'),
            paymentDate: $request->input('payment_date'),
            notes: $request->input('notes')
        );

        try {
            $this->commandBus->dispatch($command);
            return response()->json([
                'message' => 'Payment recorded successfully',
                'payment_id' => $command->paymentId
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function upcomingPayments(Request $request): JsonResponse
    {
        $query = new GetUpcomingPayments(
            daysAhead: (int) $request->get('days_ahead', 30)
        );

        $result = $this->queryBus->handle($query);

        return response()->json($result);
    }

    public function configureReminders(Request $request, string $id): JsonResponse
    {
        $this->validate($request, [
            'days_before' => 'required|integer|min:1',
            'enabled' => 'required|boolean',
            'method' => 'required|string|in:email,sms,push,in_app',
        ]);

        $command = new ConfigureReminders(
            subscriptionId: $id,
            daysBefore: (int) $request->input('days_before'),
            enabled: (bool) $request->input('enabled'),
            method: $request->input('method')
        );

        try {
            $this->commandBus->dispatch($command);
            return response()->json(['message' => 'Subscription reminders configured successfully']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function upcomingReminders(Request $request): JsonResponse
    {
        $query = new GetUpcomingReminders(
            daysAhead: (int) $request->get('days_ahead', 14)
        );

        $result = $this->queryBus->handle($query);

        return response()->json($result);
    }
}
