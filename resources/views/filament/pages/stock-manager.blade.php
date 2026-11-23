<x-filament-panels::page>
    <style>
        .sb-sm-bar{display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;margin-bottom:16px}
        .sb-sm-pills{display:flex;gap:8px;flex-wrap:wrap}
        .sb-sm-pill{padding:7px 15px;border-radius:999px;font-size:13px;font-weight:600;border:1px solid #CBD5E1;background:#FFFFFF;color:#1E293B;cursor:pointer;transition:all .2s}
        .sb-sm-pill.on{border-color:#0A449B;background:#0A449B;color:#FFFFFF}
        .sb-sm-search{min-width:220px;flex:0 1 280px;max-width:100%;padding:8px 14px;border-radius:12px;border:1px solid #CBD5E1;background:#FFFFFF;color:#0F172A;font-size:14px}
        .sb-sm-card{background:#FFFFFF;color:#0F172A;border:1px solid #E2E8F0;border-radius:16px;overflow-x:auto;-webkit-overflow-scrolling:touch}
        .sb-sm-tbl{width:100%;border-collapse:collapse;font-size:14px;color:#0F172A}
        .sb-sm-tbl thead tr{background:#F1F5F9;border-bottom:1px solid #E2E8F0;text-align:left;color:#0F172A}
        .sb-sm-tbl th{padding:12px 14px;font-weight:700;color:#1E293B;white-space:nowrap}
        .sb-sm-tbl td{padding:12px 14px;border-bottom:1px solid #F1F5F9;color:#1E293B}
        .sb-sm-sub{font-size:12px;color:#64748B}
        .sb-sm-ctrl{display:flex;align-items:center;gap:6px;flex-wrap:nowrap}
        .sb-sm-btn{width:30px;height:30px;border-radius:999px;border:1px solid #CBD5E1;background:#FFFFFF;color:#0F172A;font-weight:700;cursor:pointer;flex:none}
        .sb-sm-btn10{padding:5px 10px;border-radius:999px;border:1px solid #CBD5E1;background:#F8FAFC;color:#0F172A;font-size:12px;font-weight:600;cursor:pointer;flex:none}
        .sb-sm-save{padding:6px 14px;border-radius:999px;border:0;background:#0A449B;color:#FFFFFF;font-size:12px;font-weight:600;cursor:pointer;flex:none}
        .sb-sm-in{width:70px;padding:5px 8px;border-radius:10px;border:1px solid #CBD5E1;background:#FFFFFF;color:#0F172A;text-align:center}
        .dark .sb-sm-pill{background:#18181B;color:#F4F4F5;border-color:#3F3F46}
        .dark .sb-sm-pill.on{background:#F97316;border-color:#F97316;color:#FFFFFF}
        .dark .sb-sm-search,.dark .sb-sm-in{background:#18181B;color:#F4F4F5;border-color:#3F3F46}
        .dark .sb-sm-card{background:#18181B;color:#F4F4F5;border-color:#27272A}
        .dark .sb-sm-tbl thead tr{background:#27272A;border-color:#3F3F46}
        .dark .sb-sm-tbl th,.dark .sb-sm-tbl td{color:#F4F4F5;border-color:#27272A}
        .dark .sb-sm-sub{color:#A1A1AA}
        .dark .sb-sm-btn,.dark .sb-sm-btn10{background:#27272A;color:#F4F4F5;border-color:#52525B}
        @media (max-width:760px){
            .sb-sm-search{width:100%;flex:1 1 100%}
            .sb-sm-tbl thead{display:none}
            .sb-sm-tbl,.sb-sm-tbl tbody,.sb-sm-tbl tr,.sb-sm-tbl td{display:block;width:100%}
            .sb-sm-tbl tr{padding:12px 14px;border-bottom:1px solid #E2E8F0}
            .dark .sb-sm-tbl tr{border-color:#27272A}
            .sb-sm-tbl td{padding:4px 0;border:0;display:flex;justify-content:space-between;align-items:center;gap:10px}
            .sb-sm-tbl td[data-label]::before{content:attr(data-label);font-size:12px;font-weight:600;color:#64748B}
            .dark .sb-sm-tbl td[data-label]::before{color:#A1A1AA}
            .sb-sm-tbl td:first-child{display:block;padding-bottom:6px}
            .sb-sm-tbl td:last-child{padding-top:8px;justify-content:flex-start}
        }
    </style>

    <div class="sb-sm-bar">
        <div class="sb-sm-pills">
            @foreach (['all' => 'All Items', 'low' => 'Low Stock (1–5)', 'out' => 'Out of Stock (0)', 'unlimited' => 'Unlimited'] as $k => $label)
                <button type="button" wire:click="$set('filter', '{{ $k }}')" @class(['sb-sm-pill', 'on' => $filter === $k])>{{ $label }}</button>
            @endforeach
        </div>
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search product, variant, SKU…" class="sb-sm-search">
    </div>

    <div class="sb-sm-card">
        <table class="sb-sm-tbl">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Variation</th>
                    <th>SKU</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Stock Quantity (empty = ∞)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->rows as $row)
                    <tr wire:key="stock-{{ $row['key'] }}">
                        <td>
                            <div style="font-weight:700">{{ $row['product_name'] }}</div>
                            <div class="sb-sm-sub">{{ $row['category'] }}</div>
                        </td>
                        <td data-label="Variation">{{ $row['variant_label'] }}</td>
                        <td data-label="SKU" style="font-family:monospace;font-size:12px">{{ $row['sku'] }}</td>
                        <td data-label="Price" style="font-weight:600">{{ \App\Models\Product::taka((int) $row['price']) }}</td>
                        <td data-label="Status">
                            @if ($row['stock'] === null)
                                <span style="padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;background:#E2E8F0;color:#1E293B">Unlimited</span>
                            @elseif ($row['stock'] <= 0)
                                <span style="padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;background:#FEE2E2;color:#991B1B">Out of stock</span>
                            @elseif ($row['stock'] <= 5)
                                <span style="padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;background:#FFEDD5;color:#9A3412">Low ({{ $row['stock'] }})</span>
                            @else
                                <span style="padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;background:#DCFCE7;color:#166534">In stock ({{ $row['stock'] }})</span>
                            @endif
                        </td>
                        <td>
                            <div class="sb-sm-ctrl">
                                <button type="button" wire:click="bump('{{ $row['key'] }}', -1)" class="sb-sm-btn" aria-label="Decrease stock">−</button>
                                <input type="number" min="0" placeholder="∞" wire:model.defer="levels.{{ $row['key'] }}" class="sb-sm-in">
                                <button type="button" wire:click="bump('{{ $row['key'] }}', 1)" class="sb-sm-btn" aria-label="Increase stock">+</button>
                                <button type="button" wire:click="bump('{{ $row['key'] }}', 10)" class="sb-sm-btn10">+10</button>
                                <button type="button" wire:click="saveRow('{{ $row['key'] }}')" class="sb-sm-save">Save</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:28px;text-align:center" class="sb-sm-sub">No items match your filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
