<?php

namespace Tests\Feature;

use App\Filament\Resources\ProductResource;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\ShippingZone;
use App\Models\User;
use App\Services\Cart;
use App\Services\OrderManager;
use App\Services\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role, bool $blocked = false): User
    {
        $u = User::factory()->create();
        $u->forceFill(['role' => $role, 'is_blocked' => $blocked])->save();

        return $u;
    }

    private function makeProduct(int $stock = 10, int $price = 500): Product
    {
        $cat = Category::firstOrCreate(['slug' => 'serums'], ['name' => 'Serums', 'group' => 'beauty']);

        return Product::create([
            'category_id' => $cat->id,
            'name' => 'Test Glow Serum',
            'slug' => 'test-glow-serum-'.uniqid(),
            'sku' => 'SB-TEST-1',
            'price' => $price,
            'stock' => $stock,
            'image' => 'images/brand/safebuy-logo.webp',
            'is_active' => true,
        ]);
    }

    public function test_customer_and_blocked_admin_cannot_open_admin_panel(): void
    {
        $customer = $this->makeUser('customer');
        $this->actingAs($customer)->get('/admin')->assertForbidden();

        $blockedOwner = $this->makeUser('owner', blocked: true);
        $this->actingAs($blockedOwner)->get('/admin')->assertForbidden();
    }

    public function test_owner_can_open_all_admin_pages(): void
    {
        $owner = $this->makeUser('owner');

        $urls = [
            '/admin',
            '/admin/orders',
            '/admin/orders/create',
            '/admin/products',
            '/admin/products/create',
            '/admin/stock-manager',
            '/admin/categories',
            '/admin/brands',
            '/admin/attributes',
            '/admin/tags',
            '/admin/customers',
            '/admin/reviews',
            '/admin/coupons',
            '/admin/shipping-zones',
            '/admin/reports-page',
            '/admin/settings-page',
            '/admin/admin-users',
            '/admin/activity-logs',
        ];

        foreach ($urls as $url) {
            $this->actingAs($owner)->get($url)->assertOk();
        }
    }

    public function test_staff_and_manager_role_permissions_are_enforced(): void
    {
        $staff = $this->makeUser('staff');
        $manager = $this->makeUser('manager');
        $product = $this->makeProduct();

        // Staff: orders allowed, products/settings/users denied
        $this->actingAs($staff)->get('/admin')->assertOk();
        $this->actingAs($staff)->get('/admin/orders')->assertOk();
        $this->actingAs($staff)->get('/admin/products')->assertForbidden();
        $this->actingAs($staff)->get('/admin/stock-manager')->assertForbidden();
        $this->actingAs($staff)->get('/admin/settings-page')->assertForbidden();
        $this->actingAs($staff)->get('/admin/admin-users')->assertForbidden();
        $this->assertFalse($staff->can('delete', $product));

        // Manager: products & reports allowed, owner-only settings/admin-users denied
        $this->actingAs($manager)->get('/admin/products')->assertOk();
        $this->actingAs($manager)->get('/admin/stock-manager')->assertOk();
        $this->actingAs($manager)->get('/admin/reports-page')->assertOk();
        $this->actingAs($manager)->get('/admin/settings-page')->assertForbidden();
        $this->actingAs($manager)->get('/admin/admin-users')->assertForbidden();
        $this->assertTrue($manager->can('delete', $product));
    }

    public function test_product_soft_delete_restore_and_duplicate(): void
    {
        $owner = $this->makeUser('owner');
        $this->actingAs($owner);

        $product = $this->makeProduct(stock: 8);
        $copy = ProductResource::duplicateProduct($product);
        $this->assertNotEquals($product->id, $copy->id);
        $this->assertStringContainsString('(Copy)', $copy->name);
        $this->assertFalse($copy->is_active);

        $product->delete();
        $this->assertSoftDeleted('products', ['id' => $product->id]);

        $product->restore();
        $this->assertNotSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_manual_order_and_status_transitions_manage_stock_history_and_invoice(): void
    {
        $owner = $this->makeUser('owner');
        $customer = $this->makeUser('customer');
        $this->actingAs($owner);

        $product = $this->makeProduct(stock: 10, price: 600);
        $mgr = app(OrderManager::class);

        $order = $mgr->createManualOrder([
            'name' => 'Riaz Test',
            'phone' => '01711112222',
            'district' => 'Dhaka',
            'address' => 'House 10, Dhanmondi',
            'payment_method' => 'bkash',
            'payment_status' => 'pending_check',
            'bkash_sender' => '01711112222',
            'bkash_trx_id' => 'TRX998877',
            'shipping' => 70,
            'items' => [
                ['product_id' => $product->id, 'qty' => 3, 'price' => 600],
            ],
        ]);

        $this->assertEquals(7, $product->fresh()->stock);
        $this->assertEquals(1870, $order->total);

        // Verify bKash payment
        $mgr->updatePaymentStatus($order, 'paid');
        $this->assertEquals('paid', $order->fresh()->payment_status);

        // Cancel order -> stock restored back to 10
        $mgr->transitionStatus($order->fresh(), 'cancelled', 'Customer requested cancel');
        $this->assertEquals(10, $product->fresh()->stock);
        $this->assertEquals('cancelled', $order->fresh()->status);

        // Re-confirm -> stock deducted again to 7
        $mgr->transitionStatus($order->fresh(), 'confirmed', 'Customer confirmed again');
        $this->assertEquals(7, $product->fresh()->stock);

        $this->assertGreaterThanOrEqual(3, $order->histories()->count());
        $this->assertTrue(ActivityLog::where('subject_id', $order->id)->exists());

        // Invoice route: 200 for admin, 403 for customer
        $this->actingAs($owner)->get(route('admin.orders.invoice', $order))->assertOk()->assertSee($order->number);
        $this->actingAs($customer)->get(route('admin.orders.invoice', $order))->assertForbidden();
    }

    public function test_review_approval_updates_product_rating(): void
    {
        $customer = $this->makeUser('customer');
        $product = $this->makeProduct();

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 5,
            'body' => 'Original Korean serum, works great!',
            'is_approved' => false,
        ]);

        $product->refreshRating();
        $this->assertEquals(0, $product->fresh()->reviews_count);

        $review->update(['is_approved' => true, 'reply' => 'Thank you for shopping with SafeBuy!']);
        $product->refreshRating();

        $this->assertEquals(1, $product->fresh()->reviews_count);
        $this->assertEquals(5.0, $product->fresh()->rating);
    }

    public function test_settings_and_shipping_zones_apply_to_storefront(): void
    {
        Settings::setMany([
            'shop_name' => 'SafeBuy BD',
            'color_blue' => '#113388',
            'color_orange' => '#FF6600',
            'bkash_number' => '01812345678',
            'shipping_free_over' => 5000,
        ], 'general');

        $this->assertEquals('#113388', Settings::colors()['blue']);
        $this->assertEquals('01812345678', config('safebuy.bkash.number'));

        // Custom shipping zone overrides default charge for Sylhet
        ShippingZone::create([
            'name' => 'Sylhet Express',
            'districts' => ['Sylhet'],
            'charge' => 110,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $product = $this->makeProduct(stock: 5, price: 800);
        $cart = app(Cart::class);
        $cart->add($product, null, 1);
        $cart->setShipping(null, 'Sylhet');

        $summary = $cart->summary();
        $this->assertEquals(110, $summary['shipping']);
        $this->assertEquals(910, $summary['total']);
    }

    public function test_backup_command_runs_cleanly(): void
    {
        $this->artisan('safebuy:backup')->assertSuccessful();
    }
}
