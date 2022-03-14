<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\User;
use App\Models\Table;
use App\Models\Catagory;

class Merchant extends Model
{
    use HasFactory;

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'onwer_id');
    }

    public function tables(): HasMany
    {
        return $this->hasMany(Table::class, 'merchant_id');
    }

    public function catagories(): HasMany
    {
        return $this->hasMany(Catagory::class, 'merchant_id');
    }

}
