<?php

namespace App\Http\Controllers;

use App\Http\Requests\CycleMenus\UpdateCycleMenuDayRequest;
use App\Models\CycleMenuDay;
use Illuminate\Http\RedirectResponse;

class CycleMenuDayController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function update(UpdateCycleMenuDayRequest $request, CycleMenuDay $cycle_menu_day): RedirectResponse
    {
        $this->authorize('update', $cycle_menu_day);
        $cycle_menu_day->update($request->validated());

        $menu = $cycle_menu_day->menu()->first();

        return redirect()->route('cycle-menus.show', $menu)
            ->with('status', 'Day updated.');
    }
}
