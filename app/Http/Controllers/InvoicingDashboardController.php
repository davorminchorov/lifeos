<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoicingDashboardController extends Controller
{
    /**
     * Display the invoicing dashboard.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();

        // Date range filter (default to last 30 days)
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Summary Statistics
        $summary = [
            'total_invoices' => Invoice::where('user_id', $userId)->count(),
            'total_customers' => Customer::where('user_id', $userId)->count(),
            'total_revenue' => Invoice::where('user_id', $userId)
                ->whereIn('status', [InvoiceStatus::PAID, InvoiceStatus::PARTIALLY_PAID])
                ->sum('amount_paid'),
            'outstanding_amount' => Invoice::where('user_id', $userId)
                ->whereIn('status', [InvoiceStatus::ISSUED, InvoiceStatus::PARTIALLY_PAID, InvoiceStatus::PAST_DUE])
                ->sum('amount_due'),
            'draft_invoices' => Invoice::where('user_id', $userId)
                ->where('status', InvoiceStatus::DRAFT)
                ->count(),
            'overdue_invoices' => Invoice::where('user_id', $userId)
                ->where('status', InvoiceStatus::PAST_DUE)
                ->count(),
            'available_credit' => CreditNote::where('user_id', $userId)
                ->where('status', 'available')
                ->sum('remaining_amount'),
        ];

        // Revenue by month (last 6 months)
        $revenueByMonth = Payment::where('payments.user_id', $userId)
            ->select(
                DB::raw('DATE_FORMAT(payment_date, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total')
            )
            ->where('payment_date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        // Top customers by revenue
        $topCustomers = Customer::where('customers.user_id', $userId)
            ->select('customers.*')
            ->selectRaw('SUM(invoices.amount_paid) as total_revenue')
            ->leftJoin('invoices', 'customers.id', '=', 'invoices.customer_id')
            ->groupBy('customers.id')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // Recent invoices
        $recentInvoices = Invoice::where('user_id', $userId)
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Recent payments
        $recentPayments = Payment::where('payments.user_id', $userId)
            ->with('invoice.customer')
            ->orderBy('payment_date', 'desc')
            ->limit(10)
            ->get();

        // Invoice status breakdown
        $statusBreakdown = Invoice::where('user_id', $userId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        return view('invoicing.dashboard', compact(
            'summary',
            'revenueByMonth',
            'topCustomers',
            'recentInvoices',
            'recentPayments',
            'statusBreakdown',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export invoices to CSV.
     */
    public function exportInvoices(Request $request)
    {
        $query = Invoice::where('user_id', auth()->id())
            ->with('customer');

        // Apply filters
        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->orderBy('created_at', 'desc')->get();

        $filename = 'invoices_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($invoices) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'Invoice Number',
                'Customer',
                'Status',
                'Currency',
                'Subtotal',
                'Tax Total',
                'Total',
                'Amount Paid',
                'Amount Due',
                'Issued Date',
                'Due Date',
                'Created Date',
            ]);

            // Data
            foreach ($invoices as $invoice) {
                fputcsv($file, [
                    $invoice->number ?? 'Draft',
                    $invoice->customer->name,
                    $invoice->status->label(),
                    $invoice->currency,
                    $invoice->subtotal / 100,
                    $invoice->tax_total / 100,
                    $invoice->total / 100,
                    $invoice->amount_paid / 100,
                    $invoice->amount_due / 100,
                    $invoice->issued_at?->format('Y-m-d'),
                    $invoice->due_at?->format('Y-m-d'),
                    $invoice->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export payments to CSV.
     */
    public function exportPayments(Request $request)
    {
        $query = Payment::where('payments.user_id', auth()->id())
            ->with('invoice.customer');

        // Apply filters
        if ($request->filled('start_date')) {
            $query->where('payment_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('payment_date', '<=', $request->end_date);
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();

        $filename = 'payments_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'Payment Date',
                'Invoice Number',
                'Customer',
                'Amount',
                'Payment Method',
                'Reference',
                'Notes',
            ]);

            // Data
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->payment_date,
                    $payment->invoice->number ?? 'Draft',
                    $payment->invoice->customer->name,
                    $payment->amount / 100,
                    ucwords(str_replace('_', ' ', $payment->payment_method)),
                    $payment->reference ?? '',
                    $payment->notes ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
