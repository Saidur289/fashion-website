<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\ActivityLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => ProductResource::streamCsv()),

            Action::make('importCsv')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->form([
                    FileUpload::make('csv')
                        ->label('CSV File (columns: name, slug, sku, category, brand, price, old_price, stock, origin, skin_type, volume, image)')
                        ->disk('local')
                        ->directory('imports')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $path = Storage::disk('local')->path($data['csv']);
                    if (! is_file($path)) {
                        return;
                    }

                    $fh = fopen($path, 'r');
                    $header = fgetcsv($fh);
                    if (! $header) {
                        fclose($fh);

                        return;
                    }
                    $header = array_map(fn ($h) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))), $header);

                    $imported = 0;
                    $defaultCat = Category::firstOrCreate(['slug' => 'uncategorized'], ['name' => 'Uncategorized', 'group' => Category::GROUP_BEAUTY]);

                    while (($row = fgetcsv($fh)) !== false) {
                        if (count($row) < 2) {
                            continue;
                        }
                        $item = array_combine($header, array_pad($row, count($header), null));
                        $name = trim((string) ($item['name'] ?? ''));
                        if ($name === '') {
                            continue;
                        }
                        $slug = trim((string) ($item['slug'] ?? '')) ?: Str::slug($name);

                        $categoryId = $defaultCat->id;
                        if (! empty($item['category'])) {
                            $cat = Category::firstOrCreate(
                                ['slug' => Str::slug($item['category'])],
                                ['name' => trim($item['category']), 'group' => Category::GROUP_BEAUTY]
                            );
                            $categoryId = $cat->id;
                        }

                        $brandId = null;
                        if (! empty($item['brand'])) {
                            $brand = Brand::firstOrCreate(
                                ['slug' => Str::slug($item['brand'])],
                                ['name' => trim($item['brand'])]
                            );
                            $brandId = $brand->id;
                        }

                        Product::updateOrCreate(
                            ['slug' => $slug],
                            [
                                'name' => $name,
                                'sku' => $item['sku'] ?: null,
                                'category_id' => $categoryId,
                                'brand_id' => $brandId,
                                'price' => max(0, (int) ($item['price'] ?? 0)),
                                'old_price' => isset($item['old_price']) && $item['old_price'] !== '' ? (int) $item['old_price'] : null,
                                'stock' => isset($item['stock']) && $item['stock'] !== '' ? (int) $item['stock'] : null,
                                'origin' => $item['origin'] ?: null,
                                'skin_type' => $item['skin_type'] ?: null,
                                'volume' => $item['volume'] ?: null,
                                'image' => $item['image'] ?: 'images/brand/safebuy-logo.webp',
                                'is_active' => isset($item['is_active']) ? (bool) $item['is_active'] : true,
                                'is_featured' => isset($item['is_featured']) ? (bool) $item['is_featured'] : false,
                            ],
                        );
                        $imported++;
                    }
                    fclose($fh);
                    Storage::disk('local')->delete($data['csv']);

                    ActivityLog::record('created', "Imported {$imported} products from CSV");
                    Notification::make()->title("Imported / updated {$imported} products")->success()->send();
                }),

            CreateAction::make(),
        ];
    }
}
