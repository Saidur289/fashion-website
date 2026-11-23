<?php

namespace App\Providers;

use App\Models\Attribute;
use App\Models\Banner;
use App\Models\BlogCategory;
use App\Models\BlogComment;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ConciergeRequest;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\Faq;
use App\Models\HeroSlide;
use App\Models\HomeSection;
use App\Models\Menu;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\Review;
use App\Models\ShippingZone;
use App\Models\Tag;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\TrustBadge;
use App\Policies\CatalogItemPolicy;
use App\Services\Settings;
use App\Services\StorefrontLayout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Settings::applyToConfig();

        foreach ([
            Category::class, Brand::class, Attribute::class, Tag::class, Coupon::class,
            Review::class, ShippingZone::class, BlogCategory::class, BlogTag::class,
            BlogPost::class, BlogComment::class, Page::class, Faq::class, TeamMember::class,
            ConciergeRequest::class, ContactMessage::class, NewsletterSubscriber::class,
            HeroSlide::class, Banner::class, HomeSection::class, Testimonial::class,
            Menu::class, TrustBadge::class,
        ] as $model) {
            Gate::policy($model, CatalogItemPolicy::class);
        }

        // Separate limits per action (plain throttle:N,1 shares one counter across all routes).
        $by = fn (Request $r) => $r->user()?->id ?: $r->ip();
        foreach ([
            'cart' => 90, 'wishlist' => 60, 'checkout' => 10, 'track' => 30,
            'login' => 20, 'register' => 10, 'password' => 6, 'reviews' => 5,
            'contact' => 6, 'concierge' => 6, 'newsletter' => 10, 'comments' => 6,
        ] as $name => $perMinute) {
            RateLimiter::for($name, fn (Request $r) => Limit::perMinute($perMinute)->by($name.'|'.$by($r)));
        }

        // The header search's category dropdown and the mobile "All Departments" tab need categories on every page.
        View::composer('partials.header', function ($view) {
            $view->with('menuCategories', Category::orderBy('sort_order')
                ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
                ->get());
            $view->with('headerMenu', StorefrontLayout::menu('header'));
        });

        View::composer('partials.footer', function ($view) {
            $view->with('footerQuickMenu', StorefrontLayout::menu('footer_quick'));
            $view->with('footerBottomMenu', StorefrontLayout::menu('footer_bottom'));
        });

        View::composer('product._buybox', function ($view) {
            $view->with('trustBadges', StorefrontLayout::badges('product'));
        });

        View::composer('shop._main', function ($view) {
            $view->with('shopPromoBanner', StorefrontLayout::banner('shop_promo'));
        });
    }
}
