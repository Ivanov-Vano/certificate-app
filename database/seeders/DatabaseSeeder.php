<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use http\Client;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ExpertSeeder::class,
            UserSeeder::class,
            RolesAndPermissionsSeeder::class,
            ChamberSeeder::class,
            CountrySeeder::class,
            CompanySeeder::class,
            OrganizationSeeder::class,
            TypeSeeder::class,
            CertificateSeeder::class,
            ClientSeeder::class,
        ]);
    }
}
