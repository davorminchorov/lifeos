<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Gmail API Credentials
    |--------------------------------------------------------------------------
    |
    | These credentials are used to authenticate with the Gmail API.
    | You can obtain these from the Google Cloud Console.
    |
    */

    'client_id' => env('GMAIL_CLIENT_ID'),
    'client_secret' => env('GMAIL_CLIENT_SECRET'),
    'redirect_uri' => env('GMAIL_REDIRECT_URI'),

    /*
    |--------------------------------------------------------------------------
    | Gmail API Scopes
    |--------------------------------------------------------------------------
    |
    | The OAuth scopes required for reading emails and managing labels.
    |
    */

    'scopes' => [
        'https://www.googleapis.com/auth/gmail.readonly',
        'https://www.googleapis.com/auth/gmail.labels',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Expense Status
    |--------------------------------------------------------------------------
    |
    | The default status for expenses created from Gmail receipts.
    | Options: 'pending', 'confirmed', 'reimbursed'
    |
    */

    'default_expense_status' => 'pending',

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | The default currency to use when currency cannot be detected.
    |
    */

    'default_currency' => 'USD',

    /*
    |--------------------------------------------------------------------------
    | Receipt Search Queries
    |--------------------------------------------------------------------------
    |
    | Gmail search queries used to identify receipt emails.
    |
    */

    'search_queries' => [
        'subject:(receipt OR invoice OR "order confirmation" OR "purchase confirmation" OR "payment confirmation")',
        'from:(noreply@amazon.com OR receipts@uber.com OR no-reply@accounts.google.com)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Merchant Patterns
    |--------------------------------------------------------------------------
    |
    | Merchant detection patterns with default category and subcategory.
    | Each pattern should include: merchant name, email domain pattern,
    | default category, and optional subcategory.
    |
    */

    'merchant_patterns' => [
        // E-commerce
        'amazon' => [
            'domains' => ['amazon.com', 'amazon.co.uk', 'amazon.ca'],
            'merchant' => 'Amazon',
            'category' => 'shopping',
            'subcategory' => 'online',
        ],
        'ebay' => [
            'domains' => ['ebay.com', 'ebay.co.uk'],
            'merchant' => 'eBay',
            'category' => 'shopping',
            'subcategory' => 'online',
        ],

        // Transportation
        'uber' => [
            'domains' => ['uber.com'],
            'merchant' => 'Uber',
            'category' => 'transport',
            'subcategory' => 'rideshare',
        ],
        'lyft' => [
            'domains' => ['lyft.com'],
            'merchant' => 'Lyft',
            'category' => 'transport',
            'subcategory' => 'rideshare',
        ],

        // Food Delivery
        'doordash' => [
            'domains' => ['doordash.com'],
            'merchant' => 'DoorDash',
            'category' => 'food',
            'subcategory' => 'delivery',
        ],
        'ubereats' => [
            'domains' => ['ubereats.com'],
            'merchant' => 'Uber Eats',
            'category' => 'food',
            'subcategory' => 'delivery',
        ],
        'grubhub' => [
            'domains' => ['grubhub.com'],
            'merchant' => 'Grubhub',
            'category' => 'food',
            'subcategory' => 'delivery',
        ],

        // Subscriptions & Entertainment
        'netflix' => [
            'domains' => ['netflix.com'],
            'merchant' => 'Netflix',
            'category' => 'entertainment',
            'subcategory' => 'streaming',
        ],
        'spotify' => [
            'domains' => ['spotify.com'],
            'merchant' => 'Spotify',
            'category' => 'entertainment',
            'subcategory' => 'music',
        ],
        'apple' => [
            'domains' => ['apple.com', 'itunes.com'],
            'merchant' => 'Apple',
            'category' => 'entertainment',
            'subcategory' => 'apps',
        ],
        'google' => [
            'domains' => ['google.com', 'googleapis.com'],
            'merchant' => 'Google',
            'category' => 'entertainment',
            'subcategory' => 'apps',
        ],

        // Travel
        'airbnb' => [
            'domains' => ['airbnb.com'],
            'merchant' => 'Airbnb',
            'category' => 'travel',
            'subcategory' => 'accommodation',
        ],
        'booking' => [
            'domains' => ['booking.com'],
            'merchant' => 'Booking.com',
            'category' => 'travel',
            'subcategory' => 'accommodation',
        ],

        // Utilities
        'paypal' => [
            'domains' => ['paypal.com'],
            'merchant' => 'PayPal',
            'category' => 'shopping',
            'subcategory' => null,
        ],
        'stripe' => [
            'domains' => ['stripe.com'],
            'merchant' => 'Stripe',
            'category' => 'shopping',
            'subcategory' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Amount Parsing Patterns
    |--------------------------------------------------------------------------
    |
    | Regular expressions for extracting amounts from email content.
    | These patterns are tried in order until a match is found.
    |
    */

    'amount_patterns' => [
        '/Total[:\s]+\$?([0-9,]+(?:\.[0-9]{2})?)/i',
        '/Amount[:\s]+\$?([0-9,]+(?:\.[0-9]{2})?)/i',
        '/Grand Total[:\s]+\$?([0-9,]+(?:\.[0-9]{2})?)/i',
        '/Order Total[:\s]+\$?([0-9,]+(?:\.[0-9]{2})?)/i',
        '/Payment[:\s]+\$?([0-9,]+(?:\.[0-9]{2})?)/i',
        '/Charged[:\s]+\$?([0-9,]+(?:\.[0-9]{2})?)/i',
        '/Price[:\s]+\$?([0-9,]+(?:\.[0-9]{2})?)/i',
        '/\$([0-9,]+(?:\.[0-9]{2})?)\s+(?:USD|CAD|EUR|GBP)/i',
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Symbols and Codes
    |--------------------------------------------------------------------------
    |
    | Mapping of currency symbols to ISO currency codes.
    |
    */

    'currency_symbols' => [
        '$' => 'USD',
        '€' => 'EUR',
        '£' => 'GBP',
        '¥' => 'JPY',
        'C$' => 'CAD',
        'A$' => 'AUD',
        'MKD' => 'MKD',
        'ден' => 'MKD',
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Code Patterns
    |--------------------------------------------------------------------------
    |
    | Patterns to detect currency codes in email content.
    |
    */

    'currency_patterns' => [
        '/([0-9,]+\.[0-9]{2})\s+(USD|CAD|EUR|GBP|JPY|AUD|MKD)/i',
        '/(USD|CAD|EUR|GBP|JPY|AUD|MKD)\s+([0-9,]+\.[0-9]{2})/i',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Method Keywords
    |--------------------------------------------------------------------------
    |
    | Keywords to detect payment methods from email content.
    |
    */

    'payment_method_keywords' => [
        'visa' => 'credit card',
        'mastercard' => 'credit card',
        'amex' => 'credit card',
        'american express' => 'credit card',
        'discover' => 'credit card',
        'paypal' => 'paypal',
        'apple pay' => 'apple pay',
        'google pay' => 'google pay',
        'debit' => 'debit',
        'cash' => 'cash',
    ],

    /*
    |--------------------------------------------------------------------------
    | Category Keywords
    |--------------------------------------------------------------------------
    |
    | Keywords to help determine expense categories from email content.
    |
    */

    'category_keywords' => [
        'food' => ['restaurant', 'cafe', 'coffee', 'pizza', 'burger', 'sushi', 'food', 'dining'],
        'transport' => ['taxi', 'uber', 'lyft', 'bus', 'train', 'flight', 'parking', 'gas', 'fuel'],
        'entertainment' => ['movie', 'cinema', 'concert', 'theater', 'game', 'streaming', 'netflix', 'spotify'],
        'shopping' => ['amazon', 'ebay', 'store', 'shop', 'retail', 'clothing', 'electronics'],
        'utilities' => ['electric', 'water', 'gas', 'internet', 'phone', 'utility'],
        'healthcare' => ['doctor', 'hospital', 'pharmacy', 'medical', 'dental', 'health'],
        'travel' => ['hotel', 'airbnb', 'booking', 'vacation', 'trip', 'airfare'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Receipt Attachment Extensions
    |--------------------------------------------------------------------------
    |
    | File extensions to download as receipt attachments.
    |
    */

    'attachment_extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'gif'],

    /*
    |--------------------------------------------------------------------------
    | Storage Path
    |--------------------------------------------------------------------------
    |
    | Path where receipt attachments should be stored.
    | Relative to storage/app directory.
    |
    */

    'storage_path' => 'receipts',

    /*
    |--------------------------------------------------------------------------
    | Sync Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic syncing of Gmail receipts.
    |
    */

    'sync' => [
        // How far back to look for receipts on first sync (in days)
        'initial_sync_days' => 30,

        // Maximum number of emails to process per sync
        'max_emails_per_sync' => 100,

        // Enable automatic syncing via scheduler
        'auto_sync_enabled' => true,

        // How often to sync (in minutes)
        'sync_frequency' => 60,
    ],
];
