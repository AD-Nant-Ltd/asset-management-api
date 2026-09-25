<?php

namespace Database\Seeders;

use App\Models\AssetStatus;
use Illuminate\Database\Seeder;

class AssetStatusSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Operational',
            'Under Repair',
            'Awaiting Warranty Action',
            'Unavailable',
        ] as $status) {
            AssetStatus::updateOrCreate(
                ['asset_status' => $status],
                ['active' => true]
            );
        }
    }
}