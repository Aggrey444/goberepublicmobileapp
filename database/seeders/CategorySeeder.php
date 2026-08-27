<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Burgers',
            'Pizza',
            'Drinks',
            'Sides',
            'Desserts',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['name' => $name],
                ['status' => Category::STATUS_ACTIVE]
            );
        }
    }
}
