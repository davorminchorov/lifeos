<?php

declare(strict_types=1);

use App\Mcp\LifeOsServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/lifeos', LifeOsServer::class)
    ->middleware('auth.agent')
    ->name('mcp.lifeos');
