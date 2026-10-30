<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\transactions;
use App\Http\Requests\StoretransactionsRequest;
use App\Http\Requests\UpdatetransactionsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TransactionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $transactions = transactions::where('user_id', $request->user()->id)
            ->with(['category', 'details'])
            ->orderBy('date', 'desc')
            ->orderBy('transaction_id', 'desc')
            ->get();

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        $categories = Category::where('user_id', $request->user()->id)
            ->orderBy('type')
            ->orderBy('priority')
            ->orderBy('name')
            ->get(['category_id', 'name', 'type']);

        return Inertia::render('Transactions/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoretransactionsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $details = $request->input('details', []);

        DB::transaction(function () use ($request, $validated, $details) {
            $total = 0;
            foreach ($details as $item) {
                if (!empty($item['item_name'])) {
                    $qty = (int) ($item['quantity'] ?? 1);
                    $price = (float) ($item['price'] ?? 0);
                    $total += ($qty * $price);
                }
            }

            $transaction = transactions::create([
                'user_id' => $request->user()->id,
                'category_id' => $validated['category_id'],
                'date' => $validated['date'],
                'notes' => $validated['notes'] ?? null,
                'total' => $total,
            ]);

            foreach ($details as $item) {
                if (!empty($item['item_name'])) {
                    $qty = (int) ($item['quantity'] ?? 1);
                    $price = (float) ($item['price'] ?? 0);
                    $transaction->details()->create([
                        'item_name' => $item['item_name'],
                        'quantity' => $qty,
                        'price' => $price,
                        'subtotal' => $qty * $price,
                    ]);
                }
            }
        });

        return redirect()->route('transactions.index')
            ->with('status', 'Transaksi berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): RedirectResponse
    {
        return redirect()->route('transactions.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id): Response
    {
        $transaction = transactions::where('user_id', $request->user()->id)
            ->with('details')
            ->findOrFail($id);

        $categories = Category::where('user_id', $request->user()->id)
            ->orderBy('type')
            ->orderBy('priority')
            ->orderBy('name')
            ->get(['category_id', 'name', 'type']);

        return Inertia::render('Transactions/Edit', [
            'transaction' => $transaction,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatetransactionsRequest $request, $id): RedirectResponse
    {
        $transaction = transactions::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validated();
        $details = $request->input('details', []);

        DB::transaction(function () use ($transaction, $validated, $details) {
            $total = 0;
            foreach ($details as $item) {
                if (!empty($item['item_name'])) {
                    $qty = (int) ($item['quantity'] ?? 1);
                    $price = (float) ($item['price'] ?? 0);
                    $total += ($qty * $price);
                }
            }

            $transaction->update([
                'category_id' => $validated['category_id'],
                'date' => $validated['date'],
                'notes' => $validated['notes'] ?? null,
                'total' => $total,
            ]);

            // Recreate details
            $transaction->details()->delete();

            foreach ($details as $item) {
                if (!empty($item['item_name'])) {
                    $qty = (int) ($item['quantity'] ?? 1);
                    $price = (float) ($item['price'] ?? 0);
                    $transaction->details()->create([
                        'item_name' => $item['item_name'],
                        'quantity' => $qty,
                        'price' => $price,
                        'subtotal' => $qty * $price,
                    ]);
                }
            }
        });

        return redirect()->route('transactions.index')
            ->with('status', 'Transaksi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id): RedirectResponse
    {
        $transaction = transactions::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('status', 'Transaksi berhasil dihapus.');
    }
}
