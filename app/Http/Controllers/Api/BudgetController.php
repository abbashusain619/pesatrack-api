<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function index()
    {
        $budgets = Budget::where('user_id', Auth::id())
            ->with('category')
            ->get()
            ->map(function ($budget) {
                $budget->spent = $budget->spentAmount();
                $budget->percentage = $budget->amount > 0 ? ($budget->spent / $budget->amount) * 100 : 0;
                return $budget;
            });
        return response()->json($budgets);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|in:monthly,weekly,yearly',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'alert_threshold' => 'nullable|numeric|min:0|max:100',
        ]);

        // Optional: verify category belongs to user (or system category)
        $category = Category::find($validated['category_id']);
        if ($category && $category->user_id && $category->user_id !== Auth::id()) {
            return response()->json(['message' => 'Invalid category'], 422);
        }

        $validated['user_id'] = Auth::id();
        $budget = Budget::create($validated);

        // load category for response
        $budget->load('category');
        $budget->spent = $budget->spentAmount();
        $budget->percentage = $budget->amount > 0 ? ($budget->spent / $budget->amount) * 100 : 0;

        return response()->json($budget, 201);
    }

    public function show(Budget $budget)
    {
        if ($budget->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $budget->spent = $budget->spentAmount();
        $budget->percentage = $budget->amount > 0 ? ($budget->spent / $budget->amount) * 100 : 0;
        $budget->load('category');
        return response()->json($budget);
    }

    public function update(Request $request, Budget $budget)
    {
        if ($budget->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'amount' => 'sometimes|numeric|min:0',
            'period' => 'sometimes|in:monthly,weekly,yearly',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'alert_threshold' => 'nullable|numeric|min:0|max:100',
        ]);

        // Optional category ownership check
        if (isset($validated['category_id'])) {
            $category = Category::find($validated['category_id']);
            if ($category && $category->user_id && $category->user_id !== Auth::id()) {
                return response()->json(['message' => 'Invalid category'], 422);
            }
        }

        $budget->update($validated);
        $budget->load('category');
        $budget->spent = $budget->spentAmount();
        $budget->percentage = $budget->amount > 0 ? ($budget->spent / $budget->amount) * 100 : 0;

        return response()->json($budget);
    }

    public function destroy(Budget $budget)
    {
        if ($budget->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $budget->delete();
        return response()->json(['message' => 'Budget deleted']);
    }
}