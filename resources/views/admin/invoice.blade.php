@php
    use App\Models\Product;
    $shopName = config('safebuy.shop_name', 'SafeBuy');
    $logo = config('safebuy.logo', 'images/brand/safebuy-logo.webp');
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->number }} — {{ $shopName }}</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;padding:24px;font-family:Inter,system-ui,-apple-system,sans-serif;color:#1F2937;background:#F4F7FC;font-size:14px;line-height:1.55}
        .sheet{max-width:820px;margin:0 auto;background:#fff;border-radius:16px;padding:36px 40px;box-shadow:0 10px 30px rgba(10,68,155,.10)}
        .bar{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px;max-width:820px;margin-inline:auto}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:999px;border:0;font-weight:600;cursor:pointer;text-decoration:none;font-size:13px}
        .btn-blue{background:#0A449B;color:#fff}
        .btn-orange{background:#F97316;color:#fff}
        .head{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #EAF1FB;padding-bottom:20px;margin-bottom:24px}
        .head img{height:46px;width:auto}
        .inv-title{text-align:right}
        .inv-title h1{margin:0;font-size:24px;color:#0A449B;letter-spacing:.04em}
        .inv-title .num{font-size:15px;font-weight:700;color:#F97316;margin-top:4px}
        .cols{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px}
        .box{background:#F8FAFD;border:1px solid #E3E8F2;border-radius:12px;padding:14px 16px}
        .box h3{margin:0 0 8px;font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#0A449B}
        table{width:100%;border-collapse:collapse;margin-bottom:20px}
        th{background:#0A449B;color:#fff;text-align:left;padding:10px 12px;font-size:12px;text-transform:uppercase;letter-spacing:.04em}
        th:first-child{border-radius:8px 0 0 8px}
        th:last-child{border-radius:0 8px 8px 0;text-align:right}
        td{padding:11px 12px;border-bottom:1px solid #E3E8F2;vertical-align:top}
        td.r, th.r{text-align:right}
        .totals{max-width:300px;margin-left:auto}
        .totals div{display:flex;justify-content:space-between;padding:5px 0}
        .totals .grand{border-top:2px solid #0A449B;margin-top:6px;padding-top:10px;font-size:17px;font-weight:700;color:#0A449B}
        .badge{display:inline-block;padding:2px 10px;border-radius:999px;font-size:12px;font-weight:600;background:#EAF1FB;color:#0A449B}
        .badge.paid{background:#DCFCE7;color:#15803D}
        .foot{margin-top:28px;padding-top:16px;border-top:1px dashed #CBD5E1;font-size:12px;color:#6B7280;display:flex;justify-content:space-between}
        @media print{
            body{background:#fff;padding:0}
            .bar{display:none}
            .sheet{box-shadow:none;padding:0;border-radius:0}
        }
    </style>
</head>
<body>
    <div class="bar">
        <a class="btn btn-blue" href="{{ url('/admin/orders') }}">← Back to Orders</a>
        <button class="btn btn-orange" type="button" onclick="window.print()">Print / Save PDF</button>
    </div>

    <div class="sheet">
        <div class="head">
            <div>
                <img src="{{ asset($logo) }}" alt="{{ $shopName }}">
                <div style="margin-top:8px;font-size:13px;color:#4B5563">
                    <div>{{ config('safebuy.address') }}</div>
                    @if (config('safebuy.phone'))<div>Phone: {{ config('safebuy.phone') }}</div>@endif
                    <div>Email: {{ config('safebuy.email') }}</div>
                </div>
            </div>
            <div class="inv-title">
                <h1>INVOICE</h1>
                <div class="num">{{ $order->number }}</div>
                <div style="font-size:13px;color:#6B7280;margin-top:4px">Date: {{ $order->created_at->format('d M Y, h:i A') }}</div>
                <div style="margin-top:6px">
                    <span class="badge">{{ $order->statusLabel() }}</span>
                    <span class="badge {{ $order->payment_status === 'paid' ? 'paid' : '' }}">{{ \App\Models\Order::PAYMENT_STATUSES[$order->payment_status] ?? ucfirst($order->payment_status) }}</span>
                </div>
            </div>
        </div>

        <div class="cols">
            <div class="box">
                <h3>Customer &amp; Delivery</h3>
                <div><strong>{{ $order->ship_name ?: $order->name }}</strong></div>
                <div>Phone: {{ $order->ship_phone ?: $order->phone }}</div>
                @if ($order->email)<div>Email: {{ $order->email }}</div>@endif
                <div style="margin-top:4px">{{ $order->ship_address ?: $order->address }}</div>
                <div>{{ trim(($order->ship_area ?: $order->area).', '.($order->ship_district ?: $order->district), ', ') }}</div>
            </div>
            <div class="box">
                <h3>Payment &amp; Shipping</h3>
                <div><strong>Payment:</strong> {{ $order->paymentLabel() }}</div>
                @if ($order->payment_method === 'bkash')
                    <div><strong>bKash Sender:</strong> {{ $order->bkash_sender ?: '—' }}</div>
                    <div><strong>TrxID:</strong> {{ $order->bkash_trx_id ?: '—' }}</div>
                @endif
                <div style="margin-top:6px"><strong>Delivery:</strong> {{ $order->shippingLabel() }}</div>
                @if ($order->tracking_code)<div><strong>Tracking Code:</strong> {{ $order->tracking_code }}</div>@endif
                @if ($order->note)<div style="margin-top:6px"><strong>Note:</strong> {{ $order->note }}</div>@endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th class="r">Price</th>
                    <th class="r">Qty</th>
                    <th class="r">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <strong>{{ $item->name }}</strong>
                            @if ($item->variant)<div style="font-size:12px;color:#6B7280">{{ $item->variant }}</div>@endif
                        </td>
                        <td>{{ $item->sku ?: '—' }}</td>
                        <td class="r">{{ Product::taka($item->price) }}</td>
                        <td class="r">{{ $item->qty }}</td>
                        <td class="r"><strong>{{ Product::taka($item->total) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div><span>Subtotal</span><span>{{ Product::taka($order->subtotal) }}</span></div>
            @if ($order->discount > 0)
                <div><span>Discount @if ($order->coupon_code)({{ $order->coupon_code }})@endif</span><span>−{{ Product::taka($order->discount) }}</span></div>
            @endif
            <div><span>Delivery ({{ $order->shippingLabel() }})</span><span>{{ $order->shipping > 0 ? Product::taka($order->shipping) : 'Free' }}</span></div>
            <div class="grand"><span>Total</span><span>{{ Product::taka($order->total) }}</span></div>
        </div>

        <div class="foot">
            <span>Thank you for shopping with {{ $shopName }} — 100% Original Products.</span>
            <span>Track: {{ route('order.track', ['number' => $order->number]) }}</span>
        </div>
    </div>
</body>
</html>
