<nav class="mnav" aria-label="Quick">
    <a @class(['cur' => request()->routeIs('home')]) href="{{ route('home') }}"><x-icon name="home" size="21" stroke="1.7" />Home</a>
    <a href="{{ route('shop') }}" @class(['cur' => request()->routeIs('shop', 'category.show', 'brand.show', 'search', 'product.show')])><x-icon name="store" size="21" stroke="1.7" />Shop</a>
    <a href="{{ route('checkout') }}" @class(['cur' => request()->routeIs('cart', 'checkout')])><x-icon name="card" size="21" stroke="1.7" />Checkout<span class="badge" data-cart-count>0</span></a>
    <a href="{{ route('wishlist') }}" data-wish-link @class(['cur' => request()->routeIs('wishlist')])><x-icon name="heart" size="21" stroke="1.7" />Wishlist<span class="badge" data-wish-count>0</span></a>
    <a href="{{ auth()->check() ? route('account') : route('login') }}" @class(['cur' => request()->routeIs('account*', 'login', 'register')])><x-icon name="user" size="21" stroke="1.7" />Account</a>
</nav>
