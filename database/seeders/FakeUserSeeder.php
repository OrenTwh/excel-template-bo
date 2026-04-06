<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * FakeUserSeeder
 *
 * Seeds additional fake users, each with both wallet types:
 *   type 1 = Xpark Points
 *   type 2 = Xpark Credits (Wallet)
 *
 * Run with:
 *   php artisan db:seed --class=FakeUserSeeder
 */
class FakeUserSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        // =====================================================================
        // USERS
        // =====================================================================
        $users = [
            [ 'key' => 'farah',   'username' => 'farah_aziz',    'fullname' => 'Farah Aziz',      'first_name' => 'Farah',   'last_name' => 'Aziz',    'email' => 'farah@xpark.test',   'phone_number' => '121110011', 'calling_code' => '+60' ],
            [ 'key' => 'hafiz',   'username' => 'hafiz_rahman',  'fullname' => 'Hafiz Rahman',    'first_name' => 'Hafiz',   'last_name' => 'Rahman',  'email' => 'hafiz@xpark.test',   'phone_number' => '121110012', 'calling_code' => '+60' ],
            [ 'key' => 'iris',    'username' => 'iris_chong',    'fullname' => 'Iris Chong',      'first_name' => 'Iris',    'last_name' => 'Chong',   'email' => 'iris@xpark.test',    'phone_number' => '121110013', 'calling_code' => '+60' ],
            [ 'key' => 'jason',   'username' => 'jason_kumar',   'fullname' => 'Jason Kumar',     'first_name' => 'Jason',   'last_name' => 'Kumar',   'email' => 'jason@xpark.test',   'phone_number' => '121110014', 'calling_code' => '+60' ],
            [ 'key' => 'karen',   'username' => 'karen_yap',     'fullname' => 'Karen Yap',       'first_name' => 'Karen',   'last_name' => 'Yap',     'email' => 'karen@xpark.test',   'phone_number' => '121110015', 'calling_code' => '+60' ],
            [ 'key' => 'liong',   'username' => 'liong_wei',     'fullname' => 'Liong Wei',       'first_name' => 'Liong',   'last_name' => 'Wei',     'email' => 'liong@xpark.test',   'phone_number' => '121110016', 'calling_code' => '+60' ],
            [ 'key' => 'maya',    'username' => 'maya_hassan',   'fullname' => 'Maya Hassan',     'first_name' => 'Maya',    'last_name' => 'Hassan',  'email' => 'maya@xpark.test',    'phone_number' => '121110017', 'calling_code' => '+60' ],
            [ 'key' => 'nabil',   'username' => 'nabil_ismail',  'fullname' => 'Nabil Ismail',    'first_name' => 'Nabil',   'last_name' => 'Ismail',  'email' => 'nabil@xpark.test',   'phone_number' => '121110018', 'calling_code' => '+60' ],
        ];

        // Wallet balances per user [points (type 1), credits (type 2)]
        $walletBalances = [
            'farah'  => [ 150.00,  25.50 ],
            'hafiz'  => [ 320.00,   0.00 ],
            'iris'   => [  80.00, 100.00 ],
            'jason'  => [   0.00,  50.00 ],
            'karen'  => [ 500.00, 200.00 ],
            'liong'  => [  60.00,  15.75 ],
            'maya'   => [ 220.00,  80.00 ],
            'nabil'  => [  10.00,   5.00 ],
        ];

        $userIds = [];

        foreach ( $users as $user ) {
            $key = $user['key'];
            unset( $user['key'] );

            $userIds[ $key ] = DB::table( 'users' )->insertGetId( array_merge( $user, [
                'password'          => Hash::make( 'password123' ),
                'invitation_code'   => strtoupper( Str::random( 6 ) ),
                'date_of_birth'     => '1995-06-15',
                'status'            => 10,
                'account_type'      => 10,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ] ) );
        }

        $this->command->info( '✔ Users seeded (' . count( $userIds ) . ')' );

        // =====================================================================
        // WALLETS  (type 1 = Points, type 2 = Credits)
        // =====================================================================
        $walletCount = 0;

        foreach ( $userIds as $key => $userId ) {
            [ $points, $credits ] = $walletBalances[ $key ];

            DB::table( 'wallets' )->insert( [
                [
                    'user_id'    => $userId,
                    'type'       => 1,
                    'balance'    => $points,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'user_id'    => $userId,
                    'type'       => 2,
                    'balance'    => $credits,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ] );

            $walletCount += 2;
        }

        $this->command->info( '✔ Wallets seeded (' . $walletCount . ' — 2 per user)' );

        $this->command->info( '' );
        $this->command->info( '🎉 FakeUserSeeder complete.' );
        $this->command->info( '   Test user password: password123' );
    }
}
