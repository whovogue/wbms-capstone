<?php

namespace Database\Seeders;

use App\Models\Charge;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'MAGSAYSAY ADMIN',
            'email' => 'magsaysaywbms@gmail.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        Charge::create([
            'name' => 'Residential',
            'minimum' => 125,
            'minimumConsumption' => 0,
            'exceedChargePerUnit' => 0,
        ]);

        Charge::create([
            'name' => 'Commercial',
            'minimum' => 150,
            'minimumConsumption' => 0,
            'exceedChargePerUnit' => 0,
        ]);
    }
}
