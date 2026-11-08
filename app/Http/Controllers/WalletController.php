<?php

namespace App\Http\Controllers;

use App\Models\Budget;
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
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

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

        // Fetch all budgets for Category Budget report
        $budgets = Budget::where('user_id', $userId)
            ->with('category')
            ->orderBy('priority', 'desc')
            ->orderBy('budget_id', 'desc')
            ->get();

        $categoryBudgets = [];

        foreach ($budgets as $budget) {
            $trxQuery = transactions::where('user_id', $userId)
                ->where('category_id', $budget->category_id);

            if (!empty($startDate)) {
                $trxQuery->whereDate('date', '>=', $startDate);
            }
            if (!empty($endDate)) {
                $trxQuery->whereDate('date', '<=', $endDate);
            }

            $catTotalExpense = (float) (clone $trxQuery)->sum('total');

            // Find first and last transaction dates in this period
            $firstTrx = (clone $trxQuery)->orderBy('date', 'asc')->first();
            $lastTrx = (clone $trxQuery)->orderBy('date', 'desc')->first();

            $firstDate = $firstTrx && $firstTrx->date ? $firstTrx->date->format('Y-m-d') : null;
            $lastDate = $lastTrx && $lastTrx->date ? $lastTrx->date->format('Y-m-d') : null;

            $budgetExpense = (float) $budget->maximal;
            $status = $catTotalExpense > $budgetExpense ? 'Over Budget' : 'Normal';

            $categoryBudgets[] = [
                'budget_id' => $budget->budget_id,
                'budget_name' => $budget->name,
                'category_id' => $budget->category_id,
                'category_name' => $budget->category ? $budget->category->name : $budget->name,
                'category_type' => $budget->category ? $budget->category->type : 2,
                'first_date' => $firstDate,
                'last_date' => $lastDate,
                'budget_expense' => $budgetExpense,
                'total_expense' => $catTotalExpense,
                'status' => $status,
            ];
        }

        return Inertia::render('Wallet/Index', [
            'currentBalance' => $currentBalance,
            'moneyBefore' => $moneyBefore,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'categoryBudgets' => $categoryBudgets,
            'filters' => [
                'start_date' => $startDate ?? '',
                'end_date' => $endDate ?? '',
            ],
        ]);
    }
}
