<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $surname = fake()->lastName();
        $name = fake()->firstName();
        $parts = explode(" ", fake()->name());
        $patronymic = array_pop($parts);

        return [
            'full_name' => $surname.' '.$name.' '.$patronymic,
            'organization_id' => Organization::all()->random()->id,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->email,

        ];
    }
}
