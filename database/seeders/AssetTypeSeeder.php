<?php

namespace Database\Seeders;

use App\Models\AssetType;
use Illuminate\Database\Seeder;

class AssetTypeSeeder extends Seeder
{
    public function run(): void
    {
        AssetType::updateOrCreate(
            ['asset_type' => 'IT Equipment'],
            ['active' => true]
        );
    }
}