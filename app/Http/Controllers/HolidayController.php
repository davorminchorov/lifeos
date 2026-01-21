<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\CountryHolidayService;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function __construct(
        private CountryHolidayService $countryHolidayService
    ) {}

    /**
     * Display a listing of holidays based on tenant's default country.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $tenant = $user->currentTenant;

        if (!$tenant) {
            abort(403, 'No tenant selected');
        }

        // Get tenant's holidays from database
        $holidays = $this->countryHolidayService->getTenantCurrentYearHolidays($tenant);

        // If no holidays exist, sync them from config
        if ($holidays->isEmpty()) {
            $this->countryHolidayService->syncTenantHolidays($tenant);
            $holidays = $this->countryHolidayService->getTenantCurrentYearHolidays($tenant);
        }

        // Get country information
        $countryCode = $this->countryHolidayService->getTenantDefaultCountry($tenant);
        $countryName = $this->countryHolidayService->getCountryName($countryCode);

        return view('holidays.index', compact('holidays', 'countryName', 'countryCode'));
    }
}
