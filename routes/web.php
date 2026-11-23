<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Catalogue (step 1)
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/category/{category}', [ShopController::class, 'category'])->name('category.show');
Route::get('/brand/{brand}', [ShopController::class, 'brand'])->name('brand.show');
Route::get('/search', [ShopController::class, 'search'])->name('search');
Route::get('/search/suggest', [SearchController::class, 'suggest'])->name('search.suggest');
Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');
Route::get('/product/{product}/quick', [ProductController::class, 'quick'])->name('product.quick');
Route::get('/compare', [CompareController::class, 'index'])->name('compare');

// Cart, checkout and orders (step 2)
Route::get('/cart', [CartController::class, 'show'])->name('cart');
Route::get('/cart/data', [CartController::class, 'data'])->name('cart.data');
Route::middleware('throttle:cart')->group(function () {
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/coupon', [CartController::class, 'coupon'])->name('cart.coupon');
    Route::post('/cart/shipping', [CartController::class, 'shipping'])->name('cart.shipping');
});
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:checkout')->name('checkout.store');
Route::get('/order/{order}/thank-you', [OrderController::class, 'thanks'])->name('order.thanks');
Route::get('/order-tracking', [OrderController::class, 'track'])->middleware('throttle:track')->name('order.track');

// Customer accounts (step 3)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::get('/register', [AuthController::class, 'show'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.store');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register')->name('register.store');
    Route::get('/forgot-password', [AuthController::class, 'forgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:password')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset'])->middleware('throttle:password')->name('password.update');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('account')->name('account')->group(function () {
    Route::get('/', [AccountController::class, 'dashboard']);
    Route::get('/orders', [AccountController::class, 'orders'])->name('.orders');
    Route::get('/orders/{order}', [AccountController::class, 'order'])->name('.order');
    Route::get('/addresses', [AccountController::class, 'addresses'])->name('.addresses');
    Route::post('/addresses', [AccountController::class, 'saveAddress'])->name('.addresses.store');
    Route::put('/addresses/{address}', [AccountController::class, 'saveAddress'])->name('.addresses.update');
    Route::delete('/addresses/{address}', [AccountController::class, 'deleteAddress'])->name('.addresses.delete');
    Route::get('/details', [AccountController::class, 'details'])->name('.details');
    Route::put('/details', [AccountController::class, 'saveDetails'])->name('.details.update');
});

Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
Route::middleware(['auth', 'throttle:wishlist'])->group(function () {
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/wishlist/sync', [WishlistController::class, 'sync'])->name('wishlist.sync');
});
Route::post('/product/{product}/reviews', [ReviewController::class, 'store'])->middleware(['auth', 'throttle:reviews'])->name('reviews.store');

// Blog & content pages (step 5)
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post}', [BlogController::class, 'show'])->name('blog.show');
Route::post('/blog/{post}/comments', [BlogController::class, 'storeComment'])->middleware('throttle:comments')->name('blog.comments.store');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/our-team', [PageController::class, 'team'])->name('team');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/verify-product', [PageController::class, 'verifyProduct'])->name('verify-product');

Route::get('/terms', fn () => app(PageController::class)->policy('terms'))->name('page.terms');
Route::get('/privacy-policy', fn () => app(PageController::class)->policy('privacy-policy'))->name('page.privacy');
Route::get('/shipping-returns', fn () => app(PageController::class)->policy('shipping-returns'))->name('page.shipping-returns');

Route::get('/contact', [InboxController::class, 'contact'])->name('contact');
Route::post('/contact', [InboxController::class, 'storeContact'])->middleware('throttle:contact')->name('contact.store');

Route::get('/concierge', [InboxController::class, 'concierge'])->name('concierge');
Route::post('/concierge', [InboxController::class, 'storeConcierge'])->middleware('throttle:concierge')->name('concierge.store');

Route::get('/newsletter', [InboxController::class, 'newsletter'])->name('newsletter');
Route::post('/newsletter', [InboxController::class, 'storeNewsletter'])->middleware('throttle:newsletter')->name('newsletter.store');

// Admin printable invoice & locale switcher (step 4)
Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/orders/{order}/invoice', [InvoiceController::class, 'show'])->name('admin.orders.invoice');
    Route::get('/locale/toggle', function () {
        abort_unless(auth()->user()?->isAdmin(), 403);
        session(['admin_locale' => session('admin_locale', 'en') === 'en' ? 'bn' : 'en']);

        return back();
    })->name('admin.locale.toggle');
});
