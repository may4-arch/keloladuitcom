<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Currencies;


class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'currency_id',
        'name',
        'type',
        'opening_balance',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currencies::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}