<?php

namespace Tests\Feature;

use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_pages_load(): void
    {
        $this->seed(CatalogSeeder::class);

        foreach (['/', '/shop', '/category/sunscreen', '/brand/hoco', '/search?q=sun', '/compare', '/cart', '/order-tracking',
            '/product/the-face-shop-rice-water-bright-foaming-cleanser'] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
