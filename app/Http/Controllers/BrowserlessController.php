<?php

namespace App\Http\Controllers;

use App\Jobs\FetchInvestorData;
use App\Models\BrowserlessConnection;
use App\Models\InvestorData;
use App\Services\BrowserlessService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BrowserlessController extends Controller
{
    protected BrowserlessService $browserlessService;

    public function __construct(BrowserlessService $browserlessService)
    {
        $this->browserlessService = $browserlessService;
    }

    /**
     * Display investor portal settings page.
     */
    public function settings()
    {
        try {
            // Validate credentials are configured
            if (! config('browserless.api_key')) {
                return view('settings.investor-portal', [
                    'connection' => null,
                    'stats' => null,
                    'latestData' => null,
                    'error' => 'Browserless API key is not configured. Please set BROWSERLESS_API_KEY in your .env file.',
                ]);
            }

            if (! config('browserless.investor_portal.username') || ! config('browserless.investor_portal.password')) {
                return view('settings.investor-portal', [
                    'connection' => null,
                    'stats' => null,
                    'latestData' => null,
                    'error' => 'Investor portal credentials are not configured. Please set INVESTOR_PORTAL_USERNAME and INVESTOR_PORTAL_PASSWORD in your .env file.',
                ]);
            }

            $connection = auth()->user()->browserlessConnections()->first();

            $stats = null;
            $latestData = null;

            if ($connection) {
                $stats = [
                    'total_crawls' => InvestorData::where('user_id', auth()->id())->count(),
                    'last_synced' => $connection->last_synced_at?->diffForHumans(),
                    'last_successful_sync' => $connection->last_successful_sync_at?->diffForHumans(),
                    'has_recent_failures' => $connection->hasRecentFailures(),
                    'consecutive_failures' => $connection->consecutive_failures,
                ];

                // Get the latest crawled data
                $latestData = InvestorData::latestForUser(auth()->id());
            }

            return view('settings.investor-portal', [
                'connection' => $connection,
                'stats' => $stats,
                'latestData' => $latestData,
            ]);
        } catch (\RuntimeException $e) {
            // Configuration error - show specific message
            return view('settings.investor-portal', [
                'connection' => null,
                'stats' => null,
                'latestData' => null,
                'error' => $e->getMessage(),
            ]);
        } catch (Exception $e) {
            Log::error('Error loading investor portal settings', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return view('settings.investor-portal', [
                'connection' => null,
                'stats' => null,
                'latestData' => null,
                'error' => 'An error occurred while loading the page. Please try again.',
            ]);
        }
    }

    /**
     * Create or enable a connection.
     */
    public function connect()
    {
        try {
            $connection = auth()->user()->browserlessConnections()->first();

            if ($connection) {
                // Re-enable if disabled
                $connection->update([
                    'sync_enabled' => true,
                    'consecutive_failures' => 0,
                    'last_error' => null,
                ]);

                return redirect()
                    ->route('settings.investor-portal')
                    ->with('success', 'Investor portal connection enabled successfully.');
            }

            // Create new connection
            $connection = BrowserlessConnection::create([
                'user_id' => auth()->id(),
                'portal_name' => 'investor.wvpfondovi.mk',
                'sync_enabled' => true,
            ]);

            // Dispatch initial crawl job
            FetchInvestorData::dispatch($connection);

            return redirect()
                ->route('settings.investor-portal')
                ->with('success', 'Investor portal connected successfully! Your data is being fetched in the background.');
        } catch (\RuntimeException $e) {
            // Configuration error - show specific message
            return redirect()
                ->route('settings.investor-portal')
                ->with('error', $e->getMessage());
        } catch (Exception $e) {
            Log::error('Failed to connect to investor portal', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('settings.investor-portal')
                ->with('error', 'Failed to connect to investor portal. Please try again.');
        }
    }

    /**
     * Disconnect and disable the connection.
     */
    public function disconnect()
    {
        try {
            $connection = auth()->user()->browserlessConnections()->first();

            if (! $connection) {
                return redirect()
                    ->route('settings.investor-portal')
                    ->with('error', 'No investor portal connection found.');
            }

            // Just disable, don't delete (preserve historical data)
            $connection->update(['sync_enabled' => false]);

            return redirect()
                ->route('settings.investor-portal')
                ->with('success', 'Investor portal disconnected successfully.');
        } catch (Exception $e) {
            Log::error('Failed to disconnect investor portal', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('settings.investor-portal')
                ->with('error', 'Failed to disconnect investor portal. Please try again.');
        }
    }

    /**
     * Manually trigger a crawl.
     */
    public function sync()
    {
        try {
            $connection = auth()->user()->browserlessConnections()->first();

            if (! $connection) {
                return redirect()
                    ->route('settings.investor-portal')
                    ->with('error', 'No investor portal connection found.');
            }

            if (! $connection->isActive()) {
                return redirect()
                    ->route('settings.investor-portal')
                    ->with('error', 'Investor portal connection is not active. Please reconnect.');
            }

            // Dispatch crawl job
            FetchInvestorData::dispatch($connection);

            return redirect()
                ->route('settings.investor-portal')
                ->with('success', 'Sync started! Your data is being fetched in the background.');
        } catch (Exception $e) {
            Log::error('Failed to trigger investor portal sync', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('settings.investor-portal')
                ->with('error', 'Failed to start sync. Please try again.');
        }
    }

    /**
     * Toggle auto-sync on/off.
     */
    public function toggleAutoSync(Request $request)
    {
        try {
            $connection = auth()->user()->browserlessConnections()->first();

            if (! $connection) {
                return response()->json([
                    'success' => false,
                    'message' => 'No investor portal connection found.',
                ], 404);
            }

            $syncEnabled = $request->boolean('sync_enabled');
            $connection->update(['sync_enabled' => $syncEnabled]);

            return response()->json([
                'success' => true,
                'message' => $syncEnabled
                    ? 'Auto-sync enabled successfully.'
                    : 'Auto-sync disabled successfully.',
                'sync_enabled' => $syncEnabled,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to toggle auto-sync', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update auto-sync setting.',
            ], 500);
        }
    }

    /**
     * View crawled data history.
     */
    public function history(Request $request)
    {
        $query = InvestorData::where('user_id', auth()->id())
            ->orderBy('crawled_at', 'desc');

        $data = $query->paginate(20);

        return view('settings.investor-portal.history', [
            'data' => $data,
        ]);
    }

    /**
     * View a specific crawl result.
     */
    public function show(InvestorData $investorData)
    {
        // Authorize
        if ($investorData->user_id !== auth()->id()) {
            abort(403);
        }

        return view('settings.investor-portal.show', [
            'data' => $investorData,
        ]);
    }

    /**
     * Test the browserless connection.
     */
    public function testConnection()
    {
        try {
            $success = $this->browserlessService->testConnection();

            return response()->json([
                'success' => $success,
                'message' => $success
                    ? 'Browserless connection test successful.'
                    : 'Browserless connection test failed.',
            ]);
        } catch (Exception $e) {
            Log::error('Browserless connection test failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
