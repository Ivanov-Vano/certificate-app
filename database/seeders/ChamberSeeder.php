<?php

namespace Database\Seeders;

use App\Models\Chamber;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChamberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = storage_path() . "/database/data/chambers.json";
        $chambers = json_decode(file_get_contents($path), true);
        foreach ($chambers as $chamber) {
            Chamber::updateOrCreate(['short_name' => $chamber['short_name']],
                [
                    'short_name' => $chamber['short_name'],
                ]
            );
        }
    }
}
