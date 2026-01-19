<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Invoice Number Prefix
    |--------------------------------------------------------------------------
    |
    | This value is the prefix for invoice numbers. For example, 'INV' will
    | generate invoice numbers like INV-2026-000001.
    |
    */

    'prefix' => env('INVOICE_PREFIX', 'INV'),

    /*
    |--------------------------------------------------------------------------
    | Credit Note Prefix
    |--------------------------------------------------------------------------
    |
    | This value is the prefix for credit note numbers. For example, 'CN' will
    | generate credit note numbers like CN-2026-000001.
    |
    */

    'credit_note_prefix' => env('CREDIT_NOTE_PREFIX', 'CN'),

    /*
    |--------------------------------------------------------------------------
    | Default Net Terms (Days)
    |--------------------------------------------------------------------------
    |
    | This value determines the default payment terms for invoices in days.
    | For example, 14 means "Net 14" - payment due within 14 days.
    |
    */

    'net_terms_days' => env('INVOICE_NET_TERMS', 14),

    /*
    |--------------------------------------------------------------------------
    | Dunning Configuration
    |--------------------------------------------------------------------------
    |
    | Configure automated payment reminder settings. The reminder_days array
    | specifies how many days after invoice issue to send reminders.
    | For example: [0, 7, 14, 21] sends reminders at issue, +7, +14, and +21 days.
    |
    */

    'dunning' => [
        'enabled' => env('DUNNING_ENABLED', true),
        'reminder_days' => [0, 7, 14, 21],
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF Configuration
    |--------------------------------------------------------------------------
    |
    | Configure PDF generation engine and storage path. Supported engines are
    | 'dompdf' and 'snappy'. The storage_path is relative to the storage/app directory.
    |
    */

    'pdf' => [
        'engine' => env('PDF_ENGINE', 'dompdf'),
        'storage_path' => 'invoices/pdfs',
    ],

];
