<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'category_id';

    protected $fillable = [
        'user_id',
        'currency_id',
        'name',
        'priority',
        'type',
    ];
    

    protected $casts = [
        'currency_id' => 'integer',
        'priority' => 'integer',
        'type' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected $attributes = [
        'currency_id' => 1,
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currencies::class, 'currency_id', 'id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(transactions::class, 'category_id', 'category_id');
    }
}
