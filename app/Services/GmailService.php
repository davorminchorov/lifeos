<?php

namespace App\Services;

use App\Models\GmailConnection;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Google\Client as GoogleClient;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GmailService
{
    protected GoogleClient $client;

    protected Gmail $gmail;

    public function __construct()
    {
        $this->initializeClient();
    }

    /**
     * Initialize the Google API client.
     */
    protected function initializeClient(): void
    {
        $this->client = new GoogleClient;
        $this->client->setApplicationName(config('app.name'));
        $this->client->setClientId(config('gmail_receipts.client_id'));
        $this->client->setClientSecret(config('gmail_receipts.client_secret'));
        $this->client->setRedirectUri(config('gmail_receipts.redirect_uri'));
        $this->client->setScopes(config('gmail_receipts.scopes'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    /**
     * Get the authorization URL for OAuth flow.
     */
    public function getAuthUrl(?string $state = null): string
    {
        if ($state) {
            $this->client->setState($state);
        }

        return $this->client->createAuthUrl();
    }

    /**
     * Complete the OAuth flow and store the tokens.
     */
    public function authenticate(User $user, string $authCode): GmailConnection
    {
        try {
            // Exchange authorization code for access token
            $token = $this->client->fetchAccessTokenWithAuthCode($authCode);

            if (isset($token['error'])) {
                throw new Exception('Error fetching access token: '.$token['error']);
            }

            // Get user's email address
            $this->client->setAccessToken($token);
            $oauth = new \Google\Service\Oauth2($this->client);
            $userInfo = $oauth->userinfo->get();

            // Calculate token expiration
            $expiresAt = isset($token['expires_in'])
                ? Carbon::now()->addSeconds($token['expires_in'])
                : null;

            // Find existing connection
            $connection = GmailConnection::where('user_id', $user->id)
                ->where('email_address', $userInfo->email)
                ->first();

            // Prepare update data
            $data = [
                'access_token' => $token['access_token'],
                'token_expires_at' => $expiresAt,
                'sync_enabled' => true,
            ];

            // Only update refresh_token if a new one is provided
            if (isset($token['refresh_token'])) {
                $data['refresh_token'] = $token['refresh_token'];
            }

            if ($connection) {
                // Update existing connection
                $connection->update($data);
            } else {
                // Create new connection (must include refresh_token)
                $data['user_id'] = $user->id;
                $data['email_address'] = $userInfo->email;
                $data['refresh_token'] = $token['refresh_token'] ?? null;
                $connection = GmailConnection::create($data);
            }

            return $connection;
        } catch (Exception $e) {
            Log::error('Gmail authentication failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Refresh the access token for a connection.
     */
    public function refreshToken(GmailConnection $connection): GmailConnection
    {
        try {
            $this->client->setAccessToken([
                'access_token' => $connection->access_token,
                'refresh_token' => $connection->refresh_token,
            ]);

            if ($this->client->isAccessTokenExpired()) {
                $token = $this->client->fetchAccessTokenWithRefreshToken($connection->refresh_token);

                if (isset($token['error'])) {
                    throw new Exception('Error refreshing token: '.$token['error']);
                }

                $expiresAt = isset($token['expires_in'])
                    ? Carbon::now()->addSeconds($token['expires_in'])
                    : null;

                $connection->update([
                    'access_token' => $token['access_token'],
                    'refresh_token' => $token['refresh_token'] ?? $connection->refresh_token,
                    'token_expires_at' => $expiresAt,
                ]);
            }

            return $connection->fresh();
        } catch (Exception $e) {
            Log::error('Gmail token refresh failed', [
                'connection_id' => $connection->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Set up the Gmail service with a connection's credentials.
     */
    protected function setConnection(GmailConnection $connection): void
    {
        // Refresh token if expired
        if ($connection->isTokenExpired()) {
            $connection = $this->refreshToken($connection);
        }

        $this->client->setAccessToken([
            'access_token' => $connection->access_token,
            'refresh_token' => $connection->refresh_token,
        ]);

        $this->gmail = new Gmail($this->client);
    }

    /**
     * Fetch receipt emails from Gmail.
     *
     * @return array Array of message objects
     */
    public function fetchReceiptEmails(GmailConnection $connection, ?Carbon $since = null, int $maxResults = 100): array
    {
        try {
            $this->setConnection($connection);

            // Build search query
            $queries = config('gmail_receipts.search_queries');
            $query = implode(' OR ', array_map(fn ($q) => "({$q})", $queries));

            // Add date filter if provided
            if ($since) {
                $query .= ' after:'.$since->format('Y/m/d');
            }

            // Search for messages
            $messages = [];
            $pageToken = null;

            do {
                $params = [
                    'maxResults' => min($maxResults - count($messages), 100),
                    'q' => $query,
                ];

                if ($pageToken) {
                    $params['pageToken'] = $pageToken;
                }

                $response = $this->gmail->users_messages->listUsersMessages('me', $params);

                foreach ($response->getMessages() as $message) {
                    $messages[] = $this->getMessageDetails($message->getId());

                    if (count($messages) >= $maxResults) {
                        break 2;
                    }
                }

                $pageToken = $response->getNextPageToken();
            } while ($pageToken && count($messages) < $maxResults);

            return $messages;
        } catch (Exception $e) {
            Log::error('Failed to fetch Gmail receipts', [
                'connection_id' => $connection->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get detailed information about a specific message.
     */
    public function getMessageDetails(string $messageId): array
    {
        try {
            $message = $this->gmail->users_messages->get('me', $messageId, ['format' => 'full']);

            return [
                'id' => $message->getId(),
                'thread_id' => $message->getThreadId(),
                'subject' => $this->getHeader($message, 'Subject'),
                'from' => $this->getHeader($message, 'From'),
                'to' => $this->getHeader($message, 'To'),
                'date' => $this->getHeader($message, 'Date'),
                'snippet' => $message->getSnippet(),
                'body' => $this->getMessageBody($message),
                'attachments' => $this->getAttachments($message),
                'labels' => $message->getLabelIds() ?? [],
            ];
        } catch (Exception $e) {
            Log::error('Failed to get message details', [
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Extract a specific header from a message.
     */
    protected function getHeader(Message $message, string $name): ?string
    {
        $headers = $message->getPayload()->getHeaders();

        foreach ($headers as $header) {
            if (strtolower($header->getName()) === strtolower($name)) {
                return $header->getValue();
            }
        }

        return null;
    }

    /**
     * Extract the message body from a Gmail message.
     */
    protected function getMessageBody(Message $message): string
    {
        $payload = $message->getPayload();
        $body = '';

        // Try to get HTML body first, fall back to plain text
        if ($payload->getParts()) {
            foreach ($payload->getParts() as $part) {
                if ($part->getMimeType() === 'text/html') {
                    $body = base64_decode(strtr($part->getBody()->getData(), '-_', '+/'));
                    break;
                } elseif ($part->getMimeType() === 'text/plain' && empty($body)) {
                    $body = base64_decode(strtr($part->getBody()->getData(), '-_', '+/'));
                }

                // Check nested parts
                if ($part->getParts()) {
                    foreach ($part->getParts() as $subPart) {
                        if ($subPart->getMimeType() === 'text/html') {
                            $body = base64_decode(strtr($subPart->getBody()->getData(), '-_', '+/'));
                            break 2;
                        } elseif ($subPart->getMimeType() === 'text/plain' && empty($body)) {
                            $body = base64_decode(strtr($subPart->getBody()->getData(), '-_', '+/'));
                        }
                    }
                }
            }
        } elseif ($payload->getBody()->getData()) {
            $body = base64_decode(strtr($payload->getBody()->getData(), '-_', '+/'));
        }

        // Strip HTML tags for plain text
        return strip_tags($body);
    }

    /**
     * Get list of attachments from a message.
     */
    protected function getAttachments(Message $message): array
    {
        $attachments = [];
        $payload = $message->getPayload();
        $allowedExtensions = config('gmail_receipts.attachment_extensions', []);

        if ($payload->getParts()) {
            foreach ($payload->getParts() as $part) {
                if ($part->getFilename() && $part->getBody()->getAttachmentId()) {
                    $extension = strtolower(pathinfo($part->getFilename(), PATHINFO_EXTENSION));

                    if (in_array($extension, $allowedExtensions)) {
                        $attachments[] = [
                            'filename' => $part->getFilename(),
                            'attachment_id' => $part->getBody()->getAttachmentId(),
                            'mime_type' => $part->getMimeType(),
                            'size' => $part->getBody()->getSize(),
                        ];
                    }
                }
            }
        }

        return $attachments;
    }

    /**
     * Download an attachment from Gmail and store it.
     */
    public function downloadAttachment(GmailConnection $connection, string $messageId, string $attachmentId, string $filename): ?string
    {
        try {
            $this->setConnection($connection);

            $attachment = $this->gmail->users_messages_attachments->get('me', $messageId, $attachmentId);
            $data = base64_decode(strtr($attachment->getData(), '-_', '+/'));

            // Generate storage path
            $userId = $connection->user_id;
            $year = Carbon::now()->year;
            $month = Carbon::now()->format('m');
            $path = config('gmail_receipts.storage_path')."/{$userId}/{$year}/{$month}/";

            // Sanitize filename to prevent path traversal
            $sanitizedFilename = basename($filename);
            $sanitizedFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $sanitizedFilename);
            $sanitizedFilename = substr($sanitizedFilename, 0, 255);

            // Generate unique filename
            $extension = pathinfo($sanitizedFilename, PATHINFO_EXTENSION);
            $basename = pathinfo($sanitizedFilename, PATHINFO_FILENAME);

            // Fallback if basename is empty after sanitization
            if (empty($basename)) {
                $basename = 'receipt_'.bin2hex(random_bytes(8));
            }

            $uniqueFilename = $basename.'_'.time().'.'.$extension;

            // Store file
            $fullPath = $path.$uniqueFilename;
            Storage::put($fullPath, $data);

            return $fullPath;
        } catch (Exception $e) {
            Log::error('Failed to download attachment', [
                'connection_id' => $connection->id,
                'message_id' => $messageId,
                'attachment_id' => $attachmentId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Add a label to a message (e.g., to mark as processed).
     */
    public function addLabel(GmailConnection $connection, string $messageId, string $labelName): bool
    {
        try {
            $this->setConnection($connection);

            // Get or create label
            $labelId = $this->getOrCreateLabel($labelName);

            // Add label to message
            $mods = new \Google\Service\Gmail\ModifyMessageRequest;
            $mods->setAddLabelIds([$labelId]);

            $this->gmail->users_messages->modify('me', $messageId, $mods);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to add label to message', [
                'connection_id' => $connection->id,
                'message_id' => $messageId,
                'label' => $labelName,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get or create a Gmail label.
     */
    protected function getOrCreateLabel(string $labelName): string
    {
        try {
            // List existing labels
            $labels = $this->gmail->users_labels->listUsersLabels('me');

            foreach ($labels->getLabels() as $label) {
                if ($label->getName() === $labelName) {
                    return $label->getId();
                }
            }

            // Create new label
            $label = new \Google\Service\Gmail\Label;
            $label->setName($labelName);
            $label->setLabelListVisibility('labelShow');
            $label->setMessageListVisibility('show');

            $createdLabel = $this->gmail->users_labels->create('me', $label);

            return $createdLabel->getId();
        } catch (Exception $e) {
            Log::error('Failed to get or create label', [
                'label' => $labelName,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Disconnect a Gmail connection.
     */
    public function disconnect(GmailConnection $connection): bool
    {
        try {
            // Revoke access token
            $this->client->revokeToken($connection->access_token);

            // Delete the connection
            $connection->delete();

            return true;
        } catch (Exception $e) {
            Log::error('Failed to disconnect Gmail', [
                'connection_id' => $connection->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
