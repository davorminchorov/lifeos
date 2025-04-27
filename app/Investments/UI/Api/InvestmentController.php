<?php

namespace App\Investments\UI\Api;

use App\Http\Controllers\Controller;
use App\Investments\Commands\CreateInvestment;
use App\Investments\Commands\RecordTransaction;
use App\Investments\Commands\UpdateValuation;
use App\Investments\Projections\InvestmentList;
use App\Investments\Projections\TransactionList;
use App\Investments\Projections\ValuationList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InvestmentController extends Controller
{
    /**
     * Get a list of all investments
     */
    public function index(): JsonResponse
    {
        $investments = InvestmentList::all();

        return response()->json($investments);
    }

    /**
     * Get a specific investment with its transactions and valuations
     */
    public function show(string $id): JsonResponse
    {
        $investment = InvestmentList::findOrFail($id);
        $transactions = TransactionList::where('investment_id', $id)->orderBy('date', 'desc')->get();
        $valuations = ValuationList::where('investment_id', $id)->orderBy('date', 'desc')->get();

        return response()->json([
            'investment' => $investment,
            'transactions' => $transactions,
            'valuations' => $valuations,
        ]);
    }

    /**
     * Create a new investment
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => ['required', 'string', Rule::in(['stock', 'bond', 'mutual_fund', 'etf', 'real_estate', 'retirement', 'life_insurance', 'other'])],
            'institution' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'initial_investment' => 'required|numeric|min:0.01',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $investmentId = (string) Str::uuid();

        dispatch(new CreateInvestment(
            $investmentId,
            $request->name,
            $request->type,
            $request->institution,
            $request->account_number,
            $request->initial_investment,
            $request->start_date,
            $request->end_date,
            $request->description
        ));

        return response()->json([
            'id' => $investmentId,
            'message' => 'Investment created successfully',
        ], 201);
    }

    /**
     * Record a transaction for an investment
     */
    public function recordTransaction(Request $request, string $id): JsonResponse
    {
        // Validate the investment exists
        InvestmentList::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'type' => ['required', 'string', Rule::in(['deposit', 'withdrawal', 'dividend', 'fee', 'interest'])],
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $transactionId = (string) Str::uuid();

        dispatch(new RecordTransaction(
            $id,
            $transactionId,
            $request->type,
            $request->amount,
            $request->date,
            $request->notes
        ));

        return response()->json([
            'id' => $transactionId,
            'message' => 'Transaction recorded successfully',
        ]);
    }

    /**
     * Update the valuation of an investment
     */
    public function updateValuation(Request $request, string $id): JsonResponse
    {
        // Validate the investment exists
        InvestmentList::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'value' => 'required|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        dispatch(new UpdateValuation(
            $id,
            $request->value,
            $request->date,
            $request->notes
        ));

        return response()->json([
            'message' => 'Valuation updated successfully',
        ]);
    }

    /**
     * Get performance data for an investment
     */
    public function getPerformance(string $id): JsonResponse
    {
        $investment = InvestmentList::findOrFail($id);
        $valuations = ValuationList::where('investment_id', $id)
            ->orderBy('date', 'asc')
            ->get();

        // Transform the data for a time series
        $performanceData = $valuations->map(function ($valuation) {
            return [
                'date' => $valuation->date->format('Y-m-d'),
                'value' => $valuation->value,
            ];
        });

        // Calculate metrics
        $initialValue = $investment->initial_investment;
        $currentValue = $investment->current_value;
        $totalReturn = (($currentValue - $initialValue) / $initialValue) * 100;

        return response()->json([
            'roi' => $investment->roi,
            'total_return' => round($totalReturn, 2),
            'initial_value' => $initialValue,
            'current_value' => $currentValue,
            'total_invested' => $investment->total_invested,
            'total_withdrawn' => $investment->total_withdrawn,
            'time_series' => $performanceData,
        ]);
    }

    /**
     * Get portfolio summary
     */
    public function getPortfolioSummary(): JsonResponse
    {
        // Get all investments
        $investments = InvestmentList::all();

        // Calculate totals
        $totalInvested = $investments->sum('total_invested');
        $totalCurrentValue = $investments->sum('current_value');
        $totalWithdrawn = $investments->sum('total_withdrawn');

        // Group by type
        $byType = $investments->groupBy('type')->map(function ($group) {
            return [
                'count' => $group->count(),
                'value' => $group->sum('current_value'),
                'percentage' => 0, // Will calculate below
            ];
        });

        // Calculate percentages
        foreach ($byType as $type => $data) {
            $byType[$type]['percentage'] = ($totalCurrentValue > 0)
                ? round(($data['value'] / $totalCurrentValue) * 100, 2)
                : 0;
        }

        // Calculate overall ROI
        $overallROI = ($totalInvested > 0)
            ? (($totalCurrentValue + $totalWithdrawn - $totalInvested) / $totalInvested) * 100
            : 0;

        return response()->json([
            'total_invested' => $totalInvested,
            'total_current_value' => $totalCurrentValue,
            'total_withdrawn' => $totalWithdrawn,
            'overall_roi' => round($overallROI, 2),
            'by_type' => $byType,
            'total_investments' => $investments->count(),
        ]);
    }
}
