/* SafeBuy homepage interactions — no dependencies. */
(function () {
  'use strict';

  var $ = function (sel, root) { return (root || document).querySelector(sel); };
  var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  var store = {
    get: function (k, d) { try { var v = localStorage.getItem(k); return v ? JSON.parse(v) : d; } catch (e) { return d; } },
    set: function (k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) { /* private mode */ } }
  };

  /* ---------- sticky header shadow + back-to-top ---------- */
  var header = $('[data-header]');
  var toTop = $('[data-totop]');
  function onScroll() {
    var y = window.scrollY || document.documentElement.scrollTop;
    if (header) header.classList.toggle('scrolled', y > 8);
    if (toTop) toTop.classList.toggle('show', y > 900);
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
  if (toTop) toTop.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' }); });

  /* ---------- mobile drawer ---------- */
  var menu = $('[data-menu]');
  var openBtn = $('[data-menu-open]');
  function setMenu(open) {
    if (!menu) return;
    menu.classList.toggle('open', open);
    if (openBtn) openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    document.body.style.overflow = open ? 'hidden' : '';
    if (open) { var first = $('.drawer a', menu); if (first) first.focus(); } else if (openBtn) { openBtn.focus(); }
  }
  if (openBtn) openBtn.addEventListener('click', function () { setMenu(true); });
  $$('[data-menu-close]').forEach(function (b) { b.addEventListener('click', function () { setMenu(false); }); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && menu && menu.classList.contains('open')) setMenu(false); });

  /* ---------- hero slider (Hara style) ---------- */
  var hero = $('[data-hero]');
  if (hero) {
    var slides = $$('.slide', hero);
    var dots = $$('[data-hero-dot]', hero);
    var seconds = Math.max(3, parseFloat(hero.getAttribute('data-interval')) || 9);
    var current = 0;
    var timer = null;
    hero.style.setProperty('--dur', seconds + 's');

    var show = function (i, instant) {
      if (!slides.length) return;
      var next = (i + slides.length) % slides.length;
      slides.forEach(function (s, n) {
        s.classList.remove('leaving');
        if (!instant && n === current && n !== next) {
          s.classList.add('leaving');
        }
        var on = n === next;
        s.classList.toggle('on', on);
        s.setAttribute('aria-hidden', on ? 'false' : 'true');
        $$('a,button', s).forEach(function (el) { el.tabIndex = on ? 0 : -1; });
      });
      dots.forEach(function (d, n) {
        var active = n === next;
        d.classList.toggle('on', active);
        d.setAttribute('aria-current', active ? 'true' : 'false');
      });
      current = next;
    };
    var restart = function () {
      clearInterval(timer);
      if (slides.length > 1) {
        timer = setInterval(function () { show(current + 1, false); }, seconds * 1000);
      }
    };
    dots.forEach(function (d) {
      d.addEventListener('click', function () {
        show(parseInt(d.getAttribute('data-hero-dot'), 10), false);
        restart();
      });
    });
    var prev = $('[data-hero-prev]', hero), next = $('[data-hero-next]', hero);
    if (prev) prev.addEventListener('click', function () { show(current - 1, false); restart(); });
    if (next) next.addEventListener('click', function () { show(current + 1, false); restart(); });

    // swipe on touch screens
    var startX = null;
    hero.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
    hero.addEventListener('touchend', function (e) {
      if (startX === null) return;
      var dx = e.changedTouches[0].clientX - startX;
      if (Math.abs(dx) > 50) { show(current + (dx < 0 ? 1 : -1), false); restart(); }
      startX = null;
    });

    // pause while the tab is hidden
    document.addEventListener('visibilitychange', function () { if (document.hidden) clearInterval(timer); else restart(); });

    // Optional URL param ?slide=1|2|3 for previewing specific slides
    var params = new URLSearchParams(window.location.search);
    var slideParam = parseInt(params.get('slide'), 10);
    var startIdx = (slideParam >= 1 && slideParam <= slides.length) ? (slideParam - 1) : 0;
    show(startIdx, true);
    if (params.get('static') === '1') {
      document.body.classList.add('hero-static');
    } else {
      restart();
    }
  }

  /* ---------- scroll reveal, motion stagger & lazy image fade-in ---------- */
  var compareStarted = false;
  var revealIO = null;
  if ('IntersectionObserver' in window) {
    revealIO = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('in');
        revealIO.unobserve(entry.target);
        if (!compareStarted && $('[data-compare]', entry.target)) {
          compareStarted = true;
          setTimeout(sweepCompare, 900);
        }
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
  }

  function initMotion(root) {
    var scope = root || document;
    // Assign Motion-style staggerChildren delays (60ms steps, capped at 420ms).
    $$('[data-stagger]', scope).forEach(function (group) {
      var step = parseFloat(group.getAttribute('data-stagger')) || 0.06;
      Array.prototype.forEach.call(group.children, function (child, idx) {
        child.style.setProperty('--md', Math.min(idx * step, 0.42).toFixed(2) + 's');
      });
    });

    var targets = $$('[data-reveal], [data-motion], [data-stagger]', scope);
    if (!revealIO || reduceMotion) {
      targets.forEach(function (el) { el.classList.add('in'); });
      return;
    }
    targets.forEach(function (el) {
      if (el.classList.contains('in')) return;
      // Reveal elements already visible above the fold immediately.
      var rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight * 0.96 && rect.bottom > 0) {
        requestAnimationFrame(function () { el.classList.add('in'); });
      } else {
        revealIO.observe(el);
      }
    });
  }

  function initLazyImages(root) {
    var scope = root || document;
    $$('img[loading="lazy"], img[data-lazy]', scope).forEach(function (img) {
      if (img.classList.contains('is-loaded')) return;
      var wrap = img.closest('.pimg, .bcard-img, .cbg, .wcard > a, .bpost-hero');
      var done = function () {
        img.classList.add('is-loaded');
        if (wrap) wrap.classList.remove('img-loading');
      };
      if (reduceMotion || (img.complete && img.naturalWidth > 0)) {
        done();
        return;
      }
      if (wrap) wrap.classList.add('img-loading');
      img.addEventListener('load', done, { once: true });
      img.addEventListener('error', done, { once: true });
      // Safety net so no image ever stays hidden on slow/blocked connections.
      setTimeout(done, 2500);
    });
  }

  initMotion(document);
  initLazyImages(document);

  /* ---------- product tabs ---------- */
  var tabs = $$('[data-tab]');
  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      var key = tab.getAttribute('data-tab');
      tabs.forEach(function (t) {
        var on = t === tab;
        t.classList.toggle('on', on);
        t.setAttribute('aria-selected', on ? 'true' : 'false');
      });
      $$('[data-panel]').forEach(function (p) { p.hidden = p.getAttribute('data-panel') !== key; });
    });
  });

  /* ---------- toast + "coming soon" links ---------- */
  var toastEl = $('[data-toast]');
  var toastTimer = null;
  function toast(msg) {
    if (!toastEl) return;
    toastEl.textContent = msg;
    toastEl.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { toastEl.classList.remove('show'); }, 2400);
  }
  // Pages that are not built yet are marked data-soon in the markup.
  document.addEventListener('submit', function (e) {
    if (e.target.matches('form[data-soon]')) { e.preventDefault(); toast('Search is coming soon.'); }
  });

  /* ---------- header dropdowns (recently viewed + mini-cart) ---------- */
  var pops = $$('[data-pop]');
  function closePops(except) {
    pops.forEach(function (p) {
      if (p === except) return;
      var panel = $('[data-pop-panel]', p);
      if (panel) panel.hidden = true;
      var b = $('[data-pop-btn]', p);
      if (b) b.setAttribute('aria-expanded', 'false');
    });
  }
  pops.forEach(function (p) {
    var btn = $('[data-pop-btn]', p);
    var panel = $('[data-pop-panel]', p);
    btn.addEventListener('click', function () {
      var open = panel.hidden;
      closePops(p);
      panel.hidden = !open;
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });
  // Close when clicking anywhere outside an open dropdown (clicks inside, like qty +/-, keep it open).
  document.addEventListener('click', function (e) { if (!e.target.closest('[data-pop]')) closePops(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closePops(); });

  /* ---------- cart (server session), wishlist, compare, recently viewed ---------- */
  var SB = window.SB || { cart: { items: [] }, routes: {} };
  var taka = function (n) { return 'Tk ' + Math.round(n).toLocaleString('en-US'); };
  var esc = function (s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; };
  var cart = SB.cart;                        // summary from the server (App\Services\Cart::json)
  var wish = SB.user ? (SB.wish || []) : store.get('sb_wish', []); // account list when logged in, else this browser
  var compare = store.get('sb_compare', []); // product ids, max 4
  var recent = store.get('sb_recent', []);   // newest first, max 6
  var COMPARE_MAX = 4;
  var miniCart = $('[data-minicart]');
  var csrf = ($('meta[name="csrf-token"]') || {}).content;
  var cartPage = $('[data-cart-page]');
  var coSummary = $('[data-checkout-summary]');

  function productOf(el) {
    var card = el.closest('[data-product]');
    try { return card ? JSON.parse(card.getAttribute('data-product')) : null; } catch (err) { return null; }
  }
  function bump(el) { el.classList.remove('bump'); void el.offsetWidth; el.classList.add('bump'); }

  // POST to a cart endpoint; resolves with the JSON ({ok, message, cart}) and refreshes every cart view.
  function cartApi(url, body) {
    [cartPage, coSummary].forEach(function (el) { if (el) el.classList.add('busy'); });
    var unbusy = function () { [cartPage, coSummary].forEach(function (el) { if (el) el.classList.remove('busy'); }); };
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify(body || {})
    }).then(function (r) {
      if (r.status === 419) { toast('Your session expired. Reloading…'); setTimeout(function () { location.reload(); }, 900); throw new Error('csrf'); }
      return r.json().then(function (d) { d.status = r.status; return d; });
    }).then(function (d) {
      if (d.cart) { cart = d.cart; renderCounts('cart'); refreshCartPages(); }
      if (d.message) toast(d.message);
      return d;
    }).catch(function (err) {
      if (err.message !== 'csrf') toast('Something went wrong. Please try again.');
      unbusy();
      return { ok: false };
    });
  }

  // item: {id, vid?}
  function addToCart(item, qty, quiet) {
    return cartApi(SB.routes.add, { product_id: item.id, variant_id: item.vid || null, qty: qty || 1 });
  }
  function setQty(key, qty) { return cartApi(SB.routes.update, { key: key, qty: qty }); }
  function lineQty(key) { var it = (cart.items || []).filter(function (i) { return i.key === key; })[0]; return it ? it.qty : 0; }

  function renderMiniCart() {
    if (!miniCart) return;
    var items = cart.items || [];
    $('[data-mc-items]', miniCart).innerHTML = items.map(function (it) {
      var full = it.max !== null && it.qty >= it.max;
      return '<li><a href="' + esc(it.url) + '"><img src="' + esc(it.image) + '" alt="" width="56" height="56"></a>' +
        '<div><a class="mn" href="' + esc(it.url) + '">' + esc(it.name) + (it.variant ? ' – ' + esc(it.variant) : '') + '</a><div class="mp">' + esc(it.price_text) + '</div>' +
        '<div class="qty"><button type="button" aria-label="Decrease quantity" data-qty="-1" data-key="' + esc(it.key) + '"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/></svg></button>' +
        '<span aria-label="Quantity">' + it.qty + '</span>' +
        '<button type="button" aria-label="Increase quantity" data-qty="1" data-key="' + esc(it.key) + '"' + (full ? ' disabled' : '') + '><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg></button></div></div>' +
        '<button class="mc-del" type="button" aria-label="Remove ' + esc(it.name) + '" data-del="' + esc(it.key) + '"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg></button></li>';
    }).join('');
    $('[data-mc-empty]', miniCart).hidden = items.length > 0;
    $('[data-mc-foot]', miniCart).hidden = items.length === 0;
    $('[data-mc-sub]', miniCart).textContent = cart.subtotal_text || 'Tk 0';
    var bar = $('[data-mc-bar]', miniCart), note = $('[data-mc-ship]', miniCart);
    if (cart.free_over) {
      var base = (cart.subtotal || 0) - (cart.discount || 0);
      bar.parentNode.hidden = false;
      var pct = Math.min(100, Math.round(base / cart.free_over * 100));
      bar.style.width = Math.max(pct, 9) + '%'; // keep room for the % label (Hara shows "8%")
      var pctEl = $('[data-mc-pct]', miniCart); if (pctEl) pctEl.textContent = pct + '%';
      note.innerHTML = cart.free_left > 0 ? 'Spend <b>' + esc(cart.free_left_text) + '</b> more to get <b>FREE delivery</b>.' : 'You get <b>FREE delivery</b> on this order!';
    } else {
      bar.parentNode.hidden = true;
      note.textContent = '';
    }
  }

  // Cart page and checkout show server-rendered blocks; fetch them again after a change.
  function refreshCartPages() {
    if (cartPage) {
      cartPage.classList.add('busy');
      fetch(SB.routes.cart, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function (r) { return r.text(); })
        .then(function (html) { cartPage.innerHTML = html; cartPage.classList.remove('busy'); });
    }
    if (coSummary) {
      if (!(cart.items || []).length) { location.href = SB.routes.cart; return; }
      coSummary.classList.add('busy');
      fetch(SB.routes.checkout, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function (r) { return r.text(); })
        .then(function (html) {
          coSummary.innerHTML = html;
          coSummary.classList.remove('busy');
          var t = $('[data-total]', coSummary), amt = $('[data-bkash-amount]');
          if (t && amt) amt.textContent = t.getAttribute('data-total');
        });
    }
  }

  function renderRecent() {
    var list = $('[data-recent-list]');
    if (!list) return;
    list.innerHTML = recent.map(function (it) {
      return '<li><a href="' + esc(it.url || '#') + '"><img src="' + esc(it.image) + '" alt="" width="56" height="56"></a><div><a class="mn" href="' + esc(it.url || '#') + '">' + esc(it.name) + '</a><div class="mp">' + taka(it.price) + '</div></div><span></span></li>';
    }).join('');
    $('[data-recent-empty]').hidden = recent.length > 0;
  }
  function rememberView(p) {
    if (!p) return;
    recent = [{ id: p.id, name: p.name, price: p.price, image: p.image, url: p.url }].concat(recent.filter(function (r) { return r.id !== p.id; })).slice(0, 6);
    store.set('sb_recent', recent);
    renderRecent();
  }

  function renderCounts(animate) {
    $$('[data-cart-count]').forEach(function (el) { el.textContent = cart.count || 0; if (animate === 'cart') bump(el); });
    $$('[data-wish-count]').forEach(function (el) { el.textContent = wish.length; if (animate === 'wish') bump(el); });
    $$('[data-compare-count]').forEach(function (el) { el.textContent = compare.length; if (animate === 'compare') bump(el); });
    $$('[data-wish]').forEach(function (b) {
      var on = wish.indexOf(b.getAttribute('data-wish')) !== -1;
      b.classList.toggle('on', on);
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
      if (b.hasAttribute('data-wish-text')) { var s = $('span', b); if (s) s.textContent = on ? 'Saved to wishlist' : 'Add to wishlist'; }
      else b.setAttribute('aria-label', on ? 'Remove from wishlist' : 'Add to wishlist');
    });
    $$('[data-compare-add]').forEach(function (b) {
      var on = compare.indexOf(parseInt(b.getAttribute('data-compare-add'), 10)) !== -1;
      b.classList.toggle('on-cmp', on);
      var s = $('span', b); if (s) s.textContent = on ? 'Added to compare' : 'Add to compare';
    });
    renderMiniCart();
  }

  document.addEventListener('click', function (e) {
    var add = e.target.closest('[data-add]');
    if (add) {
      var p = productOf(add);
      if (!p || add.disabled) return;
      var label = $('span', add);
      add.disabled = true;
      if (add.hasAttribute('data-variant')) p.vid = parseInt(add.getAttribute('data-variant'), 10);
      addToCart(p, 1).then(function (d) {
        add.disabled = false;
        if (!d.ok) return;
        add.classList.add('added');
        if (label) label.textContent = 'Added!';
        setTimeout(function () { add.classList.remove('added'); if (label) label.textContent = 'Add to cart'; }, 1400);
      });
      return;
    }
    var heart = e.target.closest('[data-wish]');
    if (heart) {
      var wid = heart.getAttribute('data-wish');
      var i = wish.indexOf(wid);
      var done = function (on) {
        renderCounts('wish');
        toast(on ? 'Saved to wishlist' : 'Removed from wishlist');
        if (!on && heart.hasAttribute('data-wish-remove')) {
          var card = heart.closest('.wcard');
          if (card) card.remove();
          if (!$('.wcard')) location.reload();
        }
      };
      if (SB.user) {
        // Save the chosen size/colour too when the heart is on a product page.
        var buyBox = heart.closest('[data-buy]');
        var vid = buyBox && buyBox.currentVariant ? buyBox.currentVariant.id : null;
        fetch(SB.routes.wishToggle, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ product_id: parseInt(wid, 10), variant_id: vid }) })
          .then(function (r) { return r.json(); })
          .then(function (d) { wish = d.ids || wish; done(!!d.on); })
          .catch(function () { toast('Could not update your wishlist. Please try again.'); });
      } else {
        if (i === -1) wish.push(wid); else wish.splice(i, 1);
        store.set('sb_wish', wish);
        done(i === -1);
      }
      return;
    }
    var q = e.target.closest('[data-qty], [data-cq]');
    if (q && !q.disabled) {
      var key = q.getAttribute('data-key');
      var delta = parseInt(q.getAttribute('data-qty') || q.getAttribute('data-cq'), 10);
      setQty(key, Math.max(0, lineQty(key) + delta));
      return;
    }
    var del = e.target.closest('[data-del]');
    if (del) { cartApi(SB.routes.remove, { key: del.getAttribute('data-del') }); return; }
    if (e.target.closest('[data-coupon-remove]')) { cartApi(SB.routes.coupon, { code: '' }); return; }
    var view = e.target.closest('[data-view]');
    if (view) rememberView(productOf(view));
    var soon = e.target.closest('[data-soon]');
    if (soon && soon.tagName !== 'FORM') {
      e.preventDefault();
      toast('This page is coming soon.');
    }
  });
  document.addEventListener('submit', function (e) {
    var f = e.target.closest('[data-coupon-form]');
    if (!f) return;
    e.preventDefault();
    cartApi(SB.routes.coupon, { code: $('input', f).value });
  });
  document.addEventListener('change', function (e) {
    if (e.target.matches('[data-ship-method]')) cartApi(SB.routes.shipping, { method: e.target.value });
    if (e.target.matches('[data-ship-district]') && e.target.value) cartApi(SB.routes.shipping, { district: e.target.value });
  });
  renderCounts();
  renderRecent();

  /* ---------- checkout form ---------- */
  var checkout = $('[data-checkout]');
  if (checkout) {
    var shipToggle = $('[data-ship-toggle]', checkout), shipTo = $('[data-shipto]', checkout);
    var pickup = $('[data-pickup]', checkout);
    var deliveryDistrict = function () {
      var other = shipToggle && shipToggle.checked && $('[data-district-ship]', checkout).value;
      return other || $('[data-district]', checkout).value;
    };
    var syncShipping = function () {
      if (pickup && pickup.checked) { cartApi(SB.routes.shipping, { method: 'pickup' }); return; }
      var d = deliveryDistrict();
      // Choosing a district sets the inside/outside Dhaka charge on the server.
      cartApi(SB.routes.shipping, d ? { district: d } : { method: 'inside_dhaka' });
    };
    $('[data-district]', checkout).addEventListener('change', syncShipping);
    var sd = $('[data-district-ship]', checkout); if (sd) sd.addEventListener('change', syncShipping);
    if (pickup) pickup.addEventListener('change', syncShipping);
    if (shipToggle) shipToggle.addEventListener('change', function () { shipTo.hidden = !shipToggle.checked; syncShipping(); });

    var pays = $$('[data-pay]', checkout);
    var showPay = function () {
      var v = (pays.filter(function (p) { return p.checked; })[0] || {}).value;
      $$('[data-pay-box]', checkout).forEach(function (b) { b.hidden = b.getAttribute('data-pay-box') !== v; });
    };
    pays.forEach(function (p) { p.addEventListener('change', showPay); });
    showPay();

    checkout.addEventListener('submit', function () {
      var btn = $('[data-place-order]', checkout);
      btn.disabled = true;
      btn.lastChild.textContent = ' Placing order…';
    });
    // Clear a field's red error state as soon as the customer edits it.
    checkout.addEventListener('input', function (e) {
      var f = e.target.closest('.fld');
      if (!f || !e.target.classList.contains('bad')) return;
      e.target.classList.remove('bad');
      var em = $('em', f); if (em) em.remove();
    });
    var errs = $('[data-errors]');
    if (errs) errs.focus();
  }

  /* ---------- popup (Quick View + Compare) and image lightbox ---------- */
  var modal = $('[data-modal]');
  var modalBody = modal ? $('[data-modal-body]', modal) : null;
  var lastFocus = null;
  function openModal(html, wide) {
    if (!modal) return;
    lastFocus = document.activeElement;
    modalBody.innerHTML = html;
    modal.classList.toggle('wide', !!wide);
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(function () { modal.classList.add('open'); });
    $('.mbox', modal).focus();
    initWidgets(modalBody);
    initMotion(modalBody);
    initLazyImages(modalBody);
    renderCounts();
  }
  function closeModal() {
    if (!modal || modal.hidden) return;
    $$('[data-gallery]', modalBody).forEach(function (g) { if (g.stopAuto) g.stopAuto(); });
    modal.classList.remove('open');
    document.body.style.overflow = '';
    setTimeout(function () { modal.hidden = true; modalBody.innerHTML = ''; }, 250);
    if (lastFocus) lastFocus.focus();
  }
  function loading() { return '<div class="mload"><span class="spin"></span>Loading…</div>'; }
  function fetchHtml(url) {
    return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function (r) {
      if (!r.ok) throw new Error(r.status);
      return r.text();
    });
  }
  $$('[data-modal-close]').forEach(function (b) { b.addEventListener('click', closeModal); });

  var lightbox = $('[data-lightbox]');
  function openLightbox(src, alt) {
    if (!lightbox) return;
    var img = $('[data-lightbox-img]', lightbox);
    img.src = src; img.alt = alt || '';
    lightbox.hidden = false;
    requestAnimationFrame(function () { lightbox.classList.add('open'); });
    $('[data-lightbox-close]', lightbox).focus();
  }
  function closeLightbox() { if (!lightbox || lightbox.hidden) return; lightbox.classList.remove('open'); setTimeout(function () { lightbox.hidden = true; }, 250); }
  if (lightbox) lightbox.addEventListener('click', function (e) { if (e.target.tagName !== 'IMG') closeLightbox(); });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    if (lightbox && !lightbox.hidden) closeLightbox(); else closeModal();
  });

  document.addEventListener('click', function (e) {
    var qv = e.target.closest('[data-quick]');
    if (qv) {
      e.preventDefault();
      rememberView(productOf(qv));
      openModal(loading());
      fetchHtml(qv.getAttribute('data-quick')).then(function (html) { openModal(html); })
        .catch(function () { closeModal(); toast('Could not open this product. Please try again.'); });
    }
  });

  /* ---------- compare ---------- */
  function compareUrl() { return SB.routes.compare + '?ids=' + compare.join(','); }
  function openCompare() {
    openModal(loading(), true);
    fetchHtml(compareUrl()).then(function (html) { openModal('<h2 class="mtitle">Compare products</h2>' + html, true); })
      .catch(function () { closeModal(); });
  }
  document.addEventListener('click', function (e) {
    var addc = e.target.closest('[data-compare-add]');
    if (addc) {
      var id = parseInt(addc.getAttribute('data-compare-add'), 10);
      if (compare.indexOf(id) === -1) {
        if (compare.length >= COMPARE_MAX) compare.shift(); // keep the newest 4
        compare.push(id);
        store.set('sb_compare', compare);
        renderCounts('compare');
      }
      openCompare();
      return;
    }
    var rm = e.target.closest('[data-compare-remove]');
    if (rm) {
      var rid = parseInt(rm.getAttribute('data-compare-remove'), 10);
      compare = compare.filter(function (x) { return x !== rid; });
      store.set('sb_compare', compare);
      renderCounts('compare');
      if ($('[data-compare-page]')) { location.replace(compareUrl()); return; }
      openCompare();
      return;
    }
    var link = e.target.closest('[data-compare-link]');
    if (link) {
      e.preventDefault();
      openCompare();
    }
  });
  // /compare opened without ids: use the list saved in this browser.
  if ($('[data-compare-page]') && !/[?&]ids=/.test(location.search) && compare.length) location.replace(location.pathname + '?ids=' + compare.join(','));

  /* ---------- widgets inside a page or a popup: gallery, buy box ---------- */
  function initGallery(root) {
    var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    $$('[data-gallery]', root).forEach(function (g) {
      var slides = $$('[data-gslide]', g), thumbs = $$('[data-gthumb]', g), cur = 0;
      if (!slides.length) return;
      var thumbsWrap = $('.gthumbs', g);
      var intervalMs = Math.max(1500, (parseFloat(g.getAttribute('data-interval')) || 3.5) * 1000);
      var timer = null;
      var paused = false;
      var lockedByVariant = false;

      var show = function (i, instant) {
        var next = (i + slides.length) % slides.length;
        slides.forEach(function (s, n) {
          s.classList.remove('prev');
          if (!instant && n === cur && n !== next) s.classList.add('prev');
          var active = n === next;
          s.classList.toggle('on', active);
          s.tabIndex = active ? 0 : -1;
        });
        thumbs.forEach(function (t, n) {
          var active = n === next;
          t.classList.toggle('on', active);
          if (active && thumbsWrap && !instant) {
            var left = t.offsetLeft - (thumbsWrap.clientWidth - t.offsetWidth) / 2;
            thumbsWrap.scrollTo({ left: Math.max(0, left), behavior: 'smooth' });
          }
        });
        cur = next;
      };

      var stopAuto = function () {
        if (timer) { clearInterval(timer); timer = null; }
      };
      var startAuto = function () {
        stopAuto();
        if (slides.length <= 1 || reducedMotion || paused || lockedByVariant) return;
        timer = setInterval(function () {
          if (!g.isConnected) { stopAuto(); return; }
          if (document.hidden) return;
          if (lightbox && !lightbox.hidden) return;
          show(cur + 1, false);
        }, intervalMs);
      };

      g.stopAuto = stopAuto;
      g.showSrc = function (src) {
        slides.forEach(function (s, n) {
          if (s.getAttribute('data-src') === src) {
            lockedByVariant = true;
            stopAuto();
            show(n, false);
          }
        });
      };
      g.resumeAuto = function () {
        lockedByVariant = false;
        startAuto();
      };

      thumbs.forEach(function (t) {
        t.addEventListener('click', function () {
          lockedByVariant = false;
          show(parseInt(t.getAttribute('data-gthumb'), 10), false);
          startAuto();
        });
      });
      $$('[data-gstep]', g).forEach(function (b) {
        b.addEventListener('click', function () {
          lockedByVariant = false;
          show(cur + parseInt(b.getAttribute('data-gstep'), 10), false);
          startAuto();
        });
      });
      slides.forEach(function (s) {
        s.addEventListener('click', function () {
          openLightbox(s.getAttribute('data-src'), $('img', s).alt);
        });
      });

      g.addEventListener('mouseenter', function () { paused = true; stopAuto(); });
      g.addEventListener('mouseleave', function () { paused = false; startAuto(); });
      g.addEventListener('focusin', function () { paused = true; stopAuto(); });
      g.addEventListener('focusout', function (e) {
        if (!g.contains(e.relatedTarget)) { paused = false; startAuto(); }
      });

      var sx = null;
      g.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; stopAuto(); }, { passive: true });
      g.addEventListener('touchend', function (e) {
        if (sx === null) return;
        var dx = e.changedTouches[0].clientX - sx;
        if (Math.abs(dx) > 40) { lockedByVariant = false; show(cur + (dx < 0 ? 1 : -1), false); }
        sx = null;
        startAuto();
      });

      document.addEventListener('visibilitychange', function () {
        if (!g.isConnected) { stopAuto(); return; }
        if (document.hidden) stopAuto(); else startAuto();
      });

      startAuto();
    });
  }

  function initBuy(root) {
    $$('[data-buy]', root).forEach(function (box) {
      var product = JSON.parse(box.getAttribute('data-product'));
      var variants = JSON.parse(box.getAttribute('data-variants') || '[]');
      var gallery = (box.closest('.pdp-main, .quick') || document).querySelector('[data-gallery]');
      var chosen = {};   // attribute slug -> value id
      var current = null;
      var priceEl = $('[data-b-price]', box), oldEl = $('[data-b-old]', box), stockEl = $('[data-b-stock]', box), skuEl = $('[data-b-sku]', box);
      var addBtn = $('[data-buy-add]', box), nowBtn = $('[data-buy-now]', box), clear = $('[data-vclear]', box);
      var input = $('[data-bq-input]', box);
      var base = { price: priceEl.textContent, old: oldEl ? oldEl.textContent : '', stock: stockEl.innerHTML, sku: skuEl ? skuEl.textContent : '' };
      var groups = $$('[data-vopt]', box);

      function match() {
        var ids = Object.keys(chosen).map(function (k) { return chosen[k]; });
        if (ids.length < groups.length) return null;
        return variants.filter(function (v) { return ids.every(function (id) { return v.values.indexOf(id) !== -1; }); })[0] || null;
      }
      function update() {
        current = match();
        box.currentVariant = current;
        var chosenBox = $('[data-b-chosen]', box);
        if (chosenBox) chosenBox.hidden = !current;
        groups.forEach(function (g) {
          var b = $('[data-vval][aria-pressed="true"]', g);
          $('[data-vchosen]', g).textContent = b ? b.textContent.trim() : 'Choose an option';
        });
        if (clear) clear.hidden = Object.keys(chosen).length === 0;
        if (current) {
          priceEl.textContent = taka(current.price);
          if (oldEl) oldEl.textContent = current.old_price ? taka(current.old_price) : '';
          var ok = current.stock === null || current.stock > 0;
          stockEl.classList.toggle('out', !ok);
          stockEl.classList.remove('wait');
          stockEl.innerHTML = ok ? '✓ In stock' + (current.stock !== null ? ' (' + current.stock + ')' : '') : 'Out of stock';
          if (skuEl) skuEl.textContent = current.sku || '—';
          addBtn.disabled = nowBtn.disabled = !ok;
          if (current.image && gallery && gallery.showSrc) gallery.showSrc(current.image);
        } else {
          priceEl.textContent = base.price;
          if (oldEl) oldEl.textContent = base.old;
          stockEl.innerHTML = base.stock;
          stockEl.classList.remove('out');
          if (variants.length) stockEl.classList.add('wait');
          if (skuEl) skuEl.textContent = base.sku;
          addBtn.disabled = nowBtn.disabled = false;
          if (gallery && gallery.resumeAuto) gallery.resumeAuto();
        }
      }
      groups.forEach(function (g) {
        $$('[data-vval]', g).forEach(function (b) {
          b.addEventListener('click', function () {
            var on = b.getAttribute('aria-pressed') === 'true';
            $$('[data-vval]', g).forEach(function (o) { o.setAttribute('aria-pressed', 'false'); });
            if (on) delete chosen[g.getAttribute('data-vopt')];
            else { b.setAttribute('aria-pressed', 'true'); chosen[g.getAttribute('data-vopt')] = parseInt(b.getAttribute('data-vval'), 10); }
            update();
          });
        });
      });
      if (clear) clear.addEventListener('click', function () {
        chosen = {};
        $$('[data-vval]', box).forEach(function (o) { o.setAttribute('aria-pressed', 'false'); });
        update();
      });
      $$('[data-bq]', box).forEach(function (b) {
        b.addEventListener('click', function () { input.value = Math.max(1, (parseInt(input.value, 10) || 1) + parseInt(b.getAttribute('data-bq'), 10)); });
      });
      function item() {
        if (variants.length && !current) {
          toast('Please choose ' + groups.map(function (g) { return $('.vlabel', g).firstChild.textContent.replace(':', '').trim().toLowerCase(); }).join(' and ') + ' first.');
          groups[0].classList.add('shake'); setTimeout(function () { groups[0].classList.remove('shake'); }, 600);
          return null;
        }
        var it = { id: product.id, name: product.name, price: product.price, image: product.image, url: product.url };
        if (current) { it.vid = current.id; it.variant = current.label; it.price = current.price; if (current.image) it.image = current.image; }
        return it;
      }
      addBtn.addEventListener('click', function () {
        var it = item(); if (!it) return;
        addToCart(it, Math.max(1, parseInt(input.value, 10) || 1));
      });
      nowBtn.addEventListener('click', function () {
        var it = item(); if (!it) return;
        nowBtn.disabled = true;
        addToCart(it, Math.max(1, parseInt(input.value, 10) || 1)).then(function (d) {
          nowBtn.disabled = false;
          if (d.ok) location.href = SB.routes.checkout;
        });
      });
      box.buyAdd = function () { addBtn.click(); };
      box.hasVariants = variants.length > 0;
    });
  }

  function initWidgets(root) { initGallery(root); initBuy(root); }
  initWidgets(document);

  document.addEventListener('click', function (e) {
    var copy = e.target.closest('[data-copy]');
    if (copy) {
      var url = copy.getAttribute('data-copy');
      (navigator.clipboard ? navigator.clipboard.writeText(url) : Promise.reject()).then(function () { toast('Link copied'); }, function () { toast(url); });
    }
  });

  /* ---------- product page: tabs, sticky bar, bundle, recently viewed ---------- */
  var pdp = $('[data-pdp]');
  if (pdp) {
    try { rememberView(JSON.parse(pdp.getAttribute('data-recent'))); } catch (err) { /* ignore */ }

    var ptabs = $$('[data-ptab]', pdp);
    var showTab = function (key) {
      ptabs.forEach(function (t) { var on = t.getAttribute('data-ptab') === key; t.classList.toggle('on', on); t.setAttribute('aria-selected', on ? 'true' : 'false'); });
      $$('[data-ppanel]', pdp).forEach(function (p) { p.hidden = p.getAttribute('data-ppanel') !== key; });
    };
    ptabs.forEach(function (t) { t.addEventListener('click', function () { showTab(t.getAttribute('data-ptab')); }); });
    $$('[data-tab-link]', pdp).forEach(function (a) {
      a.addEventListener('click', function (e) { e.preventDefault(); showTab(a.getAttribute('data-tab-link')); $('#reviews').scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' }); });
    });

    var bar = $('[data-pbar]');
    var buy = $('[data-buy]', pdp);
    if (bar && buy && 'IntersectionObserver' in window) {
      new IntersectionObserver(function (en) {
        var show = !en[0].isIntersecting && en[0].boundingClientRect.top < 0;
        bar.classList.toggle('show', show);
        document.body.classList.toggle('pbar-on', show);
        bar.setAttribute('aria-hidden', show ? 'false' : 'true');
        $('[data-pbar-add]', bar).tabIndex = show ? 0 : -1;
      }).observe($('.bactions', buy));
      $('[data-pbar-add]', bar).addEventListener('click', function () {
        if (buy.hasVariants) buy.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' });
        else buy.buyAdd();
      });
    }

    var fbt = $('[data-fbt]', pdp);
    if (fbt) {
      var boxes = $$('[data-fbt-item]', fbt);
      var total = function () {
        var sum = boxes.reduce(function (n, b) { return n + (b.checked ? parseInt(b.getAttribute('data-price'), 10) : 0); }, 0);
        $('[data-fbt-total]', fbt).textContent = taka(sum);
        $('[data-fbt-add]', fbt).disabled = sum === 0;
        boxes.forEach(function (b) { b.closest('li').classList.toggle('off', !b.checked); });
      };
      boxes.forEach(function (b) { b.addEventListener('change', total); });
      $('[data-fbt-add]', fbt).addEventListener('click', function () {
        // Add one after another so the session is not written twice at once.
        var list = boxes.filter(function (b) { return b.checked; }).map(function (b) {
          var p = productOf(b), dv = p && p.default_variant;
          return p ? { id: p.id, vid: dv ? dv.id : null } : null;
        }).filter(Boolean);
        var btn = this, ok = 0;
        btn.disabled = true;
        list.reduce(function (chain, it) {
          return chain.then(function () { return cartApi(SB.routes.add, { product_id: it.id, variant_id: it.vid, qty: 1 }).then(function (d) { if (d.ok) ok++; }); });
        }, Promise.resolve()).then(function () {
          btn.disabled = false;
          toast(ok + ' ' + (ok === 1 ? 'item' : 'items') + ' added to cart');
        });
      });
      total();
    }
  }

  /* ---------- shop: filters without page reload ---------- */
  var shop = $('[data-shop]');
  function initPrice(root) {
    var box = $('[data-price]', root);
    if (!box) return;
    var lo = $('[data-price-lo]', box), hi = $('[data-price-hi]', box);
    var min = parseInt(box.getAttribute('data-min'), 10), max = parseInt(box.getAttribute('data-max'), 10);
    var paint = function (moved) {
      var a = parseInt(lo.value, 10), b = parseInt(hi.value, 10);
      if (a > b) { if (moved === lo) lo.value = b; else hi.value = a; a = parseInt(lo.value, 10); b = parseInt(hi.value, 10); }
      var fill = $('[data-price-fill]', box);
      fill.style.left = ((a - min) / (max - min) * 100) + '%';
      fill.style.right = (100 - (b - min) / (max - min) * 100) + '%';
      $('[data-price-text]', box).textContent = taka(a) + ' – ' + taka(b);
    };
    lo.addEventListener('input', function () { paint(lo); });
    hi.addEventListener('input', function () { paint(hi); });
    $('[data-price-apply]', box).addEventListener('click', function () {
      $('[data-price-min]', box).value = parseInt(lo.value, 10) > min ? lo.value : '';
      $('[data-price-max]', box).value = parseInt(hi.value, 10) < max ? hi.value : '';
      submitFilters();
    });
    paint();
  }
  function loadShop(url, push) {
    if (!shop) { location.href = url; return; }
    shop.classList.add('busy');
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' } })
      .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
      .then(function (data) {
        var keepOpen = document.body.classList.contains('filters-open');
        shop.innerHTML = data.html;
        shop.classList.remove('busy');
        var t = $('[data-shop-title]'); if (t && data.title) t.textContent = data.title;
        if (push !== false) history.pushState({ shop: true }, '', url);
        initPrice(shop);
        initMotion(shop);
        initLazyImages(shop);
        renderCounts();
        document.body.classList.toggle('filters-open', keepOpen);
        var res = $('.results', shop);
        if (res) { res.classList.add('fresh'); if (!keepOpen && res.getBoundingClientRect().top < 0) res.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth' }); }
      })
      .catch(function () { location.href = url; });
  }
  function submitFilters() {
    var form = $('[data-filter-form]', shop);
    var params = new URLSearchParams();
    new FormData(form).forEach(function (v, k) { if (v !== '' && !(k === 'sort' && v === 'default') && !(k === 'view' && v === 'grid')) params.append(k, v); });
    var qs = params.toString();
    loadShop(form.getAttribute('action') + (qs ? '?' + qs : ''));
  }
  if (shop) {
    initPrice(shop);
    shop.addEventListener('change', function (e) {
      if (e.target.matches('[data-filter-form] input[type="checkbox"]')) submitFilters();
      if (e.target.matches('[data-sort]')) loadShop(e.target.value);
    });
    shop.addEventListener('click', function (e) {
      var a = e.target.closest('[data-filter-link]');
      if (a) { e.preventDefault(); loadShop(a.getAttribute('href')); return; }
      if (e.target.closest('[data-filter-open]')) { document.body.classList.add('filters-open'); var f = $('#filters'); if (f) { f.setAttribute('tabindex', '-1'); f.focus(); } }
      if (e.target.closest('[data-filter-close]')) document.body.classList.remove('filters-open');
    });
    window.addEventListener('popstate', function () { loadShop(location.href, false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') document.body.classList.remove('filters-open'); });
  }

  /* ---------- header live search ---------- */
  $$('[data-live-search]').forEach(function (form) {
    var input = $('input[type="search"]', form), box = $('[data-suggest]', form), sel = $('select', form);
    var timer = null, last = '';
    var mark = function (text, q) {
      var safe = esc(text);
      q.split(/\s+/).filter(Boolean).forEach(function (w) {
        safe = safe.replace(new RegExp('(' + w.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'ig'), '<mark>$1</mark>');
      });
      return safe;
    };
    var run = function () {
      var q = input.value.trim();
      var key = q + '|' + (sel ? sel.value : '');
      if (q.length < 2) { box.hidden = true; last = ''; return; }
      if (key === last) { box.hidden = false; return; }
      last = key;
      var url = SB.routes.suggest + '?q=' + encodeURIComponent(q) + (sel && sel.value ? '&cat=' + encodeURIComponent(sel.value) : '');
      fetch(url, { headers: { Accept: 'application/json' } }).then(function (r) { return r.json(); }).then(function (d) {
        if (input.value.trim() !== q) return;
        box.innerHTML = d.count
          ? '<div class="sg-h">' + d.count + ' ' + (d.count === 1 ? 'result' : 'results') + ' found with “' + esc(q) + '”</div><ul>' +
            d.items.map(function (it) { return '<li><a href="' + esc(it.url) + '"><img src="' + esc(it.image) + '" alt="" width="44" height="44"><span><b>' + mark(it.name, q) + '</b><em>' + esc(it.price) + '</em></span></a></li>'; }).join('') +
            '</ul>' + (d.count > d.items.length ? '<a class="sg-all" href="' + esc(d.all) + '">See all ' + d.count + ' results</a>' : '')
          : '<div class="sg-h">No products found for “' + esc(q) + '”</div>';
        box.hidden = false;
      }).catch(function () { box.hidden = true; });
    };
    input.setAttribute('autocomplete', 'off');
    input.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(run, 220); });
    input.addEventListener('focus', run);
    if (sel) sel.addEventListener('change', function () { last = ''; run(); });
    input.addEventListener('keydown', function (e) { if (e.key === 'Escape') box.hidden = true; });
    document.addEventListener('click', function (e) { if (!form.contains(e.target)) box.hidden = true; });
  });

  /* ---------- mobile drawer tabs: Main Menu / All Departments ---------- */
  var dtabs = $$('[data-dtab]');
  dtabs.forEach(function (t) {
    t.addEventListener('click', function () {
      var key = t.getAttribute('data-dtab');
      dtabs.forEach(function (o) { o.classList.toggle('on', o === t); o.setAttribute('aria-selected', o === t ? 'true' : 'false'); });
      $$('[data-dpanel]').forEach(function (p) { p.hidden = p.getAttribute('data-dpanel') !== key; });
    });
  });

  /* ---------- before / after compare ---------- */
  var cmp = $('[data-compare]');
  var range = $('[data-compare-range]');
  var touched = false;
  var raf = null;
  function setPos(v) { if (cmp) cmp.style.setProperty('--pos', v + '%'); }
  if (range) {
    range.addEventListener('input', function () {
      touched = true;
      if (raf) cancelAnimationFrame(raf);
      setPos(range.value);
    });
  }
  function sweepCompare() {
    if (!cmp || touched || reduceMotion) return;
    var start = performance.now();
    var frame = function (t) {
      if (touched) return;
      var p = (t - start) / 2200;
      if (p >= 1) { setPos(50); return; }
      setPos(50 + 30 * Math.sin(p * Math.PI * 2) * (1 - p * 0.4));
      raf = requestAnimationFrame(frame);
    };
    raf = requestAnimationFrame(frame);
  }

  /* ---------- testimonials ---------- */
  var testi = $('[data-testi]');
  if (testi) {
    var quotes = $$('[data-tq]', testi);
    var tdots = $$('[data-tdot]', testi);
    var tIndex = 0, tTimer = null;
    var showQuote = function (i) {
      tIndex = (i + quotes.length) % quotes.length;
      quotes.forEach(function (q, n) { q.classList.toggle('on', n === tIndex); });
      tdots.forEach(function (d, n) { d.classList.toggle('on', n === tIndex); d.setAttribute('aria-current', n === tIndex ? 'true' : 'false'); });
    };
    var tRestart = function () { clearInterval(tTimer); tTimer = setInterval(function () { showQuote(tIndex + 1); }, 7000); };
    tdots.forEach(function (d) { d.addEventListener('click', function () { showQuote(parseInt(d.getAttribute('data-tdot'), 10)); tRestart(); }); });
    tRestart();
  }

  /* ---------- newsletter (step 5: live server subscribe) ---------- */
  var nl = $('[data-newsletter]');
  if (nl) {
    nl.addEventListener('submit', function (e) {
      e.preventDefault();
      var input = $('input[name="email"]', nl) || $('input', nl);
      if (!input || !input.value.trim()) return;
      var url = nl.getAttribute('action') || '/newsletter';
      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ email: input.value.trim() }),
      })
        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
        .then(function (res) {
          if (res.ok) {
            nl.classList.add('done');
            input.value = '';
            input.placeholder = res.data.message || 'Thanks! You are subscribed.';
            toast(res.data.message || 'Subscribed!');
          } else {
            toast((res.data && res.data.message) || 'Please enter a valid email address.');
          }
        })
        .catch(function () { toast('Could not subscribe right now. Please try again.'); });
    });
  }

  /* ---------- accounts (step 3) ---------- */
  // After logging in, move the wishlist this browser kept as a guest onto the account.
  var localWish = store.get('sb_wish', []);
  if (SB.user && localWish.length) {
    fetch(SB.routes.wishSync, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ ids: localWish }) })
      .then(function (r) { return r.json(); })
      .then(function (d) { wish = d.ids || wish; store.set('sb_wish', []); renderCounts(); })
      .catch(function () { /* try again next page */ });
  }
  // Guests: the wishlist page needs the ids saved in this browser.
  document.addEventListener('click', function (e) {
    var a = e.target.closest('[data-wish-link]');
    if (a && !SB.user) { e.preventDefault(); location.href = SB.routes.wishlist + '?ids=' + wish.join(','); }
  });
  if ($('[data-wishlist-page][data-guest]') && !/[?&]ids=/.test(location.search) && wish.length) {
    location.replace(location.pathname + '?ids=' + wish.join(','));
  }

  // Sign-in popup
  var loginModal = $('[data-login-modal]');
  var openLogin = function () {
    lastFocus = document.activeElement;
    loginModal.hidden = false;
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(function () { loginModal.classList.add('open'); });
    var first = $('input[name="login"]', loginModal); if (first) first.focus();
  };
  var closeLogin = function () {
    if (!loginModal || loginModal.hidden) return;
    loginModal.classList.remove('open');
    document.body.style.overflow = '';
    setTimeout(function () { loginModal.hidden = true; }, 250);
    if (lastFocus) lastFocus.focus();
  };
  if (loginModal) {
    document.addEventListener('click', function (e) {
      if (e.target.closest('[data-login-open]')) { e.preventDefault(); openLogin(); }
      if (e.target.closest('[data-login-close]')) closeLogin();
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeLogin(); });
  }

  // Show / hide password
  document.addEventListener('click', function (e) {
    var eye = e.target.closest('[data-eye]');
    if (!eye) return;
    var input = eye.parentNode.querySelector('input');
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    eye.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    eye.classList.toggle('on', show);
  });

  // "Are you sure?" on delete buttons
  document.addEventListener('submit', function (e) {
    var msg = e.target.getAttribute && e.target.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) e.preventDefault();
  });

  // Checkout: pick a saved address
  var saved = $('[data-saved-address]');
  if (saved && checkout) {
    saved.addEventListener('change', function () {
      var a; try { a = JSON.parse(saved.value); } catch (err) { return; }
      ['name', 'phone', 'area', 'address'].forEach(function (k) { var el = checkout.querySelector('[name="' + k + '"]'); if (el) el.value = a[k] || ''; });
      var d = $('[data-district]', checkout);
      d.value = a.district;
      d.dispatchEvent(new Event('change', { bubbles: true }));
    });
  }

  // Customer review photos open in the lightbox; limit the photo picker to 3 files.
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-lightbox-src]');
    if (b) openLightbox(b.getAttribute('data-lightbox-src'), '');
  });
  document.addEventListener('change', function (e) {
    var max = e.target.getAttribute && e.target.getAttribute('data-max-files');
    if (max && e.target.files.length > +max) { toast('You can add up to ' + max + ' photos.'); e.target.value = ''; }
  });

  // Open the Reviews tab when the page is opened at #reviews or after posting a review.
  var revPanel = $('[data-ppanel="reviews"]');
  if (revPanel && (location.hash === '#reviews' || !revPanel.hidden)) {
    var rt = $('[data-ptab="reviews"]'); if (rt) rt.click();
  }

  /* ---------- cart page: typed quantities + "Update cart" (Hara) ---------- */
  document.addEventListener('input', function (e) {
    if (!e.target.matches('[data-cart-qty]')) return;
    var btn = $('[data-cart-update]');
    if (btn) btn.disabled = !$$('[data-cart-qty]').some(function (i) { return i.value !== i.getAttribute('data-was'); });
  });
  var runCartUpdate = function () {
    var changed = $$('[data-cart-qty]').filter(function (i) { return i.value !== i.getAttribute('data-was'); });
    var btn = $('[data-cart-update]');
    if (!changed.length) return;
    if (btn) btn.disabled = true;
    changed.reduce(function (chain, i) {
      return chain.then(function () { return setQty(i.getAttribute('data-cart-qty'), Math.max(0, parseInt(i.value, 10) || 0)); });
    }, Promise.resolve());
  };
  document.addEventListener('click', function (e) { if (e.target.closest('[data-cart-update]')) runCartUpdate(); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && e.target.matches && e.target.matches('[data-cart-qty]')) { e.preventDefault(); runCartUpdate(); }
  });
})();
