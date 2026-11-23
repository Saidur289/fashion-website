<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\ProductVariant;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;

class StockManager extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.stock-manager';

    public string $search = '';

    public string $filter = 'all'; // all | low | out | unlimited

    /** @var array<string, string|int|null> */
    public array $levels = [];

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.stock');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageCatalog() ?? false;
    }

    public function mount(): void
    {
        $this->loadLevels();
    }

    public function loadLevels(): void
    {
        $this->levels = [];
        foreach (Product::with('variants')->get() as $product) {
            if ($product->variants->isEmpty()) {
                $this->levels['p_'.$product->id] = $product->stock === null ? '' : (string) $product->stock;
            } else {
                foreach ($product->variants as $variant) {
                    $this->levels['v_'.$variant->id] = $variant->stock === null ? '' : (string) $variant->stock;
                }
            }
        }
    }

    public function getRowsProperty(): array
    {
        $search = mb_strtolower(trim($this->search));
        $rows = [];

        $products = Product::with(['category', 'variants.values'])->orderBy('name')->get();
        foreach ($products as $p) {
            if ($p->variants->isEmpty()) {
                $rows[] = [
                    'key' => 'p_'.$p->id,
                    'type' => 'product',
                    'id' => $p->id,
                    'product_name' => $p->name,
                    'variant_label' => 'Simple product',
                    'category' => $p->category?->name ?? '—',
                    'sku' => $p->sku ?: '—',
                    'price' => $p->price,
                    'stock' => $p->stock,
                ];
            } else {
                foreach ($p->variants as $v) {
                    $rows[] = [
                        'key' => 'v_'.$v->id,
                        'type' => 'variant',
                        'id' => $v->id,
                        'product_name' => $p->name,
                        'variant_label' => $v->label() ?: 'Variant #'.$v->id,
                        'category' => $p->category?->name ?? '—',
                        'sku' => $v->sku ?: ($p->sku ?: '—'),
                        'price' => $v->price,
                        'stock' => $v->stock,
                    ];
                }
            }
        }

        return array_values(array_filter($rows, function (array $row) use ($search) {
            if ($search !== '') {
                $hay = mb_strtolower($row['product_name'].' '.$row['variant_label'].' '.$row['sku'].' '.$row['category']);
                if (! str_contains($hay, $search)) {
                    return false;
                }
            }

            return match ($this->filter) {
                'low' => $row['stock'] !== null && $row['stock'] > 0 && $row['stock'] <= 5,
                'out' => $row['stock'] !== null && $row['stock'] <= 0,
                'unlimited' => $row['stock'] === null,
                default => true,
            };
        }));
    }

    public function saveRow(string $key): void
    {
        abort_unless(self::canAccess(), 403);

        $raw = $this->levels[$key] ?? '';
        $value = ($raw === '' || $raw === null) ? null : max(0, (int) $raw);

        [$type, $id] = explode('_', $key, 2);

        if ($type === 'p') {
            $model = Product::findOrFail($id);
            $old = $model->stock;
            $model->update(['stock' => $value]);
            ActivityLog::record('stock_adjusted', "Stock for “{$model->name}” changed from ".($old ?? '∞').' to '.($value ?? '∞'), $model);
        } else {
            $model = ProductVariant::with('product', 'values')->findOrFail($id);
            $old = $model->stock;
            $model->update(['stock' => $value]);
            ActivityLog::record('stock_adjusted', "Stock for “{$model->product?->name} ({$model->label()})” changed from ".($old ?? '∞').' to '.($value ?? '∞'), $model->product);
        }

        $this->levels[$key] = $value === null ? '' : (string) $value;
        Notification::make()->title('Stock updated')->success()->send();
    }

    public function bump(string $key, int $delta): void
    {
        $current = $this->levels[$key] ?? '';
        $base = $current === '' ? 0 : (int) $current;
        $this->levels[$key] = (string) max(0, $base + $delta);
        $this->saveRow($key);
    }
}
