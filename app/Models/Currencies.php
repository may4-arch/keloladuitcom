<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Model;

class Currencies extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'symbol_position',
        'decimal_places',
        'thousand_separator',
        'decimal_separator',
        'exchange_rate',
        'is_base',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'decimal_places' => 'integer',
            'exchange_rate' => 'decimal:6',
            'is_base' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'currency_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function format(float|int|string $amount): string
    {
        $numeric = (float) $amount;
        $formattedNumber = number_format(
            $numeric,
            $this->decimal_places,
            $this->decimal_separator,
            $this->thousand_separator
        );

        return $this->symbol_position === 'suffix'
            ? "{$formattedNumber} {$this->symbol}"
            : "{$this->symbol}{$formattedNumber}";
    }
}
