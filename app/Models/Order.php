<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'due_date', 'client_id', 'manufacture_id', 'status', 'total'
    ];


    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class)
            ->as('orderItems')->withPivot('count', 'total');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->as('orderItems')->withPivot('count', 'total');
    }

    public function client(): BelongsTo
    {
        return $this->BelongsTo(Client::class);
    }
}
