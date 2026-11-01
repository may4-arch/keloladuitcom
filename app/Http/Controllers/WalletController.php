<?php

namespace App\Http\Controllers;

use App\Models\wallet;
use App\Models\transactions;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        // Ensure wallet records are fully synchronized
        wallet::syncForUser($userId);

        // Fetch latest wallet record to get current balance and money before
        $latestWallet = wallet::where('user_id', $userId)
            ->orderBy('wallet_id', 'desc')
            ->first();

        $currentBalance = $latestWallet ? (float) $latestWallet->money_total : 0;
        $moneyBefore = $latestWallet ? (float) $latestWallet->money_before : 0;

        // Total income and total expense calculations
        $totalIncome = (float) transactions::where('transactions.user_id', $userId)
            ->join('categories', 'transactions.category_id', '=', 'categories.category_id')
            ->where('categories.type', 1)
            ->sum('transactions.total');

        $totalExpense = (float) transactions::where('transactions.user_id', $userId)
            ->join('categories', 'transactions.category_id', '=', 'categories.category_id')
            ->where('categories.type', 2)
            ->sum('transactions.total');

        return Inertia::render('Wallet/Index', [
            'currentBalance' => $currentBalance,
            'moneyBefore' => $moneyBefore,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
        ]);
    }
}
