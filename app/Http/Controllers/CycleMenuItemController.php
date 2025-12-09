<?php

namespace App\Http\Controllers;

use App\Http\Requests\CycleMenus\StoreCycleMenuItemRequest;
use App\Http\Requests\CycleMenus\UpdateCycleMenuItemRequest;
use App\Models\CycleMenuDay;
use App\Models\CycleMenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
}
