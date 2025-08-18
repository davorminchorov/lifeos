<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display the main settings page.
     */
    public function index(): View
    {
        return view('settings.index');
    }

    /**
     * Display account settings.
     */
    public function account(): View
    {
        return view('settings.account');
    }

    /**
     * Display application settings.
     */
    public function application(): View
    {
        return view('settings.application');
    }

    /**
     * Display notification settings (redirect to existing preferences).
     */
    public function notifications(): View
    {
        return redirect()->route('notifications.preferences');
    }
}
