<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogSeeder::class);
    }

    private function user(array $extra = []): User
    {
        return User::create(array_merge(['name' => 'Rahim Uddin', 'phone' => '01712345678', 'email' => 'rahim@example.test', 'password' => 'secret-pass'], $extra));
    }

    private function product(): Product
    {
        return Product::where('slug', 'christian-dean-secret-tone-up-sun-cream-spf50-70ml')->firstOrFail();
    }

    public function test_register_with_mobile_and_login_with_mobile_or_email(): void
    {
        $this->post('/register', ['name' => 'Karim', 'phone' => '+880 1811-111111', 'password' => 'long-password', 'password_confirmation' => 'long-password'])
            ->assertRedirect('/account');
        $this->assertAuthenticated();
        $this->assertSame('01811111111', User::first()->phone);
        $this->assertNull(User::first()->email);

        $this->post('/logout');
        $this->assertGuest();

        $this->post('/login', ['login' => '01811-111111', 'password' => 'long-password'])->assertRedirect('/account');
        $this->assertAuthenticated();
    }

    public function test_register_rejects_duplicate_phone_and_bad_input(): void
    {
        $this->user();
        $this->post('/register', ['name' => 'X', 'phone' => '01712345678', 'password' => 'long-password', 'password_confirmation' => 'long-password'])
            ->assertSessionHasErrorsIn('register', 'phone');
        $this->post('/register', ['name' => 'X', 'phone' => '0171', 'password' => 'short', 'password_confirmation' => 'nope'])
            ->assertSessionHasErrorsIn('register', ['phone', 'password']);
        $this->assertSame(1, User::count());
    }

    public function test_wrong_password_and_blocked_account(): void
    {
        $u = $this->user();
        $this->post('/login', ['login' => 'rahim@example.test', 'password' => 'wrong'])->assertSessionHasErrors('login');
        $this->assertGuest();

        $u->forceFill(['is_blocked' => true])->save();
        $this->post('/login', ['login' => 'rahim@example.test', 'password' => 'secret-pass'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_account_pages_need_login_and_show_only_own_orders(): void
    {
        $this->get('/account')->assertRedirect('/login');

        $me = $this->user();
        $other = $this->user(['phone' => '01999999999', 'email' => 'o@example.test']);
        $mine = Order::create(['number' => 'SB-1', 'user_id' => $me->id, 'name' => 'R', 'phone' => '01712345678', 'district' => 'Dhaka', 'address' => 'A', 'subtotal' => 100, 'shipping' => 70, 'shipping_method' => 'inside_dhaka', 'total' => 170, 'payment_method' => 'cod']);
        $theirs = Order::create(['number' => 'SB-2', 'user_id' => $other->id, 'name' => 'O', 'phone' => '01999999999', 'district' => 'Dhaka', 'address' => 'B', 'subtotal' => 100, 'shipping' => 70, 'shipping_method' => 'inside_dhaka', 'total' => 170, 'payment_method' => 'cod']);

        $this->actingAs($me);
        $this->get('/account')->assertOk()->assertSee('SB-1')->assertDontSee('SB-2');
        $this->get('/account/orders/SB-1')->assertOk();
        $this->get('/account/orders/SB-2')->assertNotFound();
    }

    public function test_addresses_and_checkout_prefill(): void
    {
        $this->actingAs($this->user());
        $this->post('/account/addresses', ['label' => 'Home', 'name' => 'Rahim', 'phone' => '01712345678', 'district' => 'Khulna', 'address' => 'Road 5'])
            ->assertRedirect('/account/addresses');
        $this->assertTrue(auth()->user()->addresses()->first()->is_default);

        $this->postJson('/cart/add', ['product_id' => $this->product()->id]);
        $this->get('/checkout')->assertOk()->assertSee('Road 5')->assertSee('Tk 130'); // Khulna = outside Dhaka
    }

    public function test_change_password_needs_current_password(): void
    {
        $this->actingAs($this->user());
        $this->put('/account/details', ['name' => 'Rahim', 'phone' => '01712345678', 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1'])
            ->assertSessionHasErrors('current_password');
        $this->put('/account/details', ['name' => 'Rahim', 'phone' => '01712345678', 'current_password' => 'secret-pass', 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1'])
            ->assertSessionHasNoErrors();
        $this->post('/logout');
        $this->post('/login', ['login' => '01712345678', 'password' => 'new-password-1'])->assertRedirect('/account');
    }

    public function test_cart_follows_the_customer_to_another_device(): void
    {
        $u = $this->user();
        $this->actingAs($u);
        $this->postJson('/cart/add', ['product_id' => $this->product()->id, 'qty' => 2]);
        $this->assertSame(2, $u->fresh()->saved_cart[(string) $this->product()->id]['qty']);

        // New device: empty session, then log in.
        $this->post('/logout');
        $this->flushSession();
        $this->post('/login', ['login' => '01712345678', 'password' => 'secret-pass']);
        $this->getJson('/cart/data')->assertJsonPath('count', 2);
    }

    public function test_wishlist_toggle_sync_and_page(): void
    {
        $p = $this->product();
        $this->postJson('/wishlist/toggle', ['product_id' => $p->id])->assertUnauthorized();
        $this->get('/wishlist?ids='.$p->id)->assertOk()->assertSee($p->name);

        $this->actingAs($this->user());
        $this->postJson('/wishlist/toggle', ['product_id' => $p->id])->assertJsonPath('on', true);
        $this->postJson('/wishlist/sync', ['ids' => [$p->id, 2, 999]])->assertJsonCount(2, 'ids');
        $this->get('/wishlist')->assertOk()->assertSee($p->name);
        $this->postJson('/wishlist/toggle', ['product_id' => $p->id])->assertJsonPath('on', false);
    }

    public function test_only_buyers_with_a_delivered_order_can_review(): void
    {
        $u = $this->user();
        $p = $this->product();
        $this->actingAs($u);

        $this->post("/product/{$p->slug}/reviews", ['rating' => 5, 'body' => 'Really good sunscreen.'])->assertSessionHas('review_error');
        $this->assertSame(0, Review::count());

        $order = Order::create(['number' => 'SB-9', 'user_id' => $u->id, 'name' => 'R', 'phone' => '01712345678', 'district' => 'Dhaka', 'address' => 'A', 'subtotal' => 420, 'shipping' => 70, 'shipping_method' => 'inside_dhaka', 'total' => 490, 'payment_method' => 'cod', 'status' => 'delivered']);
        $order->items()->create(['product_id' => $p->id, 'name' => $p->name, 'price' => 420, 'qty' => 1, 'total' => 420]);

        $photo = UploadedFile::fake()->image('skin.jpg', 1600, 1200);
        $this->post("/product/{$p->slug}/reviews", ['rating' => 4, 'body' => 'Light and no white cast.', 'photos' => [$photo]])
            ->assertSessionHas('review_status');
        $review = Review::with('images')->firstOrFail();
        $this->assertFalse($review->is_approved); // waits for the shop owner
        $this->assertCount(1, $review->images);
        $this->assertFileExists(public_path($review->images[0]->path));
        [$w] = getimagesize(public_path($review->images[0]->path));
        $this->assertSame(1200, $w); // resized
        File::delete(public_path($review->images[0]->path));

        // Not shown until approved; then rating updates.
        $this->get("/product/{$p->slug}")->assertDontSee('Light and no white cast.');
        $review->update(['is_approved' => true]);
        $p->refreshRating();
        $this->assertSame(4.0, $p->fresh()->rating);
        $this->get("/product/{$p->slug}")->assertSee('Light and no white cast.')->assertSee('Rahim U.');

        // One review per product.
        $this->post("/product/{$p->slug}/reviews", ['rating' => 1, 'body' => 'Second review attempt.'])->assertSessionHas('review_error');
    }

    public function test_password_reset_link(): void
    {
        Notification::fake();
        $u = $this->user();
        $this->post('/forgot-password', ['email' => 'rahim@example.test'])->assertSessionHas('status');
        Notification::assertSentTo($u, ResetPassword::class);
        // Same answer for an unknown email.
        $this->post('/forgot-password', ['email' => 'nobody@example.test'])->assertSessionHas('status');
    }

    public function test_orders_placed_while_logged_in_belong_to_the_account(): void
    {
        $u = $this->user();
        $this->actingAs($u);
        $this->postJson('/cart/add', ['product_id' => $this->product()->id]);
        $this->post('/checkout', ['name' => 'Rahim', 'phone' => '01712345678', 'district' => 'Dhaka', 'address' => 'Road 1', 'payment_method' => 'cod', 'terms' => '1'])
            ->assertSessionHasNoErrors();
        $this->assertSame($u->id, Order::first()->user_id);
        $this->assertNull($u->fresh()->saved_cart); // cart emptied everywhere
    }
}
