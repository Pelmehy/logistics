<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

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

    public function mostOrderedProducts(int $limit = 5)
    {
        return DB::table("orders")
            ->join("order_product", function($join){
                $join->on("orders.id", "=", "order_product.order_id");
            })
            ->join("products", function($join){
                $join->on("order_product.product_id", "=", "products.id");
            })
            ->select(
                "order_product.product_id",
                "products.id",
                "products.name",
                "products.url",
                "products.price",
                DB::raw('sum(`order_product`.`count`) as product_count'),
                DB::raw('sum(`order_product`.`price`) as product_price')
            )
            ->orderBy("product_count","desc")
            ->groupBy("order_product.product_id")
            ->limit($limit)
            ->get();
    }

    public function statusCount(): Collection
    {
        return $this
            ->select(
                'status',
                DB::raw('count(`status`) as status_count')
            )
            ->groupBy('status')
            ->get();
    }

    public function manufacture(): BelongsTo
    {
        return $this->belongsTo(Manufacture::class);
    }

    public function finalizeProducts()
    {

    }

    public function finalizeMaterials()
    {

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
        if ($this->client_id !== 1) {
            $this->load('products');
            return $this->products;
        } else {
            $this->load('materials');
            return $this->materials;
        }
    }
}
