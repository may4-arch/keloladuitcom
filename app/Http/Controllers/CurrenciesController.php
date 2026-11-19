<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCurrenciesRequest;
use App\Http\Requests\UpdateCurrenciesRequest;
use App\Models\Currencies;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CurrenciesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $currencies = Currencies::orderByDesc('is_base')
            ->orderByDesc('is_active')
            ->orderBy('code')
            ->get();

        return Inertia::render('Currencies/Index', [
            'currencies' => $currencies,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Currencies/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCurrenciesRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $isBase = $request->boolean('is_base');
        $isActive = $request->has('is_active') ? $request->boolean('is_active') : true;

        $validated['is_base'] = $isBase;
        $validated['is_active'] = $isActive;

        DB::transaction(function () use ($validated, $isBase) {
            if ($isBase) {
                $validated['exchange_rate'] = 1;
                Currencies::where('is_base', true)->update(['is_base' => false]);
            }

            Currencies::create($validated);
        });

        return redirect()->route('currencies.index')
            ->with('status', 'Mata uang berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): RedirectResponse
    {
        return redirect()->route('currencies.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): Response
    {
        $currency = Currencies::findOrFail($id);

        return Inertia::render('Currencies/Edit', [
            'currency' => $currency,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCurrenciesRequest $request, $id): RedirectResponse
    {
        $currency = Currencies::findOrFail($id);

        $validated = $request->validated();
        $isBase = $request->boolean('is_base');
        $isActive = $request->has('is_active') ? $request->boolean('is_active') : $currency->is_active;

        $validated['is_base'] = $isBase;
        $validated['is_active'] = $isActive;

        DB::transaction(function () use ($currency, $validated, $isBase) {
            if ($isBase) {
                $validated['exchange_rate'] = 1;
                Currencies::where('id', '!=', $currency->id)
                    ->where('is_base', true)
                    ->update(['is_base' => false]);
            }

            $currency->update($validated);
        });

        return redirect()->route('currencies.index')
            ->with('status', 'Mata uang berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): RedirectResponse
    {
        $currency = Currencies::findOrFail($id);

        if ($currency->is_base) {
            return redirect()->back()
                ->with('error', 'Mata uang utama (Base Currency) tidak dapat dihapus.');
        }

        if (DB::table('accounts')->where('currency_id', $currency->id)->exists()) {
            return redirect()->back()
                ->with('error', 'Mata uang tidak dapat dihapus karena sedang digunakan pada akun.');
        }

        if (DB::table('categories')->where('currency_id', $currency->id)->exists()) {
            return redirect()->back()
                ->with('error', 'Mata uang tidak dapat dihapus karena sedang digunakan pada kategori.');
        }

        $currency->delete();

        return redirect()->route('currencies.index')
            ->with('status', 'Mata uang berhasil dihapus.');
    }
}
