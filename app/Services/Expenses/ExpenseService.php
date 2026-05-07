<?php

declare(strict_types=1);

namespace App\Services\Expenses;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    /**
     * Create an expense. Validation must happen before this call (e.g. via FormRequest).
     *
     * @param  array<string, mixed>  $data       Validated expense fields.
     * @param  array<string, mixed>  $attribution Optional ['source' => 'agent'|'user'|'import',
     *                                            'agent_token_id' => int|null].
     */
    public function create(User $user, array $data, array $attribution = []): Expense
    {
        return Expense::create([
            'user_id' => $user->id,
            ...$data,
            'source' => $attribution['source'] ?? 'user',
            'created_by_agent_token_id' => $attribution['agent_token_id'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data Validated expense fields.
     */
    public function update(Expense $expense, array $data): Expense
    {
        $expense->update($data);

        return $expense->refresh();
    }

    public function delete(Expense $expense): bool
    {
        return (bool) $expense->delete();
    }

    public function markReimbursed(Expense $expense): Expense
    {
        return $this->update($expense, ['status' => 'reimbursed']);
    }

    public function duplicate(Expense $expense): Expense
    {
        $copy = $expense->replicate();
        $copy->expense_date = now()->toDateString();
        $copy->status = 'pending';
        $copy->save();

        return $copy;
    }

    public function categorize(
        Expense $expense,
        string $category,
        ?string $subcategory = null,
    ): Expense {
        $payload = ['category' => $category];

        if ($subcategory !== null) {
            $payload['subcategory'] = $subcategory;
        }

        return $this->update($expense, $payload);
    }

    /**
     * Bulk-delete expenses scoped to the given user.
     *
     * @param  array<int>  $ids
     */
    public function bulkDelete(User $user, array $ids): int
    {
        return Expense::where('user_id', $user->id)->whereIn('id', $ids)->delete();
    }

    /**
     * @param  array<int>  $ids
     */
    public function bulkMarkReimbursed(User $user, array $ids): int
    {
        return Expense::where('user_id', $user->id)
            ->whereIn('id', $ids)
            ->update(['status' => 'reimbursed']);
    }

    /**
     * @param  array<int>  $ids
     */
    public function bulkChangeCategory(User $user, array $ids, string $category): int
    {
        return Expense::where('user_id', $user->id)
            ->whereIn('id', $ids)
            ->update(['category' => $category]);
    }

    /**
     * @param  array<int>  $ids
     */
    public function bulkChangeStatus(User $user, array $ids, string $status): int
    {
        return Expense::where('user_id', $user->id)
            ->whereIn('id', $ids)
            ->update(['status' => $status]);
    }

    /**
     * Bulk-create expenses inside a single transaction. Returns the created models.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $attribution
     * @return array<int, Expense>
     */
    public function bulkCreate(User $user, array $rows, array $attribution = []): array
    {
        return DB::transaction(function () use ($user, $rows, $attribution): array {
            $created = [];

            foreach ($rows as $row) {
                $created[] = $this->create($user, $row, $attribution);
            }

            return $created;
        });
    }
}
