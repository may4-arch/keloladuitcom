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

        // Total income and total expense calculations
        $totalIncome = (float) transactions::where('transactions.user_id', $userId)
            ->join('categories', 'transactions.category_id', '=', 'categories.category_id')
            ->where('categories.type', 1)
            ->sum('transactions.total');

        $totalExpense = (float) transactions::where('transactions.user_id', $userId)
            ->join('categories', 'transactions.category_id', '=', 'categories.category_id')
            ->where('categories.type', 2)
            ->sum('transactions.total');

        // Current balance is precisely total income minus total expense
        $currentBalance = (float) ($totalIncome - $totalExpense);

        // Fetch latest transaction in chronological order to get the correct money before
        $latestTransaction = transactions::where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->orderBy('transaction_id', 'desc')
            ->first();

        $moneyBefore = 0;
        if ($latestTransaction) {
            $latestWallet = wallet::where('transaction_id', $latestTransaction->transaction_id)->first();
            $moneyBefore = $latestWallet ? (float) $latestWallet->money_before : 0;
        }

        return Inertia::render('Wallet/Index', [
            'currentBalance' => $currentBalance,
            'moneyBefore' => $moneyBefore,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
        ]);
    }
}
