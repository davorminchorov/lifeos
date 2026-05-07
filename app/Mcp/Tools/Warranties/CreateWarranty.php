<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Warranties;

use App\Mcp\Tools\AbstractTool;
use App\Models\PendingAction;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class CreateWarranty extends AbstractTool
{
    protected string $name = 'warranties.create';

    protected string $description = 'Register a warranty for a purchased product. Queued as a pending action awaiting human approval.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'product_name' => $schema->string()->description('Product name. Required.'),
            'brand' => $schema->string()->description('Brand or manufacturer.'),
            'model' => $schema->string()->description('Model name or number.'),
            'serial_number' => $schema->string()->description('Serial number (helpful for idempotency).'),
            'purchase_date' => $schema->string()->description('YYYY-MM-DD. Required.'),
            'purchase_price' => $schema->number()->description('Purchase price (numeric).'),
            'retailer' => $schema->string()->description('Retailer name.'),
            'warranty_duration_months' => $schema->integer()->description('Length of coverage in months.'),
            'warranty_expiration_date' => $schema->string()->description('YYYY-MM-DD. Required.'),
            'warranty_type' => $schema->string()->description('"manufacturer", "extended", etc.'),
            'warranty_terms' => $schema->string()->description('Coverage details.'),
            'source_email_id' => $schema->string()->description('Optional Gmail message id for idempotency.'),
            'source_file_id' => $schema->string()->description('Optional Drive file id when extracted from a receipt scan / PDF.'),
        ];
    }

    public function handle(Request $request, PendingActionApplier $applier): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $payload = array_filter([
            'product_name' => $request->get('product_name'),
            'brand' => $request->get('brand'),
            'model' => $request->get('model'),
            'serial_number' => $request->get('serial_number'),
            'purchase_date' => $request->get('purchase_date'),
            'purchase_price' => $request->get('purchase_price'),
            'retailer' => $request->get('retailer'),
            'warranty_duration_months' => $request->get('warranty_duration_months'),
            'warranty_expiration_date' => $request->get('warranty_expiration_date'),
            'warranty_type' => $request->get('warranty_type'),
            'warranty_terms' => $request->get('warranty_terms'),
            'current_status' => 'active',
            'source_email_id' => $request->get('source_email_id'),
            'source_file_id' => $request->get('source_file_id'),
        ], static fn ($v) => $v !== null);

        try {
            $action = $applier->record(
                token: $this->agentToken(),
                tool: $this->name(),
                action: PendingAction::ACTION_CREATE,
                payload: $payload,
            );
        } catch (\Throwable $e) {
            return Response::error($e->getMessage());
        }

        return Response::structured([
            'pending_action_id' => $action->id,
            'status' => $action->status,
            'idempotency_key' => $action->idempotency_key,
            'auto_applied' => $action->status === PendingAction::STATUS_APPLIED,
        ]);
    }
}
