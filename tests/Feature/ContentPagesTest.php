<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogComment;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentPagesTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        $u = User::factory()->create();
        $u->forceFill(['role' => $role, 'is_blocked' => false])->save();

        return $u;
    }

    public function test_all_step5_storefront_pages_load_and_404_renders(): void
    {
        $this->seed(ContentSeeder::class);
        $post = BlogPost::firstOrFail();

        foreach ([
            '/blog',
            '/blog?view=list',
            '/blog/'.$post->slug,
            '/about',
            '/our-team',
            '/faq',
            '/faq?category=delivery',
            '/contact',
            '/terms',
            '/privacy-policy',
            '/shipping-returns',
            '/concierge',
            '/verify-product',
            '/newsletter',
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get('/this-page-does-not-exist-xyz')->assertNotFound()->assertSee('404');
    }

    public function test_blog_filters_and_comment_moderation(): void
    {
        $this->seed(ContentSeeder::class);
        $cat = BlogCategory::firstOrFail();
        $post = BlogPost::where('blog_category_id', $cat->id)->firstOrFail();

        $this->get('/blog?category='.$cat->slug)->assertOk()->assertSee($post->title);
        $this->get('/blog?q=Korean')->assertOk();

        // Guest posts a comment -> stored unapproved
        $this->post(route('blog.comments.store', $post), [
            'name' => 'Amina Akter',
            'email' => 'amina@example.com',
            'body' => 'Very helpful Korean skincare authenticity tips!',
        ])->assertRedirect(route('blog.show', $post).'#comments');

        $comment = BlogComment::where('blog_post_id', $post->id)->firstOrFail();
        $this->assertFalse($comment->is_approved);

        // Not visible until approved
        $this->get(route('blog.show', $post))->assertDontSee('Very helpful Korean skincare authenticity tips!');

        // Once approved with reply -> visible on post
        $comment->update(['is_approved' => true, 'reply' => 'Glad it helped!']);
        $this->get(route('blog.show', $post))
            ->assertSee('Very helpful Korean skincare authenticity tips!')
            ->assertSee('Glad it helped!');
    }

    public function test_contact_concierge_and_newsletter_forms_save_to_inbox(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Karim',
            'phone' => '01712345678',
            'email' => 'karim@example.com',
            'subject' => 'Bulk inquiry',
            'message' => 'Do you deliver original COSRX products to Rajshahi?',
        ])->assertRedirect(route('contact'));

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Karim',
            'subject' => 'Bulk inquiry',
            'is_read' => false,
        ]);

        $this->post(route('concierge.store'), [
            'name' => 'Farhana',
            'phone' => '01812345678',
            'product_name' => 'Anua Heartleaf 77% Soothing Toner 250ml',
            'brand' => 'Anua',
            'budget' => 'Tk 2200',
        ])->assertRedirect(route('concierge'));

        $this->assertDatabaseHas('concierge_requests', [
            'name' => 'Farhana',
            'product_name' => 'Anua Heartleaf 77% Soothing Toner 250ml',
            'status' => 'new',
        ]);

        // JSON newsletter subscribe (footer AJAX) + duplicate handling
        $this->postJson(route('newsletter.store'), ['email' => 'SHOPPER@Example.com'])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'shopper@example.com',
            'is_active' => true,
        ]);

        $this->postJson(route('newsletter.store'), ['email' => 'shopper@example.com'])
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_verify_product_lookup_by_sku(): void
    {
        $cat = Category::create(['name' => 'Sunscreen', 'slug' => 'sunscreen', 'group' => 'beauty']);
        Product::create([
            'category_id' => $cat->id,
            'name' => 'Beauty of Joseon Relief Sun',
            'slug' => 'boj-relief-sun',
            'sku' => 'SB-BOJ-50ML',
            'price' => 1450,
            'origin' => 'South Korea',
            'image' => 'images/brand/safebuy-logo.webp',
            'is_active' => true,
        ]);

        $this->get('/verify-product?q=SB-BOJ-50ML')
            ->assertOk()
            ->assertSee('Beauty of Joseon Relief Sun')
            ->assertSee('Official SafeBuy Item');
    }

    public function test_admin_content_and_inbox_resources_enforce_roles(): void
    {
        $this->seed(ContentSeeder::class);

        $owner = $this->makeUser('owner');
        $manager = $this->makeUser('manager');
        $staff = $this->makeUser('staff');

        $adminUrls = [
            '/admin/blog-posts',
            '/admin/blog-categories',
            '/admin/pages',
            '/admin/faqs',
            '/admin/team-members',
            '/admin/concierge-requests',
            '/admin/contact-messages',
            '/admin/newsletter-subscribers',
            '/admin/blog-comments',
        ];

        foreach ($adminUrls as $url) {
            $this->actingAs($owner)->get($url)->assertOk();
            $this->actingAs($manager)->get($url)->assertOk();
            $this->actingAs($staff)->get($url)->assertForbidden();
        }
    }
}
