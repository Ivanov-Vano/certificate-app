<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'short_name' => $this->faker->company,
            'name' => $this->faker->companySuffix,
            'inn' => $this->faker->numerify('##########'),
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'additional_number' => $this->faker->numerify('##########'),
            'delivery_price' => $this->faker->randomFloat(2, 1, 99999999),
            'discount' => $this->faker->randomFloat(2, 1, 99999999),
        ];
    }
}
