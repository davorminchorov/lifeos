<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::where('user_id', auth()->id());

        // Search by name, email, or company
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $customers = $query->paginate($request->get('per_page', 20));

        // Calculate summary statistics
        $summary = [
            'total_customers' => Customer::where('user_id', auth()->id())->count(),
            'total_outstanding' => Customer::where('user_id', auth()->id())
                ->get()
                ->sum('outstanding_balance'),
            'total_credit' => Customer::where('user_id', auth()->id())
                ->get()
                ->sum('credit_balance'),
        ];

        return view('customers.index', compact('customers', 'summary'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $customer = Customer::create($data);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        // Ensure user owns this customer
        if ($customer->user_id !== auth()->id()) {
            abort(403);
        }

        // Load relationships
        $customer->load(['invoices' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        // Ensure user owns this customer
        if ($customer->user_id !== auth()->id()) {
            abort(403);
        }

        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        // Ensure user owns this customer
        if ($customer->user_id !== auth()->id()) {
            abort(403);
        }

        $customer->update($request->validated());

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        // Ensure user owns this customer
        if ($customer->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if customer has invoices
        if ($customer->invoices()->count() > 0) {
            return redirect()->route('customers.index')
                ->with('error', 'Cannot delete customer with existing invoices.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully!');
    }
}
