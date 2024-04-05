<?php

namespace App\Models;

use App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Material extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = ['url'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function storages(): BelongsToMany
    {
        return $this->belongsToMany(Storage::class)->withPivot('storage_quantity');
    }
}
