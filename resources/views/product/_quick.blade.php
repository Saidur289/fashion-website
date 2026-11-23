<div class="quick">
    <div class="qleft">
        @include('product._gallery', ['product' => $product, 'quick' => true])
        <a class="btn blue wide" href="{{ route('product.show', $product) }}" data-view>View details <x-icon name="arrow-right" size="16" /></a>
    </div>
    <div class="qright">
        @include('product._buybox', ['product' => $product, 'quick' => true])
    </div>
</div>
