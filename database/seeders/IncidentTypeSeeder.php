<?php

namespace Database\Seeders;

use App\Models\IncidentType;
use Illuminate\Database\Seeder;

class IncidentTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Fault',
            'Damage',
            'Loss',
            'Theft',
            'Other',
        ] as $type) {
            IncidentType::updateOrCreate(
                ['incident_type' => $type],
                ['active' => true]
            );
        }
    }
}