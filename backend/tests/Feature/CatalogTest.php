<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_active_categories(): void
    {
        Category::factory()->count(3)->create();
        Category::factory()->create(['status' => Category::STATUS_INACTIVE]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data');
    }

    public function test_category_show_hides_inactive(): void
    {
        $inactive = Category::factory()->create(['status' => Category::STATUS_INACTIVE]);

        $this->getJson("/api/v1/categories/{$inactive->id}")->assertStatus(404);
    }

    public function test_lists_active_products_paginated(): void
    {
        Product::factory()->count(25)->create([
            'category_id' => Category::factory(),
        ]);
        Product::factory()->create(['status' => Product::STATUS_INACTIVE]);

        $response = $this->getJson('/api/v1/products?per_page=20');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['items', 'pagination']])
            ->assertJsonCount(20, 'data.items');
    }

    public function test_filters_products_by_category(): void
    {
        $category = Category::factory()->create();
        $other = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);
        Product::factory()->count(2)->create(['category_id' => $other->id]);

        $response = $this->getJson('/api/v1/products?category_id=' . $category->id);

        $response->assertOk()
            ->assertJsonCount(3, 'data.items');
    }

    public function test_searches_products(): void
    {
        Product::factory()->create(['name' => 'Nike Running Shoes']);
        Product::factory()->create(['name' => 'Adidas Sandals']);

        $this->getJson('/api/v1/products?search=Nike')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.name', 'Nike Running Shoes');

        $this->getJson('/api/v1/products?q=Sandals')
            ->assertOk()
            ->assertJsonCount(1, 'data.items');
    }

    public function test_shows_a_product(): void
    {
        $product = Product::factory()->create();

        $this->getJson("/api/v1/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.status', Product::STATUS_ACTIVE);
    }
}
