@if ($orders->isEmpty())
    <div class="empty sm">
        <x-icon name="bag" size="32" />
        <p>No orders yet. Orders you place while logged in show up here.</p>
        <a class="btn orange sm" href="{{ route('shop') }}">Start shopping</a>
    </div>
@else
    <table class="otable acc-orders">
        <thead><tr><th scope="col">Order</th><th scope="col">Date</th><th scope="col">Status</th><th scope="col">Total</th><th scope="col"><span class="vh">Actions</span></th></tr></thead>
        <tbody>
            @foreach ($orders as $o)
                <tr>
                    <td data-label="Order"><a href="{{ route('account.order', $o) }}" class="tlink">{{ $o->number }}</a></td>
                    <td data-label="Date">{{ $o->created_at->format('j M Y') }}</td>
                    <td data-label="Status"><span @class(['stag', 'st-'.$o->status])>{{ $o->statusLabel() }}</span></td>
                    <td data-label="Total">{{ \App\Models\Product::taka($o->total) }} <small class="small">for {{ $o->items->sum('qty') }} {{ \Illuminate\Support\Str::plural('item', $o->items->sum('qty')) }}</small></td>
                    <td><a class="btn sm ghost" href="{{ route('account.order', $o) }}">View</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
