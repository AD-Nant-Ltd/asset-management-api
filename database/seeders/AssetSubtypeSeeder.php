<?php

namespace Database\Seeders;

use App\Models\AssetSubtype;
use App\Models\AssetType;
use Illuminate\Database\Seeder;

class AssetSubtypeSeeder extends Seeder
{
    public function run(): void
    {
        $itEquipment = AssetType::where('asset_type', 'IT Equipment')->firstOrFail();

        foreach ([
            'Laptop',
            'Surface Pro 9',
            'Surface Pro 12',
        ] as $subtype) {
            AssetSubtype::updateOrCreate(
                [
                    'asset_type_id' => $itEquipment->id,
                    'asset_subtype' => $subtype,
                ],
                [
                    'active' => true,
                ]
            );
        }
    }
}