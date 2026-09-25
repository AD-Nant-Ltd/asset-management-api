<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,

            AssetTypeSeeder::class,
            AssetSubtypeSeeder::class,
            AssetStatusSeeder::class,
            AssetConditionSeeder::class,

            IncidentTypeSeeder::class,
            IncidentStatusSeeder::class,
        ]);
    }
}