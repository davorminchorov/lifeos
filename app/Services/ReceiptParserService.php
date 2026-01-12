<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;

class ReceiptParserService
{
    /**
     * Parse a Gmail message and extract expense data.
     */
    public function parse(array $emailData): array
    {
        $merchant = $this->extractMerchant($emailData);
        $merchantConfig = $this->getMerchantConfig($emailData, $merchant);

        $amount = $this->extractAmount($emailData['body']);
        $currency = $this->extractCurrency($emailData['body']);
        $date = $this->extractDate($emailData);
        $category = $merchantConfig['category'] ?? $this->detectCategory($emailData);
        $subcategory = $merchantConfig['subcategory'] ?? null;
        $paymentMethod = $this->detectPaymentMethod($emailData['body']);

        return [
            'merchant' => $merchant,
            'amount' => $amount,
            'currency' => $currency ?? config('gmail_receipts.default_currency'),
            'expense_date' => $date,
            'category' => $category,
            'subcategory' => $subcategory,
            'payment_method' => $paymentMethod,
            'description' => $this->generateDescription($emailData, $merchant),
            'confidence' => $this->calculateConfidence($amount, $merchant, $category),
        ];
    }

    /**
     * Extract merchant name from email data.
     */
    protected function extractMerchant(array $emailData): ?string
    {
        $from = $emailData['from'] ?? '';
        $subject = $emailData['subject'] ?? '';

        // Try to extract from email address
        if (preg_match('/<([^@]+)@([^>]+)>/', $from, $matches)) {
            $domain = $matches[2];

            // Check against known merchant patterns
            $patterns = config('gmail_receipts.merchant_patterns', []);
            foreach ($patterns as $key => $pattern) {
                foreach ($pattern['domains'] as $merchantDomain) {
                    if (str_contains($domain, $merchantDomain)) {
                        return $pattern['merchant'];
                    }
                }
            }

            // Extract company name from domain
            $domain = str_replace(['www.', '.com', '.co.uk', '.ca'], '', $domain);

            return ucfirst($domain);
        }

        // Try to extract from subject
        if (preg_match('/from\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)/i', $subject, $matches)) {
            return $matches[1];
        }

        // Try to extract company name patterns
        if (preg_match('/^([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)\s+-/', $subject, $matches)) {
            return $matches[1];
        }

        return 'Unknown Merchant';
    }

    /**
     * Get merchant configuration if available.
     */
    protected function getMerchantConfig(array $emailData, ?string $merchant): array
    {
        $from = $emailData['from'] ?? '';
        $patterns = config('gmail_receipts.merchant_patterns', []);

        // Extract domain from email
        if (preg_match('/@([^>]+)>?/', $from, $matches)) {
            $domain = trim($matches[1], '> ');

            foreach ($patterns as $key => $pattern) {
                foreach ($pattern['domains'] as $merchantDomain) {
                    if (str_contains($domain, $merchantDomain)) {
                        return $pattern;
                    }
                }
            }
        }

        return [];
    }

