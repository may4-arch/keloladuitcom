<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class wallet extends Model
{
    public const UPDATED_AT = null;
 
    protected $table = 'wallet';
 
    protected $primaryKey = 'wallet_id';
 
    protected $fillable = [
        'user_id',
        'transaction_id',
        'money_before',
        'money_total',
        'created_at',
    ];
 
    protected $casts = [
        'money_before' => 'decimal:2',
        'money_total' => 'decimal:2',
    ];
 
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
 
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(transactions::class, 'transaction_id', 'transaction_id');
    }

    /**
     * Sync and recalculate wallet ledger history for a specific user.
     */
    public static function syncForUser(int $userId): void
    {
        $transactions = transactions::where('user_id', $userId)
            ->with('category')
            ->orderBy('date', 'asc')
            ->orderBy('transaction_id', 'asc')
            ->get();

        $trxIds = $transactions->pluck('transaction_id')->toArray();
        static::where('user_id', $userId)->whereNotIn('transaction_id', $trxIds)->delete();

        $runningBalance = 0;
        foreach ($transactions as $trx) {
            $moneyBefore = $runningBalance;
            $type = $trx->category ? (int) $trx->category->type : 2;
            $amount = (float) $trx->total;

            if ($type === 1) {
                $runningBalance += $amount;
            } else {
                $runningBalance -= $amount;
            }

            $moneyTotal = $runningBalance;

            static::updateOrCreate(
                [
                    'transaction_id' => $trx->transaction_id,
                ],
                [
                    'user_id' => $userId,
                    'money_before' => $moneyBefore,
                    'money_total' => $moneyTotal,
                    'created_at' => $trx->date ?? $trx->created_at,
                ]
            );
        }
    }
}
