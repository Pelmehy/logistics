<?php

namespace Database\Seeders;

use App\Models\Storage;
use App\Models\Product;
use App\Models\Material;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StorageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Storage::insert([
           [
               'address' => fake()->address(),
               'height' => rand(2, 4),
               'square' => rand(15, 400)
           ],
           [
               'address' => fake()->address(),
               'height' => rand(2, 4),
               'square' => rand(15, 400)
           ],
           [
               'address' => fake()->address(),
               'height' => rand(2, 4),
               'square' => rand(15, 400)
           ],
           [
               'address' => fake()->address(),
               'height' => rand(2, 4),
               'square' => rand(15, 400)
           ],
        ]);

        $storages = Storage::all();
        foreach ($storages as $storage) {
            $capacity = $storage->square * $storage->height;

            $dataList = Material::inRandomOrder()->limit(rand(1, 3))->pluck('id')->toArray();
            $materials = [];
            foreach ($dataList as $data) {
                $storageQuantity = rand(1, $capacity);
                $capacity -= $storageQuantity;
                $materials[] = [
                    'material_id' => $data,
                    'storage_quantity' => $storageQuantity
                ];
            }

            $dataList = Product::inRandomOrder()->limit(rand(1, 3))->pluck('id')->toArray();
            $products = [];
            foreach ($dataList as $data) {
                $storageQuantity = rand(1, $capacity);
                $capacity -= $storageQuantity;
                $products[] = [
                    'product_id' => $data,
                    'storage_quantity' => $storageQuantity
                ];
            }

            $storage->materials()->sync($materials);
            $storage->products()->sync($products);
        }
    }
}
