<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Menu;

class Catagory extends Model
{
    use HasFactory;
    
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class, 'catagory_id');
    }
}
