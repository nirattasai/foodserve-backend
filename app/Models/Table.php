<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    use HasFactory;

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(merchant::class, 'merchant_id');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class, 'table_id');
    }
}
