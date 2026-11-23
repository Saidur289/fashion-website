@php use App\Models\Product; @endphp
@if ($products->isEmpty())
    <div class="empty">
        <x-icon name="compare" size="36" />
        <h2>No products to compare yet</h2>
        <p>Use the <b>compare</b> button on any product to add it here (up to {{ \App\Http\Controllers\CompareController::MAX }}).</p>
        <a class="btn" href="{{ route('shop') }}">Browse the shop</a>
    </div>
@else
    <div class="cmpwrap">
        <table class="cmptable">
            <tbody>
                <tr>
                    <th scope="row">Product</th>
                    @foreach ($products as $p)
                        <td>
                            <button class="cmp-x" type="button" data-compare-remove="{{ $p->id }}" aria-label="Remove {{ $p->name }} from compare"><x-icon name="close" size="14" /></button>
                            <a href="{{ route('product.show', $p) }}" class="cmp-p">
                                <img src="{{ asset($p->image) }}" alt="" width="160" height="160">
                                <span>{{ $p->name }}</span>
                            </a>
                        </td>
                    @endforeach
                </tr>
                <tr><th scope="row">Price</th>@foreach ($products as $p)<td class="cmp-price">{{ $p->priceLabel() }}</td>@endforeach</tr>
                <tr>
                    <th scope="row">Add to cart</th>
                    @foreach ($products as $p)
                        <td data-product='@json($p->cardData())'>
                            @if ($p->isVariable())
                                <a class="btn sm blue" href="{{ route('product.show', $p) }}">Select options</a>
                            @elseif ($p->inStock())
                                <button class="btn sm orange" type="button" data-add="{{ $p->id }}"><span>Add to cart</span></button>
                            @else
                                —
                            @endif
                        </td>
                    @endforeach
                </tr>
                <tr><th scope="row">Description</th>@foreach ($products as $p)<td class="cmp-desc">{{ \Illuminate\Support\Str::limit($p->summary, 160) }}</td>@endforeach</tr>
                <tr><th scope="row">Brand</th>@foreach ($products as $p)<td>{{ $p->brand?->name ?? '—' }}</td>@endforeach</tr>
                <tr><th scope="row">SKU</th>@foreach ($products as $p)<td>{{ $p->isVariable() ? $p->variants->pluck('sku')->implode(', ') : ($p->sku ?: '—') }}</td>@endforeach</tr>
                <tr>
                    <th scope="row">Availability</th>
                    @foreach ($products as $p)<td><span @class(['avail', 'out' => ! $p->inStock()])>{{ $p->inStock() ? 'In stock' : 'Out of stock' }}</span></td>@endforeach
                </tr>
                <tr><th scope="row">Origin</th>@foreach ($products as $p)<td>{{ $p->origin ?? '—' }}</td>@endforeach</tr>
                <tr><th scope="row">Skin type</th>@foreach ($products as $p)<td>{{ $p->skin_type ?? '—' }}</td>@endforeach</tr>
                <tr><th scope="row">Size / Volume</th>@foreach ($products as $p)<td>{{ $p->attributeValues->where('attribute.slug', 'size')->pluck('value')->implode(', ') ?: ($p->volume ?? '—') }}</td>@endforeach</tr>
                <tr><th scope="row">Color</th>@foreach ($products as $p)<td>{{ $p->attributeValues->where('attribute.slug', 'color')->pluck('value')->implode(', ') ?: '—' }}</td>@endforeach</tr>
            </tbody>
        </table>
    </div>
@endif