    /**
     * Extract amount from email body.
     */
    protected function extractAmount(string $body): ?float
    {
        $patterns = config('gmail_receipts.amount_patterns', []);

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $body, $matches)) {
                // Remove commas and convert to float
                $amount = str_replace(',', '', $matches[1]);

                return (float) $amount;
            }
        }

        return null;
    }

    /**
     * Extract currency from email body.
     */
    protected function extractCurrency(string $body): ?string
    {
        // Try currency code patterns first
        $currencyPatterns = config('gmail_receipts.currency_patterns', []);
        foreach ($currencyPatterns as $pattern) {
            if (preg_match($pattern, $body, $matches)) {
                // Currency code is in the capture group
                foreach ($matches as $match) {
                    if (preg_match('/^[A-Z]{3}$/', $match)) {
                        return strtoupper($match);
                    }
                }
            }
        }

        // Try currency symbols
        $symbols = config('gmail_receipts.currency_symbols', []);
        foreach ($symbols as $symbol => $code) {
            if (str_contains($body, $symbol)) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Extract or determine date from email.
     */
    protected function extractDate(array $emailData): Carbon
    {
        $dateString = $emailData['date'] ?? null;

        if ($dateString) {
            try {
                return Carbon::parse($dateString);
            } catch (\Exception $e) {
                // Fall back to current date if parsing fails
            }
        }

        return Carbon::now();
    }

    /**
     * Detect expense category from email content.
     */
    protected function detectCategory(array $emailData): string
    {
        $body = strtolower($emailData['body'] ?? '');
        $subject = strtolower($emailData['subject'] ?? '');
        $content = $body.' '.$subject;

        $categoryKeywords = config('gmail_receipts.category_keywords', []);
        $scores = [];

        foreach ($categoryKeywords as $category => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                $score += substr_count($content, strtolower($keyword));
            }
            $scores[$category] = $score;
        }

        // Return category with highest score, or default to shopping
        if (max($scores) > 0) {
            return array_search(max($scores), $scores);
        }

        return 'shopping';
    }

    /**
     * Detect payment method from email content.
     */
    protected function detectPaymentMethod(string $body): ?string
    {
        $body = strtolower($body);
        $keywords = config('gmail_receipts.payment_method_keywords', []);

        foreach ($keywords as $keyword => $method) {
            if (str_contains($body, $keyword)) {
                return $method;
            }
        }

        // Check for card ending patterns
        if (preg_match('/ending\s+in\s+\d{4}/', $body)) {
            return 'credit card';
        }

        return null;
    }

    /**
     * Generate expense description from email data.
     */
    protected function generateDescription(array $emailData, ?string $merchant): string
    {
        $subject = $emailData['subject'] ?? '';
        $snippet = $emailData['snippet'] ?? '';

        // If subject contains useful info, use it
        if ($subject && ! str_contains(strtolower($subject), 'receipt')) {
            return Str::limit($subject, 200);
        }

        // Use snippet as fallback
        if ($snippet) {
            return Str::limit($snippet, 200);
        }

        // Generate generic description
        return "Receipt from {$merchant}";
    }

    /**
     * Calculate confidence score for parsed data.
     */
    protected function calculateConfidence(?float $amount, ?string $merchant, ?string $category): float
    {
        $confidence = 0;

        // Amount found: +40%
        if ($amount !== null && $amount > 0) {
            $confidence += 0.4;
        }

        // Merchant identified: +30%
        if ($merchant && $merchant !== 'Unknown Merchant') {
            $confidence += 0.3;
        }

        // Category detected: +30%
        if ($category && $category !== 'shopping') {
            $confidence += 0.3;
        } elseif ($category === 'shopping') {
            $confidence += 0.15;
        }

        return round($confidence, 2);
    }

    /**
     * Check if parsed data is valid enough to create an expense.
     */
    public function isValidExpense(array $parsedData): bool
    {
        // Must have an amount greater than zero
        if (! isset($parsedData['amount']) || $parsedData['amount'] <= 0) {
            return false;
        }

        // Must have a confidence score of at least 40%
        if (! isset($parsedData['confidence']) || $parsedData['confidence'] < 0.4) {
            return false;
        }

        return true;
    }

    /**
     * Extract order/transaction ID if present.
     */
    public function extractOrderId(array $emailData): ?string
    {
        $body = $emailData['body'] ?? '';
        $subject = $emailData['subject'] ?? '';
        $content = $body.' '.$subject;

        // Common order ID patterns
        $patterns = [
            '/Order\s*#?\s*:?\s*([A-Z0-9-]+)/i',
            '/Order\s+Number\s*:?\s*([A-Z0-9-]+)/i',
            '/Transaction\s*#?\s*:?\s*([A-Z0-9-]+)/i',
            '/Confirmation\s*#?\s*:?\s*([A-Z0-9-]+)/i',
            '/Reference\s*#?\s*:?\s*([A-Z0-9-]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Extract item details if present in email.
     */
    public function extractItems(array $emailData): array
    {
        $body = $emailData['body'] ?? '';
        $items = [];

        // This is a simplified version - you could expand this with more sophisticated parsing
        // Look for common item list patterns
        $lines = explode("\n", $body);

        foreach ($lines as $line) {
            // Pattern: Item name followed by price
            if (preg_match('/(.+?)\s+\$?([0-9,]+\.[0-9]{2})/', $line, $matches)) {
                $itemName = trim($matches[1]);
                $itemPrice = (float) str_replace(',', '', $matches[2]);

                // Filter out likely headers/footers
                if (strlen($itemName) > 3 && $itemPrice > 0 && $itemPrice < 10000) {
                    $items[] = [
                        'name' => $itemName,
                        'price' => $itemPrice,
                    ];
                }
            }
        }

        return $items;
    }

    /**
     * Extract location/address if present.
     */
    public function extractLocation(array $emailData): ?string
    {
        $body = $emailData['body'] ?? '';

        // Look for address patterns
        $patterns = [
            '/Location\s*:?\s*([^\n]+)/i',
            '/Address\s*:?\s*([^\n]+)/i',
            '/Store\s*:?\s*([^\n]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $body, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }
}
