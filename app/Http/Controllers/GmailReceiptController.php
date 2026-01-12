<?php

namespace App\Http\Controllers;

use App\Jobs\ParseReceiptAndCreateExpense;
use App\Jobs\ProcessGmailReceipts;
use App\Models\GmailConnection;
use App\Models\ProcessedEmail;
use App\Services\GmailService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GmailReceiptController extends Controller
{
    protected GmailService $gmailService;

    public function __construct(GmailService $gmailService)
    {
        $this->gmailService = $gmailService;
    }

    /**
     * Display Gmail receipt settings page.
     */
    public function settings()
    {
        // Validate Gmail credentials are configured
        if (! config('gmail_receipts.client_id') || ! config('gmail_receipts.client_secret')) {
            return view('settings.gmail-receipts', [
                'connection' => null,
                'stats' => null,
                'error' => 'Gmail API credentials are not configured. Please set GMAIL_CLIENT_ID and GMAIL_CLIENT_SECRET in your .env file.',
            ]);
        }

        $connection = auth()->user()->gmailConnections()->first();

        $stats = null;
        if ($connection) {
            $stats = [
                'total_processed' => ProcessedEmail::where('user_id', auth()->id())
                    ->processed()
                    ->count(),
                'pending' => ProcessedEmail::where('user_id', auth()->id())
                    ->pending()
                    ->count(),
                'failed' => ProcessedEmail::where('user_id', auth()->id())
                    ->failed()
                    ->count(),
                'last_synced' => $connection->last_synced_at?->diffForHumans(),
            ];
        }

        return view('settings.gmail-receipts', [
            'connection' => $connection,
            'stats' => $stats,
        ]);
    }

    /**
     * Initiate OAuth flow to connect Gmail.
     */
    public function connect()
    {
        try {
            // Generate random state for CSRF protection
            $state = bin2hex(random_bytes(32));
            session(['gmail_oauth_state' => $state]);

            $authUrl = $this->gmailService->getAuthUrl($state);

            return redirect()->away($authUrl);
        } catch (\RuntimeException $e) {
            // Configuration error - show specific message
            return redirect()
                ->route('settings.gmail-receipts')
                ->with('error', $e->getMessage());
        } catch (Exception $e) {
            Log::error('Failed to initiate Gmail OAuth', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('settings.gmail-receipts')
                ->with('error', 'Failed to connect to Gmail. Please try again.');
        }
    }

    /**
     * Handle OAuth callback from Google.
     */
    public function callback(Request $request)
    {
        try {
            // Check for error from Google
            if ($request->has('error')) {
                Log::warning('Gmail OAuth denied', [
                    'user_id' => auth()->id(),
                    'error' => $request->error,
                ]);

                session()->forget('gmail_oauth_state');

                return redirect()
                    ->route('settings.gmail-receipts')
                    ->with('error', 'Gmail connection was cancelled.');
            }

            // Validate state parameter for CSRF protection
            $sessionState = session('gmail_oauth_state');
            $requestState = $request->input('state');

            if (! $sessionState || ! $requestState || ! hash_equals($sessionState, $requestState)) {
                Log::warning('Gmail OAuth state mismatch', [
                    'user_id' => auth()->id(),
                ]);

                session()->forget('gmail_oauth_state');

                return redirect()
                    ->route('settings.gmail-receipts')
                    ->with('error', 'Invalid OAuth state. Please try again.');
            }

            // Clean up state from session
            session()->forget('gmail_oauth_state');

            // Validate auth code
            if (! $request->has('code')) {
                return redirect()
                    ->route('settings.gmail-receipts')
                    ->with('error', 'Invalid authorization code.');
            }

            // Complete authentication
            $connection = $this->gmailService->authenticate(auth()->user(), $request->code);

            // Dispatch initial sync job
            ProcessGmailReceipts::dispatch($connection, true);

            return redirect()
                ->route('settings.gmail-receipts')
                ->with('success', 'Gmail connected successfully! Your receipts are being synced in the background.');
        } catch (\RuntimeException $e) {
            // Configuration error - show specific message
            session()->forget('gmail_oauth_state');

            return redirect()
                ->route('settings.gmail-receipts')
                ->with('error', $e->getMessage());
        } catch (Exception $e) {
            Log::error('Gmail OAuth callback failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            session()->forget('gmail_oauth_state');

            return redirect()
                ->route('settings.gmail-receipts')
                ->with('error', 'Failed to complete Gmail connection. Please try again.');
        }
    }

    /**
     * Disconnect Gmail and revoke access.
     */
    public function disconnect()
    {
        try {
            $connection = auth()->user()->gmailConnections()->first();

            if (! $connection) {
                return redirect()
                    ->route('settings.gmail-receipts')
                    ->with('error', 'No Gmail connection found.');
            }

            $success = $this->gmailService->disconnect($connection);

            if (! $success) {
                Log::warning('Gmail token revocation failed but connection removed', [
                    'user_id' => auth()->id(),
                    'connection_id' => $connection->id,
                ]);

                return redirect()
                    ->route('settings.gmail-receipts')
                    ->with('warning', 'Gmail connection removed, but token revocation failed. You may need to revoke access manually from your Google Account settings.');
            }

            return redirect()
                ->route('settings.gmail-receipts')
                ->with('success', 'Gmail disconnected successfully.');
        } catch (Exception $e) {
            Log::error('Failed to disconnect Gmail', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('settings.gmail-receipts')
                ->with('error', 'Failed to disconnect Gmail. Please try again.');
        }
    }

    /**
     * Manually trigger a sync.
     */
    public function sync()
    {
        try {
            $connection = auth()->user()->gmailConnections()->first();

            if (! $connection) {
                return redirect()
                    ->route('settings.gmail-receipts')
                    ->with('error', 'No Gmail connection found.');
            }

            if (! $connection->isActive()) {
                return redirect()
                    ->route('settings.gmail-receipts')
                    ->with('error', 'Gmail connection is not active. Please reconnect.');
            }

            // Dispatch sync job
            ProcessGmailReceipts::dispatch($connection, false);

            return redirect()
                ->route('settings.gmail-receipts')
                ->with('success', 'Sync started! Your receipts are being processed in the background.');
        } catch (Exception $e) {
            Log::error('Failed to trigger Gmail sync', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('settings.gmail-receipts')
                ->with('error', 'Failed to start sync. Please try again.');
        }
    }

    /**
     * Toggle auto-sync on/off.
     */
    public function toggleAutoSync(Request $request)
    {
        try {
            $connection = auth()->user()->gmailConnections()->first();

            if (! $connection) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Gmail connection found.',
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
     * Get processed emails for display.
     */
    public function processedEmails(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:pending,processed,failed,skipped',
        ]);

        $query = ProcessedEmail::where('user_id', auth()->id())
            ->with('expense')
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('processing_status', $request->status);
        }

        $emails = $query->paginate(20);

        return view('settings.gmail-receipts.emails', [
            'emails' => $emails,
        ]);
    }

    /**
     * Retry processing a failed email.
     */
    public function retryEmail(ProcessedEmail $processedEmail)
    {
        try {
            // Authorize
            if ($processedEmail->user_id !== auth()->id()) {
                abort(403);
            }

            $connection = auth()->user()->gmailConnections()->first();

            if (! $connection) {
                return redirect()
                    ->back()
                    ->with('error', 'No Gmail connection found.');
            }

            // Reset status to pending
            $processedEmail->update([
                'processing_status' => ProcessedEmail::STATUS_PENDING,
                'failure_reason' => null,
            ]);

            // Re-dispatch parsing job
            ParseReceiptAndCreateExpense::dispatch($processedEmail, $connection);

            return redirect()
                ->back()
                ->with('success', 'Email queued for re-processing.');
        } catch (Exception $e) {
            Log::error('Failed to retry email processing', [
                'processed_email_id' => $processedEmail->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to retry processing. Please try again.');
        }
    }
}
