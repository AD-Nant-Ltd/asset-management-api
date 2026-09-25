<?php

namespace Database\Seeders;

use App\Models\IncidentStatus;
use Illuminate\Database\Seeder;

class IncidentStatusSeeder extends Seeder
{
    public function run(): void
    {
        IncidentStatus::updateOrCreate(
            ['incident_status' => 'Open'],
            ['active' => true]
        );

        IncidentStatus::updateOrCreate(
            ['incident_status' => 'Resolved'],
            ['active' => true]
        );
    }
}