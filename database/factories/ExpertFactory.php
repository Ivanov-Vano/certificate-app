<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expert>
 */
class ExpertFactory extends Factory
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
            'surname' => $surname,
            'name' => $name,
            'patronymic' => $patronymic,
            'full_name' => $surname.' '.$name.' '.$patronymic,
        ];
    }
}
