<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Product;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Product::count() > 0) {
            return;
        }

        Product::insert([
            [
                'name' => 'Тетра',
                'description' => 'empty description',
                'url' => fake()->url(),
                'quantity' => rand(0, 1000),
            ],
            [
                'name' => 'Пластикова бутилка',
                'description' => 'empty description',
                'url' => fake()->url(),
                'quantity' => rand(0, 1000),
            ],
            [
                'name' => 'Скляна бутилка',
                'description' => 'empty description',
                'url' => fake()->url(),
                'quantity' => rand(0, 1000),
            ],
            [
                'name' => 'Алюмінієва банка',
                'description' => 'empty description',
                'url' => fake()->url(),
                'quantity' => rand(0, 1000),
            ],
        ]);

        $dbProducts = Product::all();

        foreach ($dbProducts as $product) {
            $materials = Material::inRandomOrder()->limit(rand(1, 3))->pluck('id')->toArray();
            $product->materials()->sync($materials);
        }
    }
}
