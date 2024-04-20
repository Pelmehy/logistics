<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Client;
use App\Models\Manufacture;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CountrySeeder::class);
        $this->call(LanguageSeeder::class);
        $this->call(MaterialSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(StorageSeeder::class);
        User::factory(50)->create();

        Manufacture::factory(5)->create();
        $this->syncRndItems(Manufacture::class, Material::class);

        Client::factory(10)->create();
    }

    private function syncRndItems($owner, $element): void
    {
        // init models
        $ownerModel =  app($owner);
        $elementModel = app($element);

        //get tied table name
        $elementTableName = $elementModel->getTable();

        // get all owner entries
        $dbOwners = $ownerModel->all();

        // generate random relationships
        foreach ($dbOwners as $dbOwner) {
            $elements = $elementModel->inRandomOrder()->limit(rand(1, 3))->pluck('id')->toArray();
            $dbOwner->$elementTableName()->sync($elements);
        }
    }
}
