<?php

namespace Database\Seeders;

use App\Models\AssetCondition;
use Illuminate\Database\Seeder;

class AssetConditionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'New',
            'Good',
            'Fair',
            'Poor',
            'Damaged',
        ] as $condition) {
            AssetCondition::updateOrCreate(
                ['asset_condition' => $condition],
                ['active' => true]
            );
        }
    }
}