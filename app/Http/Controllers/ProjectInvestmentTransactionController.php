<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectInvestmentTransactionRequest;
use App\Http\Requests\UpdateProjectInvestmentTransactionRequest;
use App\Models\ProjectInvestment;
use App\Models\ProjectInvestmentTransaction;

class ProjectInvestmentTransactionController extends Controller
{
    /**
     * Authorize that the user owns the project investment.
     */
    private function authorizeProjectOwnership(ProjectInvestment $projectInvestment): void
    {
        if ($projectInvestment->user_id !== auth()->id()) {
            abort(403);
        }
    }

    /**
     * Authorize that the user owns the transaction.
     */
    private function authorizeTransactionOwnership(ProjectInvestmentTransaction $transaction): void
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }
    }

    /**
     * Display a listing of transactions for a project investment.
     */
    public function index(ProjectInvestment $projectInvestment)
    {
        $this->authorizeProjectOwnership($projectInvestment);

        $transactions = $projectInvestment->transactions()
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('project-investment-transactions.index', compact('projectInvestment', 'transactions'));
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create(ProjectInvestment $projectInvestment)
    {
        $this->authorizeProjectOwnership($projectInvestment);

        return view('project-investment-transactions.create', compact('projectInvestment'));
    }

    /**
     * Store a newly created transaction.
     */
    public function store(StoreProjectInvestmentTransactionRequest $request, ProjectInvestment $projectInvestment)
    {
        $this->authorizeProjectOwnership($projectInvestment);

        $transaction = ProjectInvestmentTransaction::create([
            'project_investment_id' => $projectInvestment->id,
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        return redirect()->route('project-investments.show', $projectInvestment)
            ->with('success', 'Investment transaction added successfully!');
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit(ProjectInvestmentTransaction $projectInvestmentTransaction)
    {
        $this->authorizeTransactionOwnership($projectInvestmentTransaction);

        $projectInvestment = $projectInvestmentTransaction->projectInvestment;

        return view('project-investment-transactions.edit', [
            'transaction' => $projectInvestmentTransaction,
            'projectInvestment' => $projectInvestment,
        ]);
    }

    /**
     * Update the specified transaction.
     */
    public function update(UpdateProjectInvestmentTransactionRequest $request, ProjectInvestmentTransaction $projectInvestmentTransaction)
    {
        $this->authorizeTransactionOwnership($projectInvestmentTransaction);

        $projectInvestmentTransaction->update($request->validated());

        return redirect()->route('project-investments.show', $projectInvestmentTransaction->project_investment_id)
            ->with('success', 'Investment transaction updated successfully!');
    }

    /**
     * Remove the specified transaction.
     */
    public function destroy(ProjectInvestmentTransaction $projectInvestmentTransaction)
    {
        $this->authorizeTransactionOwnership($projectInvestmentTransaction);

        $projectInvestmentId = $projectInvestmentTransaction->project_investment_id;
        $projectInvestmentTransaction->delete();

        return redirect()->route('project-investments.show', $projectInvestmentId)
            ->with('success', 'Investment transaction deleted successfully!');
    }
}
