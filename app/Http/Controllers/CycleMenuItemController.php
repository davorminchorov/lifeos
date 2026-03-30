<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CycleMenus\StoreCycleMenuItemRequest;
use App\Http\Requests\CycleMenus\UpdateCycleMenuItemRequest;
use App\Http\Requests\ImportCycleMenuItemsCsvRequest;
use App\Jobs\ImportCycleMenuItemsCsv;
use App\Models\CycleMenu;
use App\Models\CycleMenuDay;
use App\Models\CycleMenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class CycleMenuItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(StoreCycleMenuItemRequest $request): RedirectResponse
    {
        $this->authorize('create', CycleMenuItem::class);
        $data = $request->validated();

        // Auto-assign position to end if not provided
        if (! isset($data['position'])) {
            $maxPosition = CycleMenuItem::query()
                ->where('cycle_menu_day_id', $data['cycle_menu_day_id'])
                ->max('position');
            $data['position'] = is_null($maxPosition) ? 0 : $maxPosition + 1;
        }

        $item = CycleMenuItem::create($data);

        $day = CycleMenuDay::findOrFail($item->cycle_menu_day_id)->load('menu');

        return redirect()->route('cycle-menus.show', $day->menu)
            ->with('status', 'Item added to the day.');
    }

    public function update(UpdateCycleMenuItemRequest $request, CycleMenuItem $cycle_menu_item): RedirectResponse
    {
        $this->authorize('update', $cycle_menu_item);
        $cycle_menu_item->update($request->validated());

        $day = $cycle_menu_item->day()->with('menu')->first();

        return redirect()->route('cycle-menus.show', $day->menu)
            ->with('status', 'Item updated.');
    }

    public function destroy(CycleMenuItem $cycle_menu_item): RedirectResponse
    {
        $this->authorize('delete', $cycle_menu_item);
        $day = $cycle_menu_item->day()->with('menu')->first();
        $cycle_menu_item->delete();

        return redirect()->route('cycle-menus.show', $day->menu)
            ->with('status', 'Item removed.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'orders' => ['required', 'array'],
            'orders.*.id' => ['required', 'exists:cycle_menu_items,id'],
            'orders.*.position' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['orders'] as $entry) {
            $item = CycleMenuItem::findOrFail($entry['id']);
            $this->authorize('update', $item);
            $item->update(['position' => $entry['position']]);
        }

        // Try to redirect back to the menu show page if referer includes it
        return back()->with('status', 'Items reordered.');
    }

    /**
     * Show the import CSV form page.
     */
    public function importForm(): Response
    {
        $menus = CycleMenu::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name', 'cycle_length_days', 'is_active']);

        return Inertia::render('CycleMenus/Import', [
            'menus' => $menus,
        ]);
    }

    /**
     * Queue an import of cycle menu items from an uploaded CSV.
     */
    public function importCsv(ImportCycleMenuItemsCsvRequest $request): JsonResponse|RedirectResponse
    {
        $userId = auth()->id();
        $tenantId = auth()->user()->current_tenant_id;
        $cycleMenuId = (int) $request->validated('cycle_menu_id');
        $file = $request->file('file');

        $storedPath = $file->storeAs('imports/'.$userId, uniqid('cycle_menu_items_').'.csv');

        ImportCycleMenuItemsCsv::dispatch($cycleMenuId, $userId, $tenantId, $storedPath)->onQueue('imports');

        if ($request->expectsJson()) {
            return new JsonResponse(['status' => 'queued']);
        }

        return redirect()->route('cycle-menus.index')
            ->with('success', 'Your CSV import has been queued and will be processed shortly.');
    }

    /**
     * Return the current import progress for the authenticated user.
     */
    public function importProgress(): JsonResponse
    {
        $progress = Cache::get('cycle_menu_items_import_progress:'.auth()->id());

        return new JsonResponse($progress ?? ['status' => 'idle']);
    }
}
