<?php

namespace App\Models;

use App\Models\Material;
use App\Models\Storage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'name', 'description', 'url', 'quantity'
    ];

    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class)->withPivot('storage_quantity');
    }

    public function storages(): BelongsToMany
    {
        return $this->belongsToMany(Storage::class);
    }
}
