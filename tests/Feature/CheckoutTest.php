<?php

namespace Tests\Feature;

use App\Mail\OrderPlaced;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogSeeder::class);
        config([
            'safebuy.shipping.inside_dhaka' => 70,
            'safebuy.shipping.outside_dhaka' => 130,
            'safebuy.shipping.free_over' => 3000,
            'safebuy.shipping.inside_districts' => ['Dhaka'],
            'safebuy.bkash.number' => '01700000000',
            'safebuy.notify_email' => 'owner@example.test',
        ]);
    }

    private function simple(): Product
    {
        return Product::where('slug', 'christian-dean-secret-tone-up-sun-cream-spf50-70ml')->firstOrFail(); // Tk 420, stock 199
    }

    private function variable(): Product
    {
        return Product::where('slug', 'the-face-shop-rice-water-bright-foaming-cleanser')->with('variants')->firstOrFail();
    }

    private function details(array $extra = []): array
    {
        return array_merge([
            'name' => 'Test Customer',
            'phone' => '017 1234-5678',
            'email' => 'buyer@example.test',
            'district' => 'Jhenaidah',
            'area' => 'Sadar',
            'address' => 'House 1, Road 2',
            'payment_method' => 'cod',
            'terms' => '1',
        ], $extra);
    }

    public function test_add_simple_product_and_cart_totals(): void
    {
        $p = $this->simple();
        $this->postJson('/cart/add', ['product_id' => $p->id, 'qty' => 2])
            ->assertOk()
            ->assertJsonPath('cart.count', 2)
            ->assertJsonPath('cart.subtotal', 840)
            ->assertJsonPath('cart.shipping', 70)
            ->assertJsonPath('cart.total', 910);
    }

    public function test_variable_product_needs_an_option(): void
    {
        $p = $this->variable();
        $this->postJson('/cart/add', ['product_id' => $p->id])->assertStatus(422);

        $v150 = $p->variants->firstWhere('price', 999);
        $this->postJson('/cart/add', ['product_id' => $p->id, 'variant_id' => $v150->id])
            ->assertOk()
            ->assertJsonPath('cart.items.0.variant', '150ml')
            ->assertJsonPath('cart.items.0.price', 999);
    }

    public function test_cannot_add_more_than_stock(): void
    {
        $p = $this->simple();
        $p->update(['stock' => 3]);
        $this->postJson('/cart/add', ['product_id' => $p->id, 'qty' => 2])->assertOk();
        $this->postJson('/cart/add', ['product_id' => $p->id, 'qty' => 2])->assertStatus(422)
            ->assertJsonPath('cart.count', 2);
    }

    public function test_district_sets_delivery_and_free_delivery_threshold(): void
    {
        $p = Product::where('slug', 'like', 'joyroom%')->firstOrFail(); // Tk 3,550
        $this->postJson('/cart/add', ['product_id' => $this->simple()->id]);
        $this->postJson('/cart/shipping', ['district' => 'Khulna'])->assertJsonPath('cart.shipping', 130);
        $this->postJson('/cart/shipping', ['district' => 'Dhaka'])->assertJsonPath('cart.shipping', 70);
        $this->postJson('/cart/add', ['product_id' => $p->id])->assertJsonPath('cart.shipping', 0);
    }

    public function test_coupon_rules(): void
    {
        Coupon::create(['code' => 'SAVE10', 'type' => 'percent', 'value' => 10, 'min_order' => 500]);
        $this->postJson('/cart/add', ['product_id' => $this->simple()->id]); // 420
        $this->postJson('/cart/coupon', ['code' => 'save10'])->assertStatus(422); // below 500
        $this->postJson('/cart/coupon', ['code' => 'nope'])->assertStatus(422);
        $this->postJson('/cart/add', ['product_id' => $this->simple()->id]); // 840
        $this->postJson('/cart/coupon', ['code' => 'save10'])->assertOk()->assertJsonPath('cart.discount', 84);
    }

    public function test_cod_order_is_placed_stock_lowered_and_cart_cleared(): void
    {
        Mail::fake();
        $p = $this->simple();
        $v = $this->variable()->variants->firstWhere('price', 735); // 100ml, stock 378
        $this->postJson('/cart/add', ['product_id' => $p->id, 'qty' => 2]);
        $this->postJson('/cart/add', ['product_id' => $v->product_id, 'variant_id' => $v->id]);

        $res = $this->post('/checkout', $this->details());

        $order = Order::with('items')->firstOrFail();
        $res->assertRedirect(route('order.thanks', $order));
        $this->assertMatchesRegularExpression('/^SB-\d{6}-\d{4}$/', $order->number);
        $this->assertSame('01712345678', $order->phone);
        $this->assertSame(840 + 735, $order->subtotal);
        $this->assertSame(130, $order->shipping); // Jhenaidah = outside Dhaka
        $this->assertSame(840 + 735 + 130, $order->total);
        $this->assertCount(2, $order->items);
        $this->assertSame('100ml', $order->items->firstWhere('product_variant_id', $v->id)->variant);
        $this->assertSame(197, $p->fresh()->stock);
        $this->assertSame(377, $v->fresh()->stock);
        $this->assertSame('pending', $order->status);
        $this->assertSame(1, $order->histories()->count());

        $this->getJson('/cart/data')->assertJsonPath('count', 0);
        $this->get(route('order.thanks', $order))->assertOk()->assertSee($order->number);
        Mail::assertSent(OrderPlaced::class, 2); // customer + shop owner
    }

    public function test_checkout_validates_phone_and_bkash(): void
    {
        $this->postJson('/cart/add', ['product_id' => $this->simple()->id]);

        $this->post('/checkout', $this->details(['phone' => '12345']))->assertSessionHasErrors('phone');
        $this->post('/checkout', $this->details(['payment_method' => 'bkash']))->assertSessionHasErrors(['bkash_sender', 'bkash_trx_id']);
        $this->post('/checkout', $this->details(['terms' => null]))->assertSessionHasErrors('terms');
        $this->assertSame(0, Order::count());

        $this->post('/checkout', $this->details(['payment_method' => 'bkash', 'bkash_sender' => '01811111111', 'bkash_trx_id' => '9kd4ab12xy']))
            ->assertSessionHasNoErrors();
        $order = Order::firstOrFail();
        $this->assertSame('pending_check', $order->payment_status);
        $this->assertSame('9KD4AB12XY', $order->bkash_trx_id);
    }

    public function test_out_of_stock_blocks_the_order(): void
    {
        $p = $this->simple();
        $this->postJson('/cart/add', ['product_id' => $p->id, 'qty' => 2]);
        $p->update(['stock' => 1]); // someone else bought it meanwhile

        $this->post('/checkout', $this->details());
        $this->assertSame(0, Order::count());
        $this->getJson('/cart/data')->assertJsonPath('items.0.qty', 1); // lowered to what is left
    }

    public function test_tracking_needs_the_right_phone(): void
    {
        $this->postJson('/cart/add', ['product_id' => $this->simple()->id]);
        $this->post('/checkout', $this->details());
        $order = Order::firstOrFail();

        $this->get('/order-tracking?number='.$order->number.'&phone=01712345678')->assertOk()->assertSee('Order placed');
        $this->get('/order-tracking?number='.$order->number.'&phone=01999999999')->assertOk()->assertSee('could not find');

        // Another browser cannot open the thank-you page.
        $this->flushSession();
        $this->get(route('order.thanks', $order))->assertRedirect();
    }

    public function test_pages_load(): void
    {
        $this->get('/cart')->assertOk()->assertSee('Your cart is empty');
        $this->get('/checkout')->assertRedirect('/cart');
        $this->postJson('/cart/add', ['product_id' => $this->simple()->id]);
        $this->get('/cart')->assertOk()->assertSee('Proceed to checkout');
        $this->get('/checkout')->assertOk()->assertSee('Place order')->assertSee('01700000000');
    }
}
