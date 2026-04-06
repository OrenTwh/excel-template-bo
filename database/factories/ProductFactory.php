<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'parent_id' => null, // Adjust this if you want to create child products
            'brand_id' =>  Brand::inRandomOrder()->value('id'),
            'supplier_id' =>  Supplier::inRandomOrder()->value('id'),
            'unit_id' =>  Unit::inRandomOrder()->value('id'),
            'type' => $this->faker->numberBetween(1, 5),
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph,
            'product_code' => $this->faker->unique()->bothify('PROD-####'),
            'barcode_symbology' => $this->faker->ean13,
            'workmanship' => $this->faker->randomElement(['Handmade', 'Machine']),
            'location' => $this->faker->city,
            'address_1' => $this->faker->streetAddress,
            'address_2' => $this->faker->secondaryAddress,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'postcode' => $this->faker->postcode,
            'purchase_unit' => $this->faker->randomElement(['Box', 'Kilogram', 'Liter', 'Piece']),
            'sale_unit' => $this->faker->randomElement(['Piece', 'Kilogram', 'Liter', 'Set']),
            'quantity' => $this->faker->numberBetween(1, 100),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'promotional_price' => $this->faker->optional()->randomFloat(2, 5, 500),
            'promotion_start' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'promotion_end' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'promotion_on' => $this->faker->boolean,
            'cost' => $this->faker->randomFloat(2, 5, 500),
            'alert_quantity' => $this->faker->numberBetween(1, 10),
            'thumbnail' => $this->faker->imageUrl(640, 480, 'product'),
            'imei' => $this->faker->optional()->unique()->numerify('###############'),
            'serial_number' => $this->faker->optional()->unique()->regexify('[A-Z0-9]{10}'),
            'tax_method' => $this->faker->randomElement([1, 2]),
            'featured' => $this->faker->boolean,
            'status' => $this->faker->randomElement([10, 20]),
        ];
    }
}
