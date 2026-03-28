<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Display the main settings page.
     */
    public function index()
    {
        return Inertia::render('Settings/Index');
    }

    /**
     * Display account settings.
     */
    public function account()
    {
        $user = auth()->user();

        return Inertia::render('Settings/Account', [
            'stats' => [
                'member_since' => $user->created_at->format('M Y'),
                'subscriptions_count' => $user->subscriptions()->count(),
                'contracts_count' => $user->contracts()->count(),
                'notifications_count' => $user->notifications()->count(),
                'created_at' => $user->created_at->format('F j, Y \a\t g:i A'),
                'updated_at' => $user->updated_at->format('F j, Y \a\t g:i A'),
            ],
        ]);
    }

    /**
     * Display application settings.
     */
    public function application()
    {
        return Inertia::render('Settings/Application');
    }

    /**
     * Display notification settings (redirect to existing preferences).
     */
    public function notifications()
    {
        return redirect()->route('notifications.preferences');
    }
}
