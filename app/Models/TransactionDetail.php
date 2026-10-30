<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDetail extends Model
{
    // Tentukan nama tabel secara manual jika memakai plural/singular khusus
    protected $table = 'transactions_detail';
    protected $primaryKey = 'transaction_detail_id';

    protected $guarded = ['transaction_detail_id'];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(transactions::class, 'transaction_id', 'transaction_id');
    }
}
