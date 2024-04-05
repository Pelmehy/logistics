<?php

namespace App\Models;

use App\Models\Product;
use App\Models\Material;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Storage extends Model
{
    use HasFactory;

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('storage_quantity');
    }

    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class)->withPivot('storage_quantity');
    }
}
