<?php

namespace Database\Seeders;

use App\Models\Type;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = storage_path() . "/database/data/types.json";
        $types = json_decode(file_get_contents($path), true);
        foreach ($types as $type) {
            Type::updateOrCreate(['short_name' => $type['short_name']],
                [
                    'short_name' => $type['short_name'],
                ]
            );
        }
    }
}
