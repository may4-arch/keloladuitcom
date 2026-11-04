<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Http\Requests\StoreBudgetRequest;
use App\Http\Requests\UpdateBudgetRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $budgets = Budget::where('user_id', $request->user()->id)
            ->with('category')
            ->orderBy('priority', 'desc')
            ->orderBy('budget_id', 'desc')
            ->get();

        return Inertia::render('Budget/Index', [
            'budgets' => $budgets,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        $categories = Category::where('user_id', $request->user()->id)
            ->orderBy('type')
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->get(['category_id', 'name', 'type', 'priority']);

        return Inertia::render('Budget/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBudgetRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        $validated['minimal'] = $validated['minimal'] ?? 0;
        $validated['priority'] = $request->boolean('priority') ? 1 : 0;

        Budget::create($validated);

        return redirect()->route('budgets.index')
            ->with('status', 'Anggaran berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): RedirectResponse
    {
        return redirect()->route('budgets.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id): Response
    {
        $budget = Budget::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $categories = Category::where('user_id', $request->user()->id)
            ->orderBy('type')
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->get(['category_id', 'name', 'type', 'priority']);

        return Inertia::render('Budget/Edit', [
            'budget' => $budget,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBudgetRequest $request, $id): RedirectResponse
    {
        $budget = Budget::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validated();
        $validated['minimal'] = $validated['minimal'] ?? 0;
        $validated['priority'] = $request->boolean('priority') ? 1 : 0;

        $budget->update($validated);

        return redirect()->route('budgets.index')
            ->with('status', 'Anggaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id): RedirectResponse
    {
        $budget = Budget::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $budget->delete();

        return redirect()->route('budgets.index')
            ->with('status', 'Anggaran berhasil dihapus.');
    }
}
