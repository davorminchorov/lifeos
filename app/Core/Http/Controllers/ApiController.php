<?php

namespace App\Core\Http\Controllers;

use App\Core\Commands\CommandBus;
use App\Core\Queries\QueryBus;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

abstract class ApiController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    protected ?CommandBus $commandBus = null;
    protected ?QueryBus $queryBus = null;

    public function __construct(CommandBus $commandBus = null, QueryBus $queryBus = null)
    {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }
}
