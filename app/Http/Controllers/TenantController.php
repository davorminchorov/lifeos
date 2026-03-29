<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TenantController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Display a listing of tenants the user has access to.
     */
    public function index()
    {
        $tenants = $this->getUserAccessibleTenants();

        return Inertia::render('Tenants/Index', [
            'tenants' => $tenants->map(fn ($tenant) => array_merge($tenant->toArray(), [
                'is_owner' => $tenant->owner_id === auth()->id(),
            ])),
        ]);
    }

    /**
     * Show the form for creating a new tenant.
     */
    public function create()
    {
        $this->authorize('create', Tenant::class);

        return Inertia::render('Tenants/Create');
    }

    /**
     * Store a newly created tenant.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Tenant::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tenant = $this->tenantService->createTenant(
            auth()->user(),
            $validated['name']
        );

        return redirect()->route('tenants.show', $tenant)
            ->with('success', 'Tenant created successfully.');
    }

    /**
     * Display the specified tenant.
     */
    public function show(Tenant $tenant)
    {
        $this->authorize('view', $tenant);

        $members = $tenant->members()->withPivot('role')->get();
        $isOwner = $tenant->owner_id === auth()->id();

        return Inertia::render('Tenants/Show', [
            'tenant' => $tenant,
            'members' => $members,
            'isOwner' => $isOwner,
        ]);
    }

    /**
     * Show the form for editing the specified tenant.
     */
    public function edit(Tenant $tenant)
    {
        $this->authorize('update', $tenant);

        return Inertia::render('Tenants/Edit', [
            'tenant' => $tenant,
        ]);
    }

    /**
     * Update the specified tenant.
     */
    public function update(Request $request, Tenant $tenant)
    {
        $this->authorize('update', $tenant);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tenant->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.Str::random(6),
        ]);

        return redirect()->route('tenants.show', $tenant)
            ->with('success', 'Tenant updated successfully.');
    }

    /**
     * Remove the specified tenant.
     */
    public function destroy(Tenant $tenant)
    {
        $this->authorize('delete', $tenant);

        $tenant->delete();

        return redirect()->route('tenants.index')
            ->with('success', 'Tenant deleted successfully.');
    }

    /**
     * Switch to the specified tenant.
     */
    public function switch(Tenant $tenant)
    {
        $this->authorize('switch', $tenant);

        if ($this->tenantService->switchTenant($tenant)) {
            return redirect()->route('dashboard')
                ->with('success', "Switched to {$tenant->name}");
        }

        return back()->with('error', 'Unable to switch to this tenant.');
    }

    /**
     * Show tenant selection page.
     */
    public function select()
    {
        $tenants = $this->getUserAccessibleTenants();

        return Inertia::render('Tenants/Select', [
            'tenants' => $tenants->map(fn ($tenant) => array_merge($tenant->toArray(), [
                'is_owner' => $tenant->owner_id === auth()->id(),
            ])),
        ]);
    }

    /**
     * Get all tenants accessible to the current user.
     */
    private function getUserAccessibleTenants()
    {
        $user = auth()->user();

        return $user->tenants()
            ->withPivot('role')
            ->get()
            ->merge($user->ownedTenants)
            ->unique('id');
    }
}
