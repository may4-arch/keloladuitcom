<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\transactions;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with Category Budget report.
     */
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Fetch all budgets for this user with their category
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

            $totalExpense = (float) (clone $trxQuery)->sum('total');

            // Find first and last transaction dates in this period
            $firstTrx = (clone $trxQuery)->orderBy('date', 'asc')->first();
            $lastTrx = (clone $trxQuery)->orderBy('date', 'desc')->first();

            $firstDate = $firstTrx && $firstTrx->date ? $firstTrx->date->format('Y-m-d') : null;
            $lastDate = $lastTrx && $lastTrx->date ? $lastTrx->date->format('Y-m-d') : null;

            $budgetExpense = (float) $budget->maximal;
            $status = $totalExpense > $budgetExpense ? 'Over Budget' : 'Normal';

            $categoryBudgets[] = [
                'budget_id' => $budget->budget_id,
                'budget_name' => $budget->name,
                'category_id' => $budget->category_id,
                'category_name' => $budget->category ? $budget->category->name : $budget->name,
                'category_type' => $budget->category ? $budget->category->type : 2,
                'first_date' => $firstDate,
                'last_date' => $lastDate,
                'budget_expense' => $budgetExpense,
                'total_expense' => $totalExpense,
                'status' => $status,
            ];
        }

        // Summary metrics
        $totalBudget = array_sum(array_column($categoryBudgets, 'budget_expense'));
        $totalSpent = array_sum(array_column($categoryBudgets, 'total_expense'));
        $overBudgetCount = count(array_filter($categoryBudgets, fn($item) => $item['status'] === 'Over Budget'));

        return Inertia::render('Dashboard', [
            'categoryBudgets' => $categoryBudgets,
            'filters' => [
                'start_date' => $startDate ?? '',
                'end_date' => $endDate ?? '',
            ],
            'summary' => [
                'total_budget' => $totalBudget,
                'total_spent' => $totalSpent,
                'over_budget_count' => $overBudgetCount,
            ],
        ]);
    }
}
