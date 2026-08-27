<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $burgers = Category::where('name', 'Burgers')->value('id');
        $drinks = Category::where('name', 'Drinks')->value('id');
        $sides = Category::where('name', 'Sides')->value('id');

        $products = [
            ['category_id' => $burgers, 'name' => 'Classic GOBE Burger', 'description' => 'Juicy beef patty with fresh lettuce, tomato and our signature sauce.', 'price' => 3500],
            ['category_id' => $burgers, 'name' => 'Double Cheese Burger', 'description' => 'Two beef patties double cheese with caramelised onions.', 'price' => 5500],
            ['category_id' => $burgers, 'name' => 'Chicken Burger', 'description' => 'Crispy chicken fillet with coleslaw in a soft bun.', 'price' => 4000],
            ['category_id' => $drinks, 'name' => 'Coca-Cola 50cl', 'description' => 'Ice cold Coca-Cola.', 'price' => 800],
            ['category_id' => $drinks, 'name' => 'Fresh Orange Juice', 'description' => 'Freshly squeezed orange juice.', 'price' => 1500],
            ['category_id' => $sides, 'name' => 'French Fries', 'description' => 'Golden crispy french fries.', 'price' => 1200],
            ['category_id' => $sides, 'name' => 'Onion Rings', 'description' => 'Crispy battered onion rings.', 'price' => 1400],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['name' => $product['name']],
                [
                    'category_id' => $product['category_id'],
                    'description' => $product['description'],
                    'price' => $product['price'],
                    'status' => Product::STATUS_ACTIVE,
                ]
            );
        }
    }
}
