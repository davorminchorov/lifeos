<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed Chat ID
    |--------------------------------------------------------------------------
    |
    | Only messages from this Telegram chat ID will be processed.
    | This restricts the bot to your personal account only.
    |
    */

    'allowed_chat_id' => env('TELEGRAM_ALLOWED_CHAT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Default Tenant ID
    |--------------------------------------------------------------------------
    |
    | The tenant ID to use for all operations via the Telegram bot.
    |
    */

    'tenant_id' => env('TELEGRAM_TENANT_ID', 1),

    /*
    |--------------------------------------------------------------------------
    | Default User ID
    |--------------------------------------------------------------------------
    |
    | The user ID to use for all operations via the Telegram bot.
    |
    */

    'user_id' => env('TELEGRAM_USER_ID', 1),

];
