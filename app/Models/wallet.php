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
}
