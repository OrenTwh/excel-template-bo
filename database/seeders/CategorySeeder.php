<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $categories = [
            [
                'title' => 'Electrical Component',
                'description' => 'Electrical Component Info',
                'status' => 10,
            ],
            [
                'title' => 'Kitchen Compliance',
                'description' => 'Kitchen Compliance Info',
                'status' => 10,
            ],
        ];

        foreach ($categories as $category) {

            $categoryAttribute = [
                'title' => $category['title'],
                'description' => $category['description'],
                'status' => $category['status'],
            ];

            $createCategory = Category::create($categoryAttribute);
        }
    }
}
