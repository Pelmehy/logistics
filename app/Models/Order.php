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
            ->as('orderItems')->withPivot('count', 'price', 'total');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->as('orderItems')->withPivot('count', 'price', 'total');
    }

    public function manufacture(): BelongsTo
    {
        return $this->belongsTo(Manufacture::class);
    }

    public function client(): BelongsTo
    {
        return $this->BelongsTo(Client::class);
    }

    public function updateOrderTotal(): void
    {
        $orderItems = $this->getOrderItems();

        $total = 0;
        foreach ($orderItems as $orderItem) {
            $total += $orderItem->orderItems->total;
        }

        $this->total = $total;
        $this->save();
    }

    public function getOrderItems()
    {
        if ($this->client_id) {
            $this->load('products');
            return $this->products;
        } else {
            $this->load('materials');
            return $this->materials;
        }
    }
}
