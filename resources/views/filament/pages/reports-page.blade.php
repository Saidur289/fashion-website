@php
    use App\Models\Product;
    $s = $this->summary;
@endphp
<x-filament-panels::page>
    <style>
        .sb-rp-bar{display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;background:#FFFFFF;color:#0F172A;border:1px solid #E2E8F0;border-radius:16px;padding:16px;margin-bottom:20px}
        .sb-rp-btn{padding:7px 14px;border-radius:999px;border:1px solid #CBD5E1;background:#F8FAFC;color:#1E293B;font-size:13px;font-weight:600;cursor:pointer}
        .sb-rp-btn:hover{border-color:#0A449B;color:#0A449B}
        .sb-rp-date{padding:6px 10px;border-radius:10px;border:1px solid #CBD5E1;background:#FFFFFF;color:#0F172A;font-size:13px}
        .sb-rp-lbl{font-size:13px;color:#475569;display:inline-flex;align-items:center;gap:6px}
        .sb-rp-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px;margin-bottom:22px}
        .sb-rp-kpi{background:#FFFFFF;color:#0F172A;border:1px solid #E2E8F0;border-radius:16px;padding:18px}
        .sb-rp-kpi-t{font-size:12px;color:#64748B;text-transform:uppercase;font-weight:700}
        .sb-rp-kpi-s{font-size:12px;color:#64748B;margin-top:2px}
        .sb-rp-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:18px}
        .sb-rp-card{background:#FFFFFF;color:#0F172A;border:1px solid #E2E8F0;border-radius:16px;overflow-x:auto}
        .sb-rp-ch{padding:14px 18px;border-bottom:1px solid #E2E8F0;font-weight:700;color:#0A449B}
        .sb-rp-tbl{width:100%;border-collapse:collapse;font-size:14px;color:#0F172A}
        .sb-rp-tbl thead tr{background:#F1F5F9;text-align:left;border-bottom:1px solid #E2E8F0}
        .sb-rp-tbl th{padding:10px 16px;font-weight:700;color:#1E293B}
        .sb-rp-tbl td{padding:10px 16px;border-bottom:1px solid #F1F5F9;color:#1E293B}
        .dark .sb-rp-bar,.dark .sb-rp-kpi,.dark .sb-rp-card{background:#18181B;color:#F4F4F5;border-color:#27272A}
        .dark .sb-rp-btn{background:#27272A;color:#F4F4F5;border-color:#52525B}
        .dark .sb-rp-date{background:#27272A;color:#F4F4F5;border-color:#52525B}
        .dark .sb-rp-lbl,.dark .sb-rp-kpi-t,.dark .sb-rp-kpi-s{color:#A1A1AA}
        .dark .sb-rp-ch{color:#93C5FD;border-color:#27272A}
        .dark .sb-rp-tbl thead tr{background:#27272A;border-color:#3F3F46}
        .dark .sb-rp-tbl th,.dark .sb-rp-tbl td{color:#F4F4F5;border-color:#27272A}
    </style>

    <div class="sb-rp-bar">
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            @foreach (['today' => 'Today', '7d' => 'Last 7 Days', '30d' => 'Last 30 Days', 'month' => 'This Month', 'year' => 'This Year'] as $k => $label)
                <button type="button" wire:click="setRange('{{ $k }}')" class="sb-rp-btn">{{ $label }}</button>
            @endforeach
        </div>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <label class="sb-rp-lbl">From <input type="date" wire:model.live="from" class="sb-rp-date"></label>
            <label class="sb-rp-lbl">To <input type="date" wire:model.live="until" class="sb-rp-date"></label>
        </div>
    </div>

    <div class="sb-rp-kpis">
        <div class="sb-rp-kpi">
            <div class="sb-rp-kpi-t">Gross Sales</div>
            <div style="font-size:24px;font-weight:700;color:#2563EB;margin-top:4px">{{ Product::taka($s['gross_sales']) }}</div>
            <div class="sb-rp-kpi-s">{{ $s['active_orders'] }} active orders</div>
        </div>
        <div class="sb-rp-kpi">
            <div class="sb-rp-kpi-t">Delivered Revenue</div>
            <div style="font-size:24px;font-weight:700;color:#16A34A;margin-top:4px">{{ Product::taka($s['delivered_sales']) }}</div>
            <div class="sb-rp-kpi-s">Avg order: {{ Product::taka($s['avg_order']) }}</div>
        </div>
        <div class="sb-rp-kpi">
            <div class="sb-rp-kpi-t">Cancellation Rate</div>
            <div style="font-size:24px;font-weight:700;color:#DC2626;margin-top:4px">{{ $s['cancel_rate'] }}%</div>
            <div class="sb-rp-kpi-s">{{ $s['cancelled_count'] }} of {{ $s['total_orders'] }} orders</div>
        </div>
        <div class="sb-rp-kpi">
            <div class="sb-rp-kpi-t">Return Rate</div>
            <div style="font-size:24px;font-weight:700;color:#F97316;margin-top:4px">{{ $s['return_rate'] }}%</div>
            <div class="sb-rp-kpi-s">{{ $s['returned_count'] }} of {{ $s['total_orders'] }} orders</div>
        </div>
    </div>

    <div class="sb-rp-grid">
        <div class="sb-rp-card">
            <div class="sb-rp-ch">Sales by Product</div>
            <table class="sb-rp-tbl">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th style="text-align:right">Qty</th>
                        <th style="text-align:right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->byProduct as $row)
                        <tr>
                            <td style="font-weight:500">{{ $row['name'] }}</td>
                            <td style="text-align:right">{{ $row['qty_sold'] }}</td>
                            <td style="text-align:right;font-weight:600">{{ Product::taka((int) $row['revenue']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="padding:20px;text-align:center" class="sb-rp-kpi-s">No sales in this date range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="sb-rp-card">
            <div class="sb-rp-ch">Sales by Category</div>
            <table class="sb-rp-tbl">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th style="text-align:right">Qty</th>
                        <th style="text-align:right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->byCategory as $row)
                        <tr>
                            <td style="font-weight:500">{{ $row['category'] }}</td>
                            <td style="text-align:right">{{ $row['qty_sold'] }}</td>
                            <td style="text-align:right;font-weight:600">{{ Product::taka((int) $row['revenue']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="padding:20px;text-align:center" class="sb-rp-kpi-s">No sales in this date range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
