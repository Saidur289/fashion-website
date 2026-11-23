@php use App\Models\Product; @endphp
<table class="otable">
    <thead><tr><th scope="col">Product</th><th scope="col">Total</th></tr></thead>
    <tbody>
        @foreach ($order->items as $item)
            <tr>
                <td><span class="oi"><img src="{{ $item->image }}" alt="" width="48" height="48"> <span>{{ $item->name }}@if ($item->variant) – {{ $item->variant }}@endif <b>× {{ $item->qty }}</b></span></span></td>
                <td>{{ Product::taka($item->total) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr><th scope="row">Subtotal</th><td>{{ Product::taka($order->subtotal) }}</td></tr>
        @if ($order->discount)<tr><th scope="row">Coupon {{ $order->coupon_code }}</th><td>− {{ Product::taka($order->discount) }}</td></tr>@endif
        <tr><th scope="row">Delivery ({{ $order->shippingLabel() }})</th><td>{{ $order->shipping ? Product::taka($order->shipping) : 'Free' }}</td></tr>
        <tr><th scope="row">Payment</th><td>{{ $order->paymentLabel() }}</td></tr>
        <tr class="grand"><th scope="row">Total</th><td>{{ Product::taka($order->total) }}</td></tr>
    </tfoot>
</table>
