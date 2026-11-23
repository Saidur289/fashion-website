<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\HomeSection;
use App\Models\Menu;
use App\Models\Product;
use App\Models\Testimonial;
use App\Models\TrustBadge;
use App\Models\User;
use Database\Seeders\StorefrontLayoutSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontLayoutTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        $u = User::factory()->create();
        $u->forceFill(['role' => $role, 'is_blocked' => false])->save();

        return $u;
    }

    public function test_homepage_renders_dynamic_slides_sections_banners_and_testimonials(): void
    {
        $this->seed(StorefrontLayoutSeeder::class);

        HeroSlide::first()->update(['title' => 'Dynamic Hero Headline Test']);
        Testimonial::first()->update(['name' => 'Dynamic Client Name']);
        Banner::where('slot', 'promo')->update(['title' => 'Dynamic Promo Headline']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Dynamic Hero Headline Test')
            ->assertSee('Dynamic Client Name')
            ->assertSee('Dynamic Promo Headline');

        // Hiding a section removes it from the homepage immediately (cache auto-cleared)
        HomeSection::where('key', 'testimonials')->firstOrFail()->update(['is_active' => false]);
        $this->get('/')->assertOk()->assertDontSee('Dynamic Client Name');

        // Reordering sections updates render order
        HomeSection::where('key', 'promo')->firstOrFail()->update(['sort_order' => 1]);
        HomeSection::where('key', 'hero')->firstOrFail()->update(['sort_order' => 2]);
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertLessThan(strpos($html, 'Dynamic Hero Headline Test'), strpos($html, 'Dynamic Promo Headline'));
    }

    public function test_dynamic_menus_and_trust_badges_render_on_storefront(): void
    {
        $this->seed(StorefrontLayoutSeeder::class);

        Menu::create([
            'location' => 'header',
            'label' => 'Winter Deals',
            'url' => '/shop?on_sale=1',
            'sort_order' => 99,
            'is_active' => true,
        ]);
        Menu::create([
            'location' => 'footer_quick',
            'label' => 'Gift Guide',
            'url' => '/blog',
            'sort_order' => 99,
            'is_active' => true,
        ]);

        $cat = Category::create(['name' => 'Serums', 'slug' => 'serums', 'group' => 'beauty']);
        $product = Product::create([
            'category_id' => $cat->id,
            'name' => 'Trust Badge Test Serum',
            'slug' => 'trust-badge-test-serum',
            'price' => 950,
            'image' => 'images/brand/safebuy-logo.webp',
            'is_active' => true,
        ]);

        TrustBadge::create([
            'location' => 'product',
            'icon' => 'shield',
            'title' => '7-Day Authenticity Money-Back',
            'sort_order' => 99,
            'is_active' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Winter Deals')
            ->assertSee('Gift Guide');

        $this->get(route('product.show', $product))
            ->assertOk()
            ->assertSee('7-Day Authenticity Money-Back');
    }

    public function test_admin_storefront_resources_enforce_roles(): void
    {
        $this->seed(StorefrontLayoutSeeder::class);

        $owner = $this->makeUser('owner');
        $manager = $this->makeUser('manager');
        $staff = $this->makeUser('staff');

        foreach ([
            '/admin/home-sections',
            '/admin/hero-slides',
            '/admin/banners',
            '/admin/testimonials',
            '/admin/menus',
            '/admin/trust-badges',
        ] as $url) {
            $this->actingAs($owner)->get($url)->assertOk();
            $this->actingAs($manager)->get($url)->assertOk();
            $this->actingAs($staff)->get($url)->assertForbidden();
        }
    }
}
