<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Manufacture;
use App\Models\Material;
use App\Models\Product;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    static array $statuses = [
        'placed',
        'paid',
        'produced',
        'shipped',
        'delivered'
    ];

    private array $syncTypes;
    private array $ordersItems;


    static int $statusCount = 4;
    public function definition(): array
    {
        return [
            'due_date' => fake()->dateTimeBetween('-1 month', '+ 6 month'),
            'client_id' => null,
            'manufacture_id' => null,
            'status' => $this->randomStatus(),
            'total' => 0,
        ];
    }

    private function randomStatus(): string
    {
        return self::$statuses[rand(0, self::$statusCount)];
    }
}
