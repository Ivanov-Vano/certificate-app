<?php

namespace Database\Seeders;

use App\Models\Deliveryman;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeliverymanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Deliveryman::factory(5)->create();
    }
}
