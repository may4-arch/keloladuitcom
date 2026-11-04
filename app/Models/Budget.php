<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    // 1. Secara default Laravel mencari tabel jamak ('budgets').
    // Karena nama tabel di migration adalah 'budget', definisikan eksplisit:
    protected $table = 'budget';

    // 2. Custom primary key
    protected $primaryKey = 'budget_id';

    // 3. Kolom yang boleh diisi (mass assignable)
    protected $fillable = [
        'category_id',
        'user_id',
        'name',
        'maximal',
        'minimal',
        'priority',
    ];

    // 4. Casting tipe data
    protected $casts = [
        'maximal' => 'decimal:2',
        'minimal' => 'decimal:2',
        'priority' => 'integer',
    ];

    /**
     * Relasi ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }
}