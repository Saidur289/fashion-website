@php
    $quick = isset($footerQuickMenu) && $footerQuickMenu->isNotEmpty()
        ? $footerQuickMenu->map(function ($m) {
            $href = str_starts_with($m->url, 'http') ? $m->url : url($m->url);
            $attr = match (true) {
                str_contains($m->url, '/wishlist') => 'data-wish-link',
                str_contains($m->url, '/compare') => 'data-compare-link',
                default => null,
            };

            return [$m->label, $href, $attr];
        })->all()
        : [
            ['How to Verify Product', route('verify-product'), null],
            ['SafeBuy Concierge', route('concierge'), null],
            ['Shipping & Returns', route('page.shipping-returns'), null],
            ['Privacy Policy', route('page.privacy'), null],
            ['Terms of Use', route('page.terms'), null],
            ['Wishlist', route('wishlist'), 'data-wish-link'],
            ['Compare', route('compare'), 'data-compare-link'],
            ['Order Tracking', route('order.track'), null],
        ];
    $bottomLinks = isset($footerBottomMenu) && $footerBottomMenu->isNotEmpty()
        ? $footerBottomMenu->map(fn ($m) => [$m->label, str_starts_with($m->url, 'http') ? $m->url : url($m->url)])->all()
        : [
            ['About Us', route('about')],
            ['Our Team', route('team')],
            ['FAQs', route('faq')],
            ['Blog', route('blog.index')],
            ['Contact', route('contact')],
        ];
    $socials = array_filter((array) config('safebuy.social', []));
    $hasContact = (bool) (config('safebuy.phone') || config('safebuy.email') || $socials);
    $hasLocation = (bool) (config('safebuy.address') || config('safebuy.hours'));
@endphp
<footer class="foot rv" data-reveal id="contact">
    <div @class(['wrap fgrid', 'fgrid-3' => ! $hasLocation])>
        <div class="st" style="--sd: .05s">
            <span class="flogo"><img src="{{ asset(config('safebuy.logo', 'images/brand/safebuy-logo.webp')) }}" alt="{{ config('safebuy.shop_name', 'SafeBuy') }}" width="150" height="48"></span>
            <p class="ftag">Safe to Buy, Smart to Trust.</p>
            @if ($hasContact)
                <h4>Contact Us</h4>
                @if (config('safebuy.phone'))
                    <a class="fcall" href="tel:{{ preg_replace('/\s+/', '', config('safebuy.phone')) }}">
                        <span><x-icon name="phone" size="18" /></span>
                        <span><small>Call us</small><b>{{ config('safebuy.phone') }}</b></span>
                    </a>
                @endif
                @if (config('safebuy.email'))
                    <div class="fline"><x-icon name="mail" size="16" stroke="1.7" /><a href="mailto:{{ config('safebuy.email') }}">{{ config('safebuy.email') }}</a></div>
                @endif
                @if ($socials)
                    <div class="fline" style="gap:10px;margin-top:8px;flex-wrap:wrap">
                        @foreach ($socials as $net => $url)
                            <a href="{{ $net === 'whatsapp' && ! str_starts_with($url, 'http') ? 'https://wa.me/'.preg_replace('/\D/', '', $url) : $url }}" target="_blank" rel="noopener" style="text-transform:capitalize;text-decoration:underline">{{ $net }}</a>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
        @if ($hasLocation)
            <div class="st" style="--sd: .15s">
                @if (config('safebuy.address'))
                    <h4>Store Location</h4>
                    <div class="fline"><x-icon name="pin" size="16" stroke="1.7" />{{ config('safebuy.address') }}</div>
                @endif
                @if (config('safebuy.hours'))
                    <h4 @class(['fh-gap' => (bool) config('safebuy.address')])>Open Hours</h4>
                    <div class="fline"><x-icon name="clock" size="16" stroke="1.7" />{{ config('safebuy.hours') }}</div>
                @endif
            </div>
        @endif
        <div class="st" style="--sd: .25s">
            <h4>Quick Links</h4>
            <ul>
                @foreach ($quick as [$label, $href, $attr])
                    <li><a href="{{ $href }}" @if ($attr) {{ $attr }} @endif>{{ $label }}</a></li>
                @endforeach
            </ul>
        </div>
        <div class="st" style="--sd: .35s">
            <h4>Newsletter</h4>
            <p style="margin: 0">Leave your email to get hot deals &amp; fake-product awareness alerts.</p>
            <form class="nl" action="{{ route('newsletter.store') }}" method="post" data-newsletter>
                @csrf
                <label class="vh" for="nl1">Email address</label>
                <input id="nl1" name="email" type="email" placeholder="Your email address…" required>
                <button type="submit" aria-label="Subscribe"><x-icon name="send" size="18" /></button>
            </form>
            <div class="pay-title">WE ACCEPT</div>
            <div class="pay">
                <span><img src="{{ asset('images/pay/cod.webp') }}" alt="Cash on Delivery" height="26"></span>
                <span><img src="{{ asset('images/pay/bkash.png') }}" alt="bKash" height="26"></span>
            </div>
        </div>
    </div>
    <div class="fbot">
        <div class="wrap fbot-in">
            <span>© {{ date('Y') }} SafeBuy. All rights reserved.</span>
            <nav aria-label="Footer">
                @foreach ($bottomLinks as [$label, $href])
                    <a href="{{ $href }}">{{ $label }}</a>
                @endforeach
            </nav>
            <span class="fcredit">Developed by <a href="https://web.facebook.com/profile.php?id=61592982546889" target="_blank" rel="noopener">QAMARIZ</a></span>
        </div>
    </div>
</footer>
