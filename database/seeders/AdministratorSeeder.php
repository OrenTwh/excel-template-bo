<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdministratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Superadmin
        $superAdminRole = DB::table( 'roles' )->insertGetId( [
            'name' => 'super_admin',
            'guard_name' => 'admin',
            'created_at' => date( 'Y-m-d H:i:s' ),
            'updated_at' => date( 'Y-m-d H:i:s' ),
        ] );

        $superAdmin2 = DB::table( 'administrators' )->insertGetId( [
            'name' => 'developer',
            'email' => 'developer-acc@gmail.com',
            'password' => Hash::make( 'developer-1234' ),
            'fullname' => 'Developer',
            'phone_number' => '11112222',
            'role' => $superAdminRole,
            'status' => 10,
            'created_at' => date( 'Y-m-d H:i:s' ),
            'updated_at' => date( 'Y-m-d H:i:s' ),
        ] );

        DB::table( 'model_has_roles' )->insert( [
            'role_id' => $superAdminRole,
            'model_type' => 'App\Models\Administrator',
            'model_id' => $superAdmin2,
        ] );

        // Admin
        $adminRole = DB::table( 'roles' )->insertGetId( [
            'name' => 'admin',
            'guard_name' => 'admin',
            'created_at' => date( 'Y-m-d H:i:s' ),
            'updated_at' => date( 'Y-m-d H:i:s' ),
        ] );
    }
}
