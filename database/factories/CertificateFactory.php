<?php

namespace Database\Factories;

use App\Models\Chamber;
use App\Models\Company;
use App\Models\Expert;
use App\Models\Organization;
use App\Models\Sign;
use App\Models\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Certificate>
 */
class CertificateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type_id' => Type::all()->random()->id,
            'chamber_id' => Chamber::all()->random()->id,
            'company_id' => Company::all()->random()->id,
            'expert_id' => Expert::all()->random()->id,
            'invoice_issued' => $this->faker->boolean,
            'paid' => $this->faker->boolean,
            'date' => $this->faker->dateTimeThisYear(),
            'extended_page' => $this->faker->numberBetween(1,10),
            'payer_id' => Organization::all()->random()->id,
            'sender_id' => Organization::all()->random()->id,
            'scan_path' => $this->faker->sentence,
            'cost' => $this->faker->randomFloat(2, 1, 99999999),
            'number' => '2023/00'.$this->faker->numberBetween(10,99),
            'rec' => $this->faker->boolean,
            'second_invoice' => $this->faker->boolean,
        ];
    }
}
