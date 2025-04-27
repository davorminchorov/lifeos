<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    /**
     * Get payment history with optional filters
     */
    public function index(Request $request)
    {
        $query = Payment::query()
            ->join('subscriptions', 'payments.subscription_id', '=', 'subscriptions.id')
            ->select(
                'payments.*',
                'subscriptions.name as subscription_name',
                'subscriptions.category'
            );

        // Apply filters if provided
        if ($request->subscription_id && $request->subscription_id !== 'all') {
            $query->where('subscription_id', $request->subscription_id);
        }

        if ($request->from_date) {
            $query->where('payment_date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->where('payment_date', '<=', $request->to_date);
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();

        // Get summary statistics
        $total_spent = $payments->sum('amount');
        $payments_count = $payments->count();
        $average_payment = $payments_count > 0 ? $total_spent / $payments_count : 0;

        // Calculate current month and previous month totals
        $now = now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $previousMonthStart = $now->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $now->copy()->subMonth()->endOfMonth();

        $this_month = Payment::where('payment_date', '>=', $currentMonthStart)
            ->where('payment_date', '<=', $now)
            ->sum('amount');

        $previous_month = Payment::where('payment_date', '>=', $previousMonthStart)
            ->where('payment_date', '<=', $previousMonthEnd)
            ->sum('amount');

        $summary = [
            'total_spent' => $total_spent,
            'payments_count' => $payments_count,
            'average_payment' => $average_payment,
            'this_month' => $this_month,
            'previous_month' => $previous_month
        ];

        return response()->json([
            'payments' => $payments,
            'summary' => $summary
        ]);
    }

    /**
     * Export payment history to CSV
     */
    public function export(Request $request)
    {
        $query = Payment::query()
            ->join('subscriptions', 'payments.subscription_id', '=', 'subscriptions.id')
            ->select(
                'payments.id',
                'payments.payment_date',
                'subscriptions.name as subscription_name',
                'payments.amount',
                'payments.currency',
                'payments.payment_method',
                'subscriptions.category',
                'payments.notes'
            );

        // Apply filters if provided
        if ($request->subscription_id && $request->subscription_id !== 'all') {
            $query->where('subscription_id', $request->subscription_id);
        }

        if ($request->from_date) {
            $query->where('payment_date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->where('payment_date', '<=', $request->to_date);
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();

        // Create CSV content
        $headers = [
            'ID',
            'Date',
            'Subscription',
            'Amount',
            'Currency',
            'Payment Method',
            'Category',
            'Notes'
        ];

        $csvContent = implode(',', $headers) . "\n";

        foreach ($payments as $payment) {
            $row = [
                $payment->id,
                $payment->payment_date,
                '"' . str_replace('"', '""', $payment->subscription_name) . '"',
                $payment->amount,
                $payment->currency,
                str_replace('_', ' ', $payment->payment_method),
                $payment->category,
                '"' . str_replace('"', '""', $payment->notes) . '"'
            ];
            $csvContent .= implode(',', $row) . "\n";
        }

        $filename = 'payment_history_' . date('Y-m-d') . '.csv';

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }

    /**
     * Get list of subscriptions for dropdown
     */
    public function getSubscriptionsList()
    {
        $subscriptions = Subscription::select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'subscriptions' => $subscriptions
        ]);
    }

    /**
     * Record a new payment for a subscription
     */
    public function store(Request $request, Subscription $subscription)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        $payment = new Payment([
            'amount' => $request->amount,
            'currency' => $subscription->currency,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->input('payment_method', 'credit_card'),
            'notes' => $request->notes
        ]);

        $subscription->payments()->save($payment);

        return response()->json($payment, Response::HTTP_CREATED);
    }
}
