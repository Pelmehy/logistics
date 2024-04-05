<?php

namespace App\Models;

use App\Models\Material;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class);
    }
}
