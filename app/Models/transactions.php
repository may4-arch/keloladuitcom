<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class transactions extends Model
{
    protected $primaryKey = 'transaction_id';
 
    protected $fillable = [ 
        'user_id',
        'category_id',
        'total',
        'date',
        'notes',
    ];
 

    protected $casts = [
        'total' => 'decimal:2',
        'date' => 'datetime',
    ];
 
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
 
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }
 
    public function wallet(): HasOne
    {
        return $this->hasOne(wallet::class, 'transaction_id', 'transaction_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id', 'transaction_id');
    }
}
