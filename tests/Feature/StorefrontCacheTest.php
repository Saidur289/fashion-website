<?php

namespace Tests\Feature;

use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The live site uses a persistent cache (database). The normal tests use the in-memory "array" cache,
 * which never serialises anything, so a bug that only shows up when cached data is read back
 * (every storefront page returning 500) slipped through. This test uses the database cache and
 * opens pages twice: once to fill the cache, once to read from it.
 */
class StorefrontCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_still_load_when_reading_from_a_real_cache(): void
    {
        config(['cache.default' => 'database']);
        $this->seed();

        $urls = ['/', '/shop', '/product/the-face-shop-rice-water-bright-foaming-cleanser', '/cart', '/blog', '/about', '/faq', '/contact'];

        foreach ([1, 2] as $round) {
            foreach ($urls as $url) {
                $this->get($url)->assertOk();
                // Each request in a real server starts with a fresh app; forget the per-request copy too.
                $this->app->forgetInstance('safebuy.storefront.layout');
            }
        }
    }
}
