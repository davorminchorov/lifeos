<?php

use SergiX44\Nutgram\Nutgram;

/*
|--------------------------------------------------------------------------
| Nutgram Telegram Bot Handlers
|--------------------------------------------------------------------------
|
| These handlers are automatically loaded by Nutgram's Laravel service
| provider. They define how the bot responds to commands and messages.
|
| For LifeOS, the primary message handling is done via the webhook
| controller (TelegramBotController) which routes messages through
| the AI agent. These Nutgram handlers are available for direct
| command handling that bypasses the AI agent if needed.
|
*/

/** @var Nutgram $bot */
$bot->onCommand('start', function (Nutgram $bot) {
    $bot->sendMessage(
        "Welcome to LifeOS Bot!\n\n".
        "I can help you manage your finances and life. Just type naturally:\n\n".
        "- \"spent 2800 vero groceries\"\n".
        "- \"what did i spend this week?\"\n".
        "- \"netflix 899 monthly subscription\"\n".
        "- \"paid evn electricity 1350\"\n\n".
        "Commands:\n".
        "/briefing — Daily summary\n".
        "/spending — Monthly spending report\n".
        "/bills — Upcoming bills\n".
        "/subs — Active subscriptions\n".
        "/jobs — Job applications\n".
        "/menu — Today's meal plan\n".
        '/sync — Run data sync'
    );
});
