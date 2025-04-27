<?php

namespace App\Expenses\UI\API;

use App\Expenses\Domain\ExpenseCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoriesController
{
    public function index(): JsonResponse
    {
        $categories = DB::table('expense_categories')
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return [
                    'category_id' => $category->category_id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'color' => $category->color,
                ];
            });

        return response()->json(['data' => $categories]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-F]{6}$/i',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $categoryId = Str::uuid()->toString();
        $category = ExpenseCategory::create(
            $request->input('name'),
            $request->input('description'),
            $request->input('color'),
        );

        DB::table('expense_categories')->insert([
            'category_id' => $categoryId,
            'name' => $category->name,
            'description' => $category->description,
            'color' => $category->color,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'category_id' => $categoryId,
                'name' => $category->name,
                'description' => $category->description,
                'color' => $category->color,
            ]
        ], 201);
    }

    public function update(Request $request, string $categoryId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-F]{6}$/i',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category = DB::table('expense_categories')
            ->where('category_id', $categoryId)
            ->first();

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        $domainCategory = new ExpenseCategory($categoryId);
        $domainCategory->update(
            $request->input('name'),
            $request->input('description'),
            $request->input('color'),
        );

        DB::table('expense_categories')
            ->where('category_id', $categoryId)
            ->update([
                'name' => $domainCategory->name,
                'description' => $domainCategory->description,
                'color' => $domainCategory->color,
                'updated_at' => now(),
            ]);

        return response()->json([
            'data' => [
                'category_id' => $categoryId,
                'name' => $domainCategory->name,
                'description' => $domainCategory->description,
                'color' => $domainCategory->color,
            ]
        ]);
    }

    public function destroy(string $categoryId): JsonResponse
    {
        // Check if category is used by any expenses
        $usedByExpenses = DB::table('expenses')
            ->where('category_id', $categoryId)
            ->exists();

        if ($usedByExpenses) {
            return response()->json([
                'error' => 'Cannot delete category that is being used by expenses'
            ], 422);
        }

        DB::table('expense_categories')
            ->where('category_id', $categoryId)
            ->delete();

        return response()->json(null, 204);
    }
}
