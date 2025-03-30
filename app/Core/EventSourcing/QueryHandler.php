<?php

namespace App\Core\EventSourcing;

interface QueryHandler
{
    /**
     * Handle a query and return result
     *
     * @return mixed The query result
     */
    public function handle(Query $query): mixed;
}
