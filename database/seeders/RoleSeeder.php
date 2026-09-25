<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(
            ['role_name' => 'Administrator']
        );

        Role::updateOrCreate(
            ['role_name' => 'Authorised User']
        );
    }
}