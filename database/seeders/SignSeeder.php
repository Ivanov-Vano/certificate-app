<?php

namespace Database\Seeders;

use App\Models\Sign;
use Illuminate\Database\Seeder;

class SignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = storage_path() . "/database/data/signs.json";
        $signs = json_decode(file_get_contents($path), true);
        foreach ($signs as $sign) {
            Sign::updateOrCreate(['name' => $sign['name']],
                [
                    'name' => $sign['name'],
                ]
            );
        }
    }
}
