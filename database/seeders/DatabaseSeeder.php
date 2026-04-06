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
            AdministratorSeeder::class,
            CountrySeeder::class,
            // Run separately: php artisan db:seed --class=DummyDataSeeder
            // DummyDataSeeder::class,
        ] );
    }
}
