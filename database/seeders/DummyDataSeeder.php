<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * DummyDataSeeder
 *
 * Seeds all new modules following the relationship chain:
 *   SportsTag → Sport (+ pivot) → Venue → VenueSport → Court → User → UserFriend
 *
 * Run with:
 *   php artisan db:seed --class=DummyDataSeeder
 */
class DummyDataSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        // =====================================================================
        // 1. SPORTS TAGS
        // =====================================================================
        $tagIds = [];
        $tags = [
            [ 'name' => 'Indoor',   'slug' => 'indoor' ],
            [ 'name' => 'Outdoor',  'slug' => 'outdoor' ],
            [ 'name' => 'Team',     'slug' => 'team' ],
            [ 'name' => 'Solo',     'slug' => 'solo' ],
            [ 'name' => 'Racquet',  'slug' => 'racquet' ],
        ];

        foreach ( $tags as $tag ) {
            $tagIds[ $tag['slug'] ] = DB::table( 'sports_tags' )->insertGetId( array_merge( $tag, [
                'status'     => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ] ) );
        }

        $this->command->info( '✔ Sports Tags seeded (' . count( $tagIds ) . ')' );

        // =====================================================================
        // 2. SPORTS
        // =====================================================================
        $sportIds = [];
        $sports = [
            [
                'name'        => 'Badminton',
                'slug'        => 'badminton',
                'description' => 'A fast-paced racquet sport played on a court divided by a net.',
                'min_players' => 2,
                'max_players' => 4,
                'status'      => 10,
                'tags'        => [ 'indoor', 'racquet', 'team' ],
            ],
            [
                'name'        => 'Basketball',
                'slug'        => 'basketball',
                'description' => 'A team sport played on a rectangular court.',
                'min_players' => 5,
                'max_players' => 10,
                'status'      => 10,
                'tags'        => [ 'indoor', 'team' ],
            ],
            [
                'name'        => 'Tennis',
                'slug'        => 'tennis',
                'description' => 'A racquet sport that can be played outdoors or indoors.',
                'min_players' => 2,
                'max_players' => 4,
                'status'      => 10,
                'tags'        => [ 'outdoor', 'racquet', 'solo', 'team' ],
            ],
            [
                'name'        => 'Football',
                'slug'        => 'football',
                'description' => 'The world\'s most popular team sport played on a grass pitch.',
                'min_players' => 11,
                'max_players' => 22,
                'status'      => 10,
                'tags'        => [ 'outdoor', 'team' ],
            ],
        ];

        foreach ( $sports as $sport ) {
            $sportTags = $sport['tags'];
            unset( $sport['tags'] );

            $sportId = DB::table( 'sports' )->insertGetId( array_merge( $sport, [
                'active'     => true,
                'created_at' => $now,
                'updated_at' => $now,
            ] ) );

            $sportIds[ $sport['slug'] ] = $sportId;

            // // Attach tags via pivot
            // foreach ( $sportTags as $tagSlug ) {
            //     if ( isset( $tagIds[ $tagSlug ] ) ) {
            //         DB::table( 'sport_sports_tag' )->insert( [
            //             'sport_id'      => $sportId,
            //             'sports_tag_id' => $tagIds[ $tagSlug ],
            //         ] );
            //     }
            // }
        }

        $this->command->info( '✔ Sports seeded (' . count( $sportIds ) . ')' );

        // =====================================================================
        // 3. VENUES
        // =====================================================================
        $venueIds = [];
        $venues = [
            [
                'name'        => 'XPark Sports Hub KL',
                'slug'        => 'xpark-sports-hub-kl',
                'description' => 'Premium multi-sport facility in the heart of Kuala Lumpur.',
                'address_1'   => 'Lot 5, Jalan Ampang',
                'address_2'   => 'KLCC',
                'city'        => 'Kuala Lumpur',
                'state'       => 'Wilayah Persekutuan',
                'postcode'    => '50450',
                'latitude'    => 3.1574600,
                'longitude'   => 101.7137000,
                'status'      => 10,
            ],
            [
                'name'        => 'XPark Petaling Jaya',
                'slug'        => 'xpark-petaling-jaya',
                'description' => 'Modern sports complex serving the Klang Valley.',
                'address_1'   => 'No. 12, Jalan SS 22/23',
                'address_2'   => 'Damansara Jaya',
                'city'        => 'Petaling Jaya',
                'state'       => 'Selangor',
                'postcode'    => '47400',
                'latitude'    => 3.1073400,
                'longitude'   => 101.6140000,
                'status'      => 10,
            ],
            [
                'name'        => 'XPark Subang',
                'slug'        => 'xpark-subang',
                'description' => 'Community sports arena with affordable court rates.',
                'address_1'   => 'Jalan USJ 21/10',
                'address_2'   => 'USJ 21',
                'city'        => 'Subang Jaya',
                'state'       => 'Selangor',
                'postcode'    => '47640',
                'latitude'    => 3.0500000,
                'longitude'   => 101.5900000,
                'status'      => 10,
            ],
        ];

        foreach ( $venues as $venue ) {
            $venueIds[ $venue['slug'] ] = DB::table( 'venues' )->insertGetId( array_merge( $venue, [
                'created_at' => $now,
                'updated_at' => $now,
            ] ) );
        }

        $this->command->info( '✔ Venues seeded (' . count( $venueIds ) . ')' );

        // =====================================================================
        // 4. VENUE SPORTS
        //    Combines a Venue + Sport with scheduling / pricing rules.
        //    This is the pivot that courts reference via venue_sport_id.
        // =====================================================================
        $venueSportIds = [];
        $venueSports = [
            // KL Hub: Badminton + Tennis
            [
                'key'            => 'kl-badminton',
                'venue_id'       => $venueIds['xpark-sports-hub-kl'],
                'sport_id'       => $sportIds['badminton'],
                'slot_duration'  => 60,
                'price_per_slot' => 25.00,
                'open_time'      => '07:00:00',
                'close_time'     => '23:00:00',
                'operating_days' => json_encode( [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ] ),
                'status'         => 10,
            ],
            [
                'key'            => 'kl-tennis',
                'venue_id'       => $venueIds['xpark-sports-hub-kl'],
                'sport_id'       => $sportIds['tennis'],
                'slot_duration'  => 60,
                'price_per_slot' => 40.00,
                'open_time'      => '08:00:00',
                'close_time'     => '22:00:00',
                'operating_days' => json_encode( [ 'monday', 'wednesday', 'friday', 'saturday', 'sunday' ] ),
                'status'         => 10,
            ],
            // PJ: Basketball + Football
            [
                'key'            => 'pj-basketball',
                'venue_id'       => $venueIds['xpark-petaling-jaya'],
                'sport_id'       => $sportIds['basketball'],
                'slot_duration'  => 60,
                'price_per_slot' => 35.00,
                'open_time'      => '08:00:00',
                'close_time'     => '22:00:00',
                'operating_days' => json_encode( [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' ] ),
                'status'         => 10,
            ],
            [
                'key'            => 'pj-badminton',
                'venue_id'       => $venueIds['xpark-petaling-jaya'],
                'sport_id'       => $sportIds['badminton'],
                'slot_duration'  => 60,
                'price_per_slot' => 22.00,
                'open_time'      => '07:00:00',
                'close_time'     => '22:00:00',
                'operating_days' => json_encode( [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ] ),
                'status'         => 10,
            ],
            // Subang: Football + Badminton
            [
                'key'            => 'subang-football',
                'venue_id'       => $venueIds['xpark-subang'],
                'sport_id'       => $sportIds['football'],
                'slot_duration'  => 90,
                'price_per_slot' => 80.00,
                'open_time'      => '08:00:00',
                'close_time'     => '22:00:00',
                'operating_days' => json_encode( [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ] ),
                'status'         => 10,
            ],
            [
                'key'            => 'subang-badminton',
                'venue_id'       => $venueIds['xpark-subang'],
                'sport_id'       => $sportIds['badminton'],
                'slot_duration'  => 60,
                'price_per_slot' => 18.00,
                'open_time'      => '07:00:00',
                'close_time'     => '23:00:00',
                'operating_days' => json_encode( [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ] ),
                'status'         => 10,
            ],
        ];

        foreach ( $venueSports as $vs ) {
            $key = $vs['key'];
            unset( $vs['key'] );

            $venueSportIds[ $key ] = DB::table( 'venue_sports' )->insertGetId( array_merge( $vs, [
                'created_at' => $now,
                'updated_at' => $now,
            ] ) );
        }

        $this->command->info( '✔ Venue Sports seeded (' . count( $venueSportIds ) . ')' );

        // =====================================================================
        // 5. COURTS
        //    Each court belongs to one VenueSport via venue_sport_id.
        // =====================================================================
        $courts = [
            // KL Badminton courts
            [ 'venue_sport_id' => $venueSportIds['kl-badminton'],  'name' => 'Court A1', 'slug' => 'kl-badminton-a1', 'description' => 'Badminton court A1 with wooden flooring.',  'capacity' => 4,  'price_per_hour' => 25.00, 'status' => 10 ],
            [ 'venue_sport_id' => $venueSportIds['kl-badminton'],  'name' => 'Court A2', 'slug' => 'kl-badminton-a2', 'description' => 'Badminton court A2 with wooden flooring.',  'capacity' => 4,  'price_per_hour' => 25.00, 'status' => 10 ],
            [ 'venue_sport_id' => $venueSportIds['kl-badminton'],  'name' => 'Court A3', 'slug' => 'kl-badminton-a3', 'description' => 'Badminton court A3 — air-conditioned.',    'capacity' => 4,  'price_per_hour' => 30.00, 'status' => 10 ],
            // KL Tennis courts
            [ 'venue_sport_id' => $venueSportIds['kl-tennis'],     'name' => 'Court T1', 'slug' => 'kl-tennis-t1',   'description' => 'Hard-surface tennis court.',               'capacity' => 4,  'price_per_hour' => 40.00, 'status' => 10 ],
            [ 'venue_sport_id' => $venueSportIds['kl-tennis'],     'name' => 'Court T2', 'slug' => 'kl-tennis-t2',   'description' => 'Clay-surface tennis court.',              'capacity' => 4,  'price_per_hour' => 45.00, 'status' => 10 ],
            // PJ Basketball
            [ 'venue_sport_id' => $venueSportIds['pj-basketball'], 'name' => 'Court B1', 'slug' => 'pj-basketball-b1', 'description' => 'Full-size basketball court.',           'capacity' => 10, 'price_per_hour' => 35.00, 'status' => 10 ],
            [ 'venue_sport_id' => $venueSportIds['pj-basketball'], 'name' => 'Court B2', 'slug' => 'pj-basketball-b2', 'description' => 'Half-court basketball (3v3).',          'capacity' => 6,  'price_per_hour' => 20.00, 'status' => 10 ],
            // PJ Badminton
            [ 'venue_sport_id' => $venueSportIds['pj-badminton'],  'name' => 'Court C1', 'slug' => 'pj-badminton-c1', 'description' => 'Standard badminton court.',              'capacity' => 4,  'price_per_hour' => 22.00, 'status' => 10 ],
            [ 'venue_sport_id' => $venueSportIds['pj-badminton'],  'name' => 'Court C2', 'slug' => 'pj-badminton-c2', 'description' => 'Standard badminton court.',              'capacity' => 4,  'price_per_hour' => 22.00, 'status' => 10 ],
            // Subang Football
            [ 'venue_sport_id' => $venueSportIds['subang-football'],  'name' => 'Pitch F1', 'slug' => 'subang-football-f1', 'description' => 'Astroturf 5-a-side pitch.',        'capacity' => 10, 'price_per_hour' => 80.00, 'status' => 10 ],
            [ 'venue_sport_id' => $venueSportIds['subang-football'],  'name' => 'Pitch F2', 'slug' => 'subang-football-f2', 'description' => 'Astroturf 5-a-side pitch.',        'capacity' => 10, 'price_per_hour' => 80.00, 'status' => 10 ],
            // Subang Badminton
            [ 'venue_sport_id' => $venueSportIds['subang-badminton'], 'name' => 'Court D1', 'slug' => 'subang-badminton-d1', 'description' => 'Economy badminton court.',       'capacity' => 4,  'price_per_hour' => 18.00, 'status' => 10 ],
            [ 'venue_sport_id' => $venueSportIds['subang-badminton'], 'name' => 'Court D2', 'slug' => 'subang-badminton-d2', 'description' => 'Economy badminton court.',       'capacity' => 4,  'price_per_hour' => 18.00, 'status' => 10 ],
            [ 'venue_sport_id' => $venueSportIds['subang-badminton'], 'name' => 'Court D3', 'slug' => 'subang-badminton-d3', 'description' => 'Economy badminton court.',       'capacity' => 4,  'price_per_hour' => 18.00, 'status' => 10 ],
        ];

        foreach ( $courts as $court ) {
            DB::table( 'courts' )->insert( array_merge( $court, [
                'active'     => true,
                'created_at' => $now,
                'updated_at' => $now,
            ] ) );
        }

        $this->command->info( '✔ Courts seeded (' . count( $courts ) . ')' );

        // =====================================================================
        // 6. USERS
        // =====================================================================
        $userIds = [];
        $users = [
            [ 'key' => 'alice',   'username' => 'alice_lim',   'fullname' => 'Alice Lim',    'first_name' => 'Alice',  'last_name' => 'Lim',   'email' => 'alice@xpark.test',   'phone_number' => '121110001', 'calling_code' => '+60' ],
            [ 'key' => 'bob',     'username' => 'bob_tan',     'fullname' => 'Bob Tan',      'first_name' => 'Bob',    'last_name' => 'Tan',   'email' => 'bob@xpark.test',     'phone_number' => '121110002', 'calling_code' => '+60' ],
            [ 'key' => 'charlie', 'username' => 'charlie_ng',  'fullname' => 'Charlie Ng',   'first_name' => 'Charlie','last_name' => 'Ng',    'email' => 'charlie@xpark.test', 'phone_number' => '121110003', 'calling_code' => '+60' ],
            [ 'key' => 'diana',   'username' => 'diana_wong',  'fullname' => 'Diana Wong',   'first_name' => 'Diana',  'last_name' => 'Wong',  'email' => 'diana@xpark.test',   'phone_number' => '121110004', 'calling_code' => '+60' ],
            [ 'key' => 'edward',  'username' => 'edward_lee',  'fullname' => 'Edward Lee',   'first_name' => 'Edward', 'last_name' => 'Lee',   'email' => 'edward@xpark.test',  'phone_number' => '121110005', 'calling_code' => '+60' ],
        ];

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
        // 7. USER FRIENDS
        //    Status: 10=pending, 20=accepted, 30=declined
        // =====================================================================
        $friendships = [
            [ 'user_id' => $userIds['alice'],   'friend_id' => $userIds['bob'],     'status' => 20 ], // accepted
            [ 'user_id' => $userIds['alice'],   'friend_id' => $userIds['charlie'], 'status' => 20 ], // accepted
            [ 'user_id' => $userIds['bob'],     'friend_id' => $userIds['diana'],   'status' => 10 ], // pending
            [ 'user_id' => $userIds['charlie'], 'friend_id' => $userIds['edward'],  'status' => 20 ], // accepted
            [ 'user_id' => $userIds['diana'],   'friend_id' => $userIds['edward'],  'status' => 30 ], // declined
        ];

        foreach ( $friendships as $friendship ) {
            DB::table( 'user_friends' )->insert( array_merge( $friendship, [
                'created_at' => $now,
                'updated_at' => $now,
            ] ) );
        }

        $this->command->info( '✔ User Friends seeded (' . count( $friendships ) . ')' );

        $this->command->info( '' );
        $this->command->info( '🎉 DummyDataSeeder complete.' );
        $this->command->info( '   Test user password: password123' );
    }
}
