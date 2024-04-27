<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Material::count() > 0) {
            return;
        }

        Material::insert([
           [
               'name' => 'Картон',
               'description' => 'empty description',
               'url' =>null,
               'quantity' => rand(0, 1000),
           ],
           [
               'name' => 'Поліетилен',
               'description' => 'empty description',
               'url' =>null,
               'quantity' => rand(0, 1000),
           ],
           [
               'name' => 'Алюмінієва фольга',
               'description' => 'empty description',
               'url' =>null,
               'quantity' => rand(0, 1000),
           ],
           [
               'name' => 'Скло',
               'description' => 'empty description',
               'url' =>null,
               'quantity' => rand(0, 1000),
           ],
           [
               'name' => 'Алюміній',
               'description' => 'empty description',
               'url' =>null,
               'quantity' => rand(0, 1000),
           ],

        ]);
    }
}
