<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            NumberSeriesSeeder::class,
            AgentSeeder::class,
            UserSeeder::class,
            SchemeSeeder::class,
            MemberSeeder::class,
            EventAndPayoutSeeder::class,
        ]);
    }
}
