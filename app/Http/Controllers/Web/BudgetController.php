<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function index()
    {
        $budgets = Budget::where('user_id', Auth::id())->with('category')->get();
        return view('budgets.index', compact('budgets'));
    }

    public function create()
    {
        $categories = Category::forUser(Auth::id())->orderBy('name')->get();
        return view('budgets.create', compact('categories'));
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

        $validated['user_id'] = Auth::id();
        Budget::create($validated);

        return redirect()->route('budgets.index')->with('success', 'Budget created.');
    }

    public function edit(Budget $budget)
    {
        if ($budget->user_id !== Auth::id()) abort(403);
        $categories = Category::forUser(Auth::id())->orderBy('name')->get();
        return view('budgets.edit', compact('budget', 'categories'));
    }

    public function update(Request $request, Budget $budget)
    {
        if ($budget->user_id !== Auth::id()) abort(403);
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|in:monthly,weekly,yearly',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'alert_threshold' => 'nullable|numeric|min:0|max:100',
        ]);
        $budget->update($validated);
        return redirect()->route('budgets.index')->with('success', 'Budget updated.');
    }

    public function destroy(Budget $budget)
    {
        if ($budget->user_id !== Auth::id()) abort(403);
        $budget->delete();
        return redirect()->route('budgets.index')->with('success', 'Budget deleted.');
    }
}