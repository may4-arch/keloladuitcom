<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Currencies;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $categories = Category::where('user_id', $request->user()->id)
            ->with('currency')
            ->orderBy('type', 'asc')
            ->orderBy('priority', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return Inertia::render('Category/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $currencies = Currencies::where('is_active', true)
            ->orderByDesc('is_base')
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'symbol', 'is_base']);

        return Inertia::render('Category/Create', [
            'currencies' => $currencies,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        $validated['priority'] = $validated['priority'] ?? 0;

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('status', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): RedirectResponse
    {
        return redirect()->route('categories.edit', $category);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Category $category): Response
    {
        if ($category->user_id !== $request->user()->id) {
            abort(403);
        }

        $currencies = Currencies::where('is_active', true)
            ->orWhere('id', $category->currency_id)
            ->orderByDesc('is_base')
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'symbol', 'is_base']);

        return Inertia::render('Category/Edit', [
            'category' => $category,
            'currencies' => $currencies,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        if ($category->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validated();
        $validated['priority'] = $validated['priority'] ?? 0;

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('status', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Category $category): RedirectResponse
    {
        if ($category->user_id !== $request->user()->id) {
            abort(403);
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('status', 'Kategori berhasil dihapus.');
    }
}
