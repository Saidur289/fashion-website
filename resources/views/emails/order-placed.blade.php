@php use App\Models\Product; @endphp
<!doctype html>
<html>
<body style="margin:0;background:#F4F7FC;font-family:Arial,Helvetica,sans-serif;color:#1F2937">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 12px">
<tr><td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#fff;border-radius:16px;overflow:hidden">
    <tr><td style="background:#0A449B;padding:22px 28px;color:#fff;font-size:22px;font-weight:bold">Safe<span style="color:#F97316">Buy</span></td></tr>
    <tr><td style="padding:28px">
        @if ($forShop)
            <h1 style="margin:0 0 8px;font-size:20px;color:#0A449B">New order {{ $order->number }}</h1>
            <p style="margin:0 0 16px">{{ $order->name }} · {{ $order->phone }} · {{ $order->paymentLabel() }}@if ($order->bkash_trx_id) (TrxID {{ $order->bkash_trx_id }}, from {{ $order->bkash_sender }})@endif</p>
        @else
            <h1 style="margin:0 0 8px;font-size:20px;color:#0A449B">Thank you for your order, {{ $order->name }}!</h1>
            <p style="margin:0 0 16px">Order <b>{{ $order->number }}</b> is received. We will call you on {{ $order->phone }} to confirm it.</p>
        @endif
        <table role="presentation" width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-size:14px">
            @foreach ($order->items as $item)
                <tr style="border-bottom:1px solid #E3E8F2"><td>{{ $item->name }}@if ($item->variant) – {{ $item->variant }}@endif × {{ $item->qty }}</td><td align="right">{{ Product::taka($item->total) }}</td></tr>
            @endforeach
            <tr><td>Subtotal</td><td align="right">{{ Product::taka($order->subtotal) }}</td></tr>
            @if ($order->discount)<tr><td>Coupon {{ $order->coupon_code }}</td><td align="right">− {{ Product::taka($order->discount) }}</td></tr>@endif
            <tr><td>Delivery ({{ $order->shippingLabel() }})</td><td align="right">{{ $order->shipping ? Product::taka($order->shipping) : 'Free' }}</td></tr>
            <tr><td><b>Total</b></td><td align="right" style="color:#F97316;font-weight:bold">{{ Product::taka($order->total) }}</td></tr>
        </table>
        <p style="margin:18px 0 6px;font-size:14px"><b>Deliver to:</b> {{ $order->ship_name ?: $order->name }}, {{ $order->ship_phone ?: $order->phone }}, {{ $order->ship_address ?: $order->address }}, {{ $order->ship_district ?: $order->district }}</p>
        @unless ($forShop)
            <p style="margin:22px 0 0"><a href="{{ route('order.track', ['number' => $order->number]) }}" style="background:#F97316;color:#fff;text-decoration:none;padding:12px 22px;border-radius:999px;font-weight:bold;display:inline-block">Track your order</a></p>
        @endunless
    </td></tr>
    <tr><td style="background:#F4F7FC;padding:16px 28px;font-size:12px;color:#5B6474">SafeBuy · {{ config('safebuy.address') }} · {{ config('safebuy.email') }}</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
