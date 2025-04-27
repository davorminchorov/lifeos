<?php

namespace App\Expenses\Domain;

use App\Core\Domain\AggregateRoot;
use Illuminate\Support\Str;

class ExpenseCategory extends AggregateRoot
{
    public string $categoryId;
    public string $name;
    public ?string $description;
    public ?string $color;

    public static function create(string $name, ?string $description = null, ?string $color = null): static
    {
        $category = new static(Str::uuid()->toString());
        $category->name = $name;
        $category->description = $description;
        $category->color = $color;

        return $category;
    }

    public function update(string $name, ?string $description = null, ?string $color = null): void
    {
        $this->name = $name;
        $this->description = $description;
        $this->color = $color;
    }
}
