<?php

namespace App\Http\Controllers;

use App\Http\Requests\CycleMenus\StoreCycleMenuRequest;
use App\Http\Requests\CycleMenus\UpdateCycleMenuRequest;
use App\Models\CycleMenu;
use App\Models\CycleMenuDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CycleMenuController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(CycleMenu::class, 'cycle_menu');
    }

    public function index(Request $request): View
    {
        $menus = CycleMenu::query()
            ->where('user_id', auth()->id())
            ->with(['days' => function ($q) {
                $q->with('items');
            }])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10);

        return view('cycle-menus.index', [
            'menus' => $menus,
        ]);
    }

    public function create(): View
    {
        return view('cycle-menus.create');
    }

    public function store(StoreCycleMenuRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $menu = CycleMenu::create($data);

        // Ensure Day records exist for the cycle length (0..length-1)
        $length = $menu->cycle_length_days;
        for ($i = 0; $i < $length; $i++) {
            CycleMenuDay::firstOrCreate([
                'cycle_menu_id' => $menu->id,
                'day_index' => $i,
            ]);
        }

        return redirect()->route('cycle-menus.show', $menu)->with('status', 'Cycle menu created.');
    }

    public function show(CycleMenu $cycle_menu): View
    {
        $cycle_menu->load(['days.items']);
        // Map days by index for rendering grid easily
        $daysByIndex = $cycle_menu->days->keyBy('day_index');

        return view('cycle-menus.show', [
            'menu' => $cycle_menu,
            'daysByIndex' => $daysByIndex,
        ]);
    }

    public function edit(CycleMenu $cycle_menu): View
    {
        return view('cycle-menus.edit', ['menu' => $cycle_menu]);
    }

    public function update(UpdateCycleMenuRequest $request, CycleMenu $cycle_menu): RedirectResponse
    {
        $cycle_menu->update($request->validated());

        // If cycle length changed, ensure Day records exist up to new length
        $length = $cycle_menu->cycle_length_days;
        for ($i = 0; $i < $length; $i++) {
            CycleMenuDay::firstOrCreate([
                'cycle_menu_id' => $cycle_menu->id,
                'day_index' => $i,
            ]);
        }

        // Optionally, we will not delete extra days automatically to avoid data loss

        return redirect()->route('cycle-menus.show', $cycle_menu)->with('status', 'Cycle menu updated.');
    }

    public function destroy(CycleMenu $cycle_menu): RedirectResponse
    {
        $cycle_menu->delete();

        return redirect()->route('cycle-menus.index')->with('status', 'Cycle menu deleted.');
    }
}
