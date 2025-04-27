<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Main expenses channel
Broadcast::channel('expenses', function ($user) {
    return (bool) $user;
});

// Individual expense channel
Broadcast::channel('expense.{expenseId}', function ($user, $expenseId) {
    // Authorization logic could be enhanced here to check if user owns this expense
    return (bool) $user;
});
