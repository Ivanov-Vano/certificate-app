<?php

namespace Database\Factories;

use App\Models\Deliveryman;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Delivery>
 */
class DeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => $this->faker->numberBetween(1,10),
            'accepted_at' => $this->faker->dateTimeThisYear(),
            'cost' => $this->faker->randomFloat(2, 1, 9999999999),
            'organization_id' => Organization::all()->random()->id,
            'deliveryman_id' => Deliveryman::all()->random()->id,
            'is_pickup' => $this->faker->boolean,
            'delivered_at' => $this->faker->dateTimeThisYear(),
        ];
    }
}
