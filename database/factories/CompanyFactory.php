<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
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
            'address' => $this->faker->address,
            'registration_number' => $this->faker->numerify('##########'),
            'country_id' => Country::all()->random()->id,
        ];
    }
}
