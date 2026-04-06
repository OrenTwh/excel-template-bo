<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * SportProductSeeder
 *
 * Seeds the sport product module following the relationship chain:
 *   SportProductCategory → SportProduct (+ sport pivot) → SportProductVariant → SportProductStock
 *
 * Prerequisite: DummyDataSeeder (sports must exist)
 *
 * Run with:
 *   php artisan db:seed --class=SportProductSeeder
 */
class SportProductSeeder extends Seeder
{
    /** Wrap a plain string into the translatable JSON format: {"en": "..."} */
    private function t( string $value ): string
    {
        return json_encode( [ 'en' => $value ] );
    }

    /** Return existing ID by slug, or insert and return new ID. */
    private function firstOrInsertBySlug( string $table, string $slug, array $data ): int
    {
        $existing = DB::table( $table )->where( 'slug', $slug )->first();

        if ( $existing ) {
            return $existing->id;
        }

        return DB::table( $table )->insertGetId( $data );
    }

    public function run()
    {
        $now = now();

        // Resolve existing sport IDs by slug (seeded by DummyDataSeeder)
        $sports = DB::table('sports')->whereIn('slug', ['badminton', 'tennis', 'basketball', 'football'])
            ->pluck('id', 'slug');

        // =====================================================================
        // 1. SPORT PRODUCT CATEGORIES
        // =====================================================================
        $categoryIds = [];

        $parentCategories = [
            [ 'key' => 'equipment',  'name' => $this->t( 'Equipment' ),   'slug' => 'equipment',   'sequence' => 1 ],
            [ 'key' => 'apparel',    'name' => $this->t( 'Apparel' ),     'slug' => 'apparel',     'sequence' => 2 ],
            [ 'key' => 'footwear',   'name' => $this->t( 'Footwear' ),    'slug' => 'footwear',    'sequence' => 3 ],
            [ 'key' => 'accessories','name' => $this->t( 'Accessories' ), 'slug' => 'accessories', 'sequence' => 4 ],
        ];

        foreach ( $parentCategories as $cat ) {
            $key  = $cat['key'];
            $slug = $cat['slug'];
            unset( $cat['key'] );

            $categoryIds[ $key ] = $this->firstOrInsertBySlug( 'sport_product_categories', $slug, array_merge( $cat, [
                'parent_id'  => null,
                'image'      => null,
                'status'     => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ] ) );
        }

        // Sub-categories
        $subCategories = [
            [ 'key' => 'rackets',   'parent' => 'equipment',   'name' => $this->t( 'Rackets' ),      'slug' => 'rackets',        'sequence' => 1 ],
            [ 'key' => 'shuttles',  'parent' => 'equipment',   'name' => $this->t( 'Shuttlecocks' ), 'slug' => 'shuttlecocks',   'sequence' => 2 ],
            [ 'key' => 'balls',     'parent' => 'equipment',   'name' => $this->t( 'Balls' ),        'slug' => 'balls',          'sequence' => 3 ],
            [ 'key' => 'shirts',    'parent' => 'apparel',     'name' => $this->t( 'Shirts' ),       'slug' => 'shirts',         'sequence' => 1 ],
            [ 'key' => 'shorts',    'parent' => 'apparel',     'name' => $this->t( 'Shorts' ),       'slug' => 'shorts',         'sequence' => 2 ],
            [ 'key' => 'shoes',     'parent' => 'footwear',    'name' => $this->t( 'Sports Shoes' ), 'slug' => 'sports-shoes',   'sequence' => 1 ],
            [ 'key' => 'bags',      'parent' => 'accessories', 'name' => $this->t( 'Sport Bags' ),   'slug' => 'sport-bags',     'sequence' => 1 ],
            [ 'key' => 'grips',     'parent' => 'accessories', 'name' => $this->t( 'Grips & Tape' ), 'slug' => 'grips-tape',     'sequence' => 2 ],
        ];

        foreach ( $subCategories as $sub ) {
            $key    = $sub['key'];
            $slug   = $sub['slug'];
            $parent = $sub['parent'];
            unset( $sub['key'], $sub['parent'] );

            $categoryIds[ $key ] = $this->firstOrInsertBySlug( 'sport_product_categories', $slug, array_merge( $sub, [
                'parent_id'  => $categoryIds[ $parent ],
                'image'      => null,
                'status'     => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ] ) );
        }

        $this->command->info( '✔ Sport Product Categories seeded (' . count( $categoryIds ) . ')' );

        // =====================================================================
        // 2. SPORT PRODUCTS + SPORT PIVOT
        // =====================================================================
        $productIds = [];

        $products = [
            [
                'key'         => 'yonex-astrox-88d',
                'category'    => 'rackets',
                'name'        => $this->t( 'Yonex Astrox 88D Pro' ),
                'slug'        => 'yonex-astrox-88d-pro',
                'description' => $this->t( 'High-performance badminton racket designed for doubles play.' ),
                'sequence'    => 1,
                'sports'      => [ 'badminton' ],
            ],
            [
                'key'         => 'li-ning-racket',
                'category'    => 'rackets',
                'name'        => $this->t( 'Li-Ning Turbo Charging 75' ),
                'slug'        => 'li-ning-turbo-charging-75',
                'description' => $this->t( 'Lightweight badminton racket with excellent control.' ),
                'sequence'    => 2,
                'sports'      => [ 'badminton' ],
            ],
            [
                'key'         => 'wilson-tennis-racket',
                'category'    => 'rackets',
                'name'        => $this->t( 'Wilson Clash 100 Pro' ),
                'slug'        => 'wilson-clash-100-pro',
                'description' => $this->t( 'Professional-grade tennis racket with flexible carbon frame.' ),
                'sequence'    => 3,
                'sports'      => [ 'tennis' ],
            ],
            [
                'key'         => 'rsl-shuttlecocks',
                'category'    => 'shuttles',
                'name'        => $this->t( 'RSL Classic Feather Shuttlecocks' ),
                'slug'        => 'rsl-classic-feather-shuttlecocks',
                'description' => $this->t( 'Tournament-grade natural feather shuttlecocks, speed 77.' ),
                'sequence'    => 1,
                'sports'      => [ 'badminton' ],
            ],
            [
                'key'         => 'yonex-mavis-350',
                'category'    => 'shuttles',
                'name'        => $this->t( 'Yonex Mavis 350 Nylon' ),
                'slug'        => 'yonex-mavis-350-nylon',
                'description' => $this->t( 'Durable nylon shuttlecocks for recreational play.' ),
                'sequence'    => 2,
                'sports'      => [ 'badminton' ],
            ],
            [
                'key'         => 'spalding-basketball',
                'category'    => 'balls',
                'name'        => $this->t( 'Spalding NBA Official Game Ball' ),
                'slug'        => 'spalding-nba-official-game-ball',
                'description' => $this->t( 'Official size 7 basketball with full-grain leather.' ),
                'sequence'    => 1,
                'sports'      => [ 'basketball' ],
            ],
            [
                'key'         => 'adidas-football',
                'category'    => 'balls',
                'name'        => $this->t( 'Adidas Tiro League Football' ),
                'slug'        => 'adidas-tiro-league-football',
                'description' => $this->t( 'Durable training football for all surfaces.' ),
                'sequence'    => 2,
                'sports'      => [ 'football' ],
            ],
            [
                'key'         => 'yonex-shirt',
                'category'    => 'shirts',
                'name'        => $this->t( 'Yonex Game Shirt' ),
                'slug'        => 'yonex-game-shirt',
                'description' => $this->t( 'Breathable dry-fit badminton shirt for competitive play.' ),
                'sequence'    => 1,
                'sports'      => [ 'badminton' ],
            ],
            [
                'key'         => 'nike-dri-fit-shorts',
                'category'    => 'shorts',
                'name'        => $this->t( 'Nike Dri-FIT Sport Shorts' ),
                'slug'        => 'nike-dri-fit-sport-shorts',
                'description' => $this->t( 'Lightweight multi-sport shorts with moisture-wicking fabric.' ),
                'sequence'    => 1,
                'sports'      => [ 'badminton', 'basketball', 'tennis' ],
            ],
            [
                'key'         => 'yonex-shoes',
                'category'    => 'shoes',
                'name'        => $this->t( 'Yonex Power Cushion 65Z3' ),
                'slug'        => 'yonex-power-cushion-65z3',
                'description' => $this->t( 'Premium badminton court shoes with Power Cushion technology.' ),
                'sequence'    => 1,
                'sports'      => [ 'badminton' ],
            ],
            [
                'key'         => 'yonex-bag',
                'category'    => 'bags',
                'name'        => $this->t( 'Yonex Team Racket Bag' ),
                'slug'        => 'yonex-team-racket-bag',
                'description' => $this->t( '6-racket capacity bag with thermal main compartment.' ),
                'sequence'    => 1,
                'sports'      => [ 'badminton', 'tennis' ],
            ],
            [
                'key'         => 'wilson-overgrip',
                'category'    => 'grips',
                'name'        => $this->t( 'Wilson Pro Overgrip' ),
                'slug'        => 'wilson-pro-overgrip',
                'description' => $this->t( 'Tacky overgrip for superior feel and sweat absorption.' ),
                'sequence'    => 1,
                'sports'      => [ 'badminton', 'tennis' ],
            ],
        ];

        foreach ( $products as $product ) {
            $key         = $product['key'];
            $slug        = $product['slug'];
            $categoryKey = $product['category'];
            $sportSlugs  = $product['sports'];
            unset( $product['key'], $product['category'], $product['sports'] );

            $productId = $this->firstOrInsertBySlug( 'sport_products', $slug, array_merge( $product, [
                'category_id' => $categoryIds[ $categoryKey ],
                'images'      => json_encode( [] ),
                'status'      => 10,
                'created_at'  => $now,
                'updated_at'  => $now,
            ] ) );

            $productIds[ $key ] = $productId;

            // Attach sports via pivot — skip if already linked
            foreach ( $sportSlugs as $sportSlug ) {
                if ( ! isset( $sports[ $sportSlug ] ) ) {
                    continue;
                }
                $pivotExists = DB::table('sport_product_sport')
                    ->where( 'sport_product_id', $productId )
                    ->where( 'sport_id', $sports[ $sportSlug ] )
                    ->exists();

                if ( ! $pivotExists ) {
                    DB::table('sport_product_sport')->insert( [
                        'sport_product_id' => $productId,
                        'sport_id'         => $sports[ $sportSlug ],
                    ] );
                }
            }
        }

        $this->command->info( '✔ Sport Products seeded (' . count( $productIds ) . ')' );

        // =====================================================================
        // 3. SPORT PRODUCT VARIANTS + STOCKS
        // =====================================================================
        $variantCount = 0;
        $stockCount   = 0;

        $variants = [
            // Yonex Astrox 88D
            'yonex-astrox-88d' => [
                [ 'name' => '3U G4', 'sku' => 'YX-A88D-3UG4', 'price' => 399.00, 'compare_price' => 449.00, 'specs' => [ 'weight' => '3U (85-89g)', 'grip' => 'G4' ], 'sequence' => 1, 'qty' => 20 ],
                [ 'name' => '4U G5', 'sku' => 'YX-A88D-4UG5', 'price' => 399.00, 'compare_price' => 449.00, 'specs' => [ 'weight' => '4U (80-84g)', 'grip' => 'G5' ], 'sequence' => 2, 'qty' => 15 ],
            ],
            // Li-Ning Racket
            'li-ning-racket' => [
                [ 'name' => '5U G5', 'sku' => 'LN-TC75-5UG5', 'price' => 259.00, 'compare_price' => null, 'specs' => [ 'weight' => '5U (75-79.9g)', 'grip' => 'G5' ], 'sequence' => 1, 'qty' => 10 ],
            ],
            // Wilson Tennis Racket
            'wilson-tennis-racket' => [
                [ 'name' => '4 1/4 Grip', 'sku' => 'WL-CL100-G2', 'price' => 549.00, 'compare_price' => 599.00, 'specs' => [ 'head_size' => '100 sq in', 'grip' => '4 1/4' ], 'sequence' => 1, 'qty' => 8 ],
                [ 'name' => '4 3/8 Grip', 'sku' => 'WL-CL100-G3', 'price' => 549.00, 'compare_price' => 599.00, 'specs' => [ 'head_size' => '100 sq in', 'grip' => '4 3/8' ], 'sequence' => 2, 'qty' => 8 ],
            ],
            // RSL Shuttlecocks
            'rsl-shuttlecocks' => [
                [ 'name' => 'Speed 76 (1 tube)', 'sku' => 'RSL-CL-76', 'price' => 55.00, 'compare_price' => null, 'specs' => [ 'speed' => '76', 'count' => 12 ], 'sequence' => 1, 'qty' => 50 ],
                [ 'name' => 'Speed 77 (1 tube)', 'sku' => 'RSL-CL-77', 'price' => 55.00, 'compare_price' => null, 'specs' => [ 'speed' => '77', 'count' => 12 ], 'sequence' => 2, 'qty' => 50 ],
                [ 'name' => 'Speed 78 (1 tube)', 'sku' => 'RSL-CL-78', 'price' => 55.00, 'compare_price' => null, 'specs' => [ 'speed' => '78', 'count' => 12 ], 'sequence' => 3, 'qty' => 30 ],
            ],
            // Yonex Mavis 350
            'yonex-mavis-350' => [
                [ 'name' => 'Medium Speed (1 tube)', 'sku' => 'YX-MV350-M', 'price' => 28.00, 'compare_price' => null, 'specs' => [ 'speed' => 'Medium', 'count' => 6 ], 'sequence' => 1, 'qty' => 100 ],
                [ 'name' => 'Fast Speed (1 tube)',   'sku' => 'YX-MV350-F', 'price' => 28.00, 'compare_price' => null, 'specs' => [ 'speed' => 'Fast',   'count' => 6 ], 'sequence' => 2, 'qty' => 80  ],
            ],
            // Spalding Basketball
            'spalding-basketball' => [
                [ 'name' => 'Size 7', 'sku' => 'SP-NBA-SZ7', 'price' => 189.00, 'compare_price' => 220.00, 'specs' => [ 'size' => '7', 'material' => 'Full-grain leather' ], 'sequence' => 1, 'qty' => 15 ],
            ],
            // Adidas Football
            'adidas-football' => [
                [ 'name' => 'Size 4', 'sku' => 'AD-TL-SZ4', 'price' => 79.00, 'compare_price' => null, 'specs' => [ 'size' => '4' ], 'sequence' => 1, 'qty' => 25 ],
                [ 'name' => 'Size 5', 'sku' => 'AD-TL-SZ5', 'price' => 85.00, 'compare_price' => null, 'specs' => [ 'size' => '5' ], 'sequence' => 2, 'qty' => 30 ],
            ],
            // Yonex Game Shirt
            'yonex-shirt' => [
                [ 'name' => 'S – White',  'sku' => 'YX-GS-S-WHT', 'price' => 89.00, 'compare_price' => null, 'specs' => [ 'size' => 'S', 'color' => 'White' ], 'sequence' => 1, 'qty' => 20 ],
                [ 'name' => 'M – White',  'sku' => 'YX-GS-M-WHT', 'price' => 89.00, 'compare_price' => null, 'specs' => [ 'size' => 'M', 'color' => 'White' ], 'sequence' => 2, 'qty' => 25 ],
                [ 'name' => 'L – White',  'sku' => 'YX-GS-L-WHT', 'price' => 89.00, 'compare_price' => null, 'specs' => [ 'size' => 'L', 'color' => 'White' ], 'sequence' => 3, 'qty' => 20 ],
                [ 'name' => 'M – Navy',   'sku' => 'YX-GS-M-NVY', 'price' => 89.00, 'compare_price' => null, 'specs' => [ 'size' => 'M', 'color' => 'Navy'  ], 'sequence' => 4, 'qty' => 15 ],
                [ 'name' => 'L – Navy',   'sku' => 'YX-GS-L-NVY', 'price' => 89.00, 'compare_price' => null, 'specs' => [ 'size' => 'L', 'color' => 'Navy'  ], 'sequence' => 5, 'qty' => 15 ],
            ],
            // Nike Shorts
            'nike-dri-fit-shorts' => [
                [ 'name' => 'S – Black', 'sku' => 'NK-DFS-S-BLK', 'price' => 119.00, 'compare_price' => 139.00, 'specs' => [ 'size' => 'S', 'color' => 'Black' ], 'sequence' => 1, 'qty' => 20 ],
                [ 'name' => 'M – Black', 'sku' => 'NK-DFS-M-BLK', 'price' => 119.00, 'compare_price' => 139.00, 'specs' => [ 'size' => 'M', 'color' => 'Black' ], 'sequence' => 2, 'qty' => 25 ],
                [ 'name' => 'L – Black', 'sku' => 'NK-DFS-L-BLK', 'price' => 119.00, 'compare_price' => 139.00, 'specs' => [ 'size' => 'L', 'color' => 'Black' ], 'sequence' => 3, 'qty' => 20 ],
            ],
            // Yonex Shoes
            'yonex-shoes' => [
                [ 'name' => 'UK 7', 'sku' => 'YX-PC65Z3-UK7', 'price' => 449.00, 'compare_price' => 499.00, 'specs' => [ 'uk_size' => '7' ], 'sequence' => 1, 'qty' => 8 ],
                [ 'name' => 'UK 8', 'sku' => 'YX-PC65Z3-UK8', 'price' => 449.00, 'compare_price' => 499.00, 'specs' => [ 'uk_size' => '8' ], 'sequence' => 2, 'qty' => 10 ],
                [ 'name' => 'UK 9', 'sku' => 'YX-PC65Z3-UK9', 'price' => 449.00, 'compare_price' => 499.00, 'specs' => [ 'uk_size' => '9' ], 'sequence' => 3, 'qty' => 8 ],
                [ 'name' => 'UK 10','sku' => 'YX-PC65Z3-UK10','price' => 449.00, 'compare_price' => 499.00, 'specs' => [ 'uk_size' => '10'], 'sequence' => 4, 'qty' => 5 ],
            ],
            // Yonex Bag
            'yonex-bag' => [
                [ 'name' => 'Black / Gold', 'sku' => 'YX-TRB-BLK', 'price' => 199.00, 'compare_price' => null, 'specs' => [ 'color' => 'Black / Gold', 'capacity' => '6 rackets' ], 'sequence' => 1, 'qty' => 12 ],
                [ 'name' => 'White / Blue', 'sku' => 'YX-TRB-WHT', 'price' => 199.00, 'compare_price' => null, 'specs' => [ 'color' => 'White / Blue', 'capacity' => '6 rackets' ], 'sequence' => 2, 'qty' => 10 ],
            ],
            // Wilson Overgrip
            'wilson-overgrip' => [
                [ 'name' => 'White (3-pack)', 'sku' => 'WL-PG-WHT-3', 'price' => 22.00, 'compare_price' => null, 'specs' => [ 'color' => 'White', 'pack' => 3 ], 'sequence' => 1, 'qty' => 60 ],
                [ 'name' => 'Black (3-pack)', 'sku' => 'WL-PG-BLK-3', 'price' => 22.00, 'compare_price' => null, 'specs' => [ 'color' => 'Black', 'pack' => 3 ], 'sequence' => 2, 'qty' => 60 ],
            ],
        ];

        foreach ( $variants as $productKey => $productVariants ) {
            $productId = $productIds[ $productKey ];

            foreach ( $productVariants as $variant ) {
                $qty   = $variant['qty'];
                $sku   = $variant['sku'];
                $specs = $variant['specs'];
                unset( $variant['qty'], $variant['specs'] );

                // Skip variant if SKU already exists
                $existing = DB::table('sport_product_variants')->where( 'sku', $sku )->first();

                if ( $existing ) {
                    $variantId = $existing->id;
                } else {
                    $variantId = DB::table('sport_product_variants')->insertGetId( array_merge( $variant, [
                        'sport_product_id' => $productId,
                        'specs'            => json_encode( $specs ),
                        'image'            => null,
                        'status'           => 10,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ] ) );
                    $variantCount++;
                }

                // Skip stock if already exists for this variant
                $stockExists = DB::table('sport_product_stocks')->where( 'variant_id', $variantId )->exists();

                if ( ! $stockExists ) {
                    DB::table('sport_product_stocks')->insert( [
                        'variant_id'        => $variantId,
                        'quantity'          => $qty,
                        'reserved_quantity' => 0,
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ] );
                    $stockCount++;
                }
            }
        }

        $this->command->info( '✔ Sport Product Variants seeded (' . $variantCount . ')' );
        $this->command->info( '✔ Sport Product Stocks seeded ('   . $stockCount   . ')' );

        $this->command->info( '' );
        $this->command->info( '🎉 SportProductSeeder complete.' );
    }
}
