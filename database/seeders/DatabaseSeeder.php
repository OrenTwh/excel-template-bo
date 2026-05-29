<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // User::factory(10)->create();
        $this->call( [
            AdministratorSeeder::class,   // roles + developer admin account (required for login)
            CountrySeeder::class,          // countries reference table
            FxSeeder::class,               // FX customers, transactions, arrangements, master daily
            // Run separately: php artisan db:seed --class=DummyDataSeeder
            // DummyDataSeeder::class,
        ] );
    }
}
