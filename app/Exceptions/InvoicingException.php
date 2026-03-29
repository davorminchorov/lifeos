<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;

class InvoicingException extends \RuntimeException
{
    public function render(): JsonResponse
    {
        return new JsonResponse(['message' => $this->getMessage()], 422);
    }
}
