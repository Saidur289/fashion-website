<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Products, prices, SKUs, stock and descriptions taken from the live safebuystore.com.
 * Images live in public/images/products. Details are in database/seeders/data/product-details.php.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $details = require __DIR__.'/data/product-details.php';

        $categories = [
            // name, group, image
            ['Sunscreen', Category::GROUP_BEAUTY, 'christian-dean-sun-cream-1.webp'],
            ['Serum', Category::GROUP_BEAUTY, 'skin1004-sun-serum-1.webp'],
            ['Moisturizer', Category::GROUP_BEAUTY, 'beaute-glutathione-cream-1.webp'],
            ['Facewash', Category::GROUP_BEAUTY, 'faceshop-rice-cleanser-150-1.webp'],
            ['Smart Watch', Category::GROUP_GADGET, 'joyroom-fc2-watch-1.webp'],
            ['Earbuds', Category::GROUP_GADGET, 'anker-a30i-earbuds-1.webp'],
            ['Power Bank', Category::GROUP_GADGET, 'hoco-j160b-powerbank-1.webp'],
            ['Wireless Charging', Category::GROUP_GADGET, 'hoco-cw55-dock-1.webp'],
        ];

        $ids = [];
        foreach ($categories as $i => [$name, $group, $image]) {
            $ids[$name] = Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'group' => $group, 'image' => 'images/products/'.$image, 'sort_order' => $i],
            )->id;
        }

        $brands = ['Christian Dean', 'Beaute', 'SKIN1004', 'The Face Shop', 'Missha', '3W Clinic', 'JOYROOM', 'Anker', 'Hoco'];
        $brandIds = [];
        foreach ($brands as $i => $name) {
            $brandIds[$name] = Brand::updateOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'sort_order' => $i])->id;
        }

        // Filter / variation attributes.
        $color = Attribute::updateOrCreate(['slug' => 'color'], ['name' => 'Color', 'type' => 'color', 'sort_order' => 0]);
        $size = Attribute::updateOrCreate(['slug' => 'size'], ['name' => 'Size', 'type' => 'text', 'sort_order' => 1]);
        $colors = ['Black' => '#111827', 'Space Grey' => '#6B7280'];
        foreach (array_keys($colors) as $i => $name) {
            AttributeValue::updateOrCreate(['attribute_id' => $color->id, 'slug' => Str::slug($name)], ['value' => $name, 'color' => $colors[$name], 'sort_order' => $i]);
        }
        foreach (['30ml', '40ml', '45ml', '50ml', '70ml', '100ml', '150ml'] as $i => $name) {
            AttributeValue::updateOrCreate(['attribute_id' => $size->id, 'slug' => Str::slug($name)], ['value' => $name, 'sort_order' => $i]);
        }
        $valueId = fn (Attribute $a, string $v) => AttributeValue::where('attribute_id', $a->id)->where('slug', Str::slug($v))->value('id');

        // Featured and best-seller are shop-owner picks (set from the admin panel later), not sales figures.
        $products = [
            // name, category, brand, price, old price, image base, new arrival, trending, featured, best seller
            ['Christian Dean Secret Tone Up Sun Cream SPF50 70ml', 'Sunscreen', 'Christian Dean', 420, 950, 'christian-dean-sun-cream', true, false, true, true],
            ['Beaute Melasma X Glutathione Brightening Tone Up Cream 45ml', 'Moisturizer', 'Beaute', 699, 1200, 'beaute-glutathione-cream', true, false, false, true],
            ['Beaute Melasma-X 3D Whitening Clinic Renewal Cream 40ml', 'Moisturizer', 'Beaute', 799, 1050, 'beaute-3d-renewal-cream', true, false, false, false],
            ['SKIN 1004 - Madagascar Centella Hyalu-Cica Water-Fit Sun Serum SPF 50 PA 50ml', 'Serum', 'SKIN1004', 1400, 2600, 'skin1004-sun-serum', true, false, true, true],
            ['The Face Shop Rice Water Bright Foaming Cleanser', 'Facewash', 'The Face Shop', 735, 1050, 'faceshop-rice-cleanser', true, true, false, false],
            ['Missha All Around Safe Block Soft Finish Sun Milk SPF50+ PA+++ 70ml', 'Sunscreen', 'Missha', 1190, 1850, 'missha-sun-milk', false, true, false, true],
            ['Skin 1004 Madagascar Centella Poremizing Fresh Ampoule 30ml', 'Serum', 'SKIN1004', 849, 1200, 'skin1004-poremizing-ampoule', false, true, false, false],
            ['3W Clinic Intensive UV Sunblock Cream SPF50+ PA+++ 70ml', 'Sunscreen', '3W Clinic', 449, 950, '3w-clinic-uv-sunblock', false, true, false, true],
            ['JOYROOM JR-FC2 Pro Classic Series Smart Watch – Space Grey', 'Smart Watch', 'JOYROOM', 3550, 3950, 'joyroom-fc2-watch', false, true, true, true],
            ['Anker Soundcore A30i Lipstick-Shaped Smart Noise Cancelling Earbuds – Black', 'Earbuds', 'Anker', 3500, 4500, 'anker-a30i-earbuds', false, true, true, false],
            ['Hoco CW55 Magnetic Charging Dock for iWatch', 'Wireless Charging', 'Hoco', 900, 1300, 'hoco-cw55-dock', false, true, false, false],
            ['Hoco J160B PD20W 20000mAh Magnetic Power Bank', 'Power Bank', 'Hoco', 2250, 3500, 'hoco-j160b-powerbank', false, true, false, true],
        ];

        $made = [];
        foreach ($products as $i => [$name, $category, $brand, $price, $old, $img, $isNew, $isTrending, $isFeatured, $isBest]) {
            $d = $details[$img];
            $variants = $d['variants'] ?? [];
            // Variable products show the first variant's images on cards.
            $cardImg = $variants ? $variants[0]['img'] : $img;

            $product = Product::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'category_id' => $ids[$category],
                    'brand_id' => $brandIds[$brand],
                    'name' => $name,
                    'sku' => $d['sku'] ?? null,
                    'summary' => $d['summary'],
                    'description' => $d['description'] ?? null,
                    'how_to_use' => $d['how_to_use'] ?? null,
                    'specs' => $d['specs'] ?? null,
                    // All beauty brands here are Korean (listed under K-Beauty on the old site).
                    'origin' => in_array($category, ['Sunscreen', 'Serum', 'Moisturizer', 'Facewash'], true) ? 'South Korea' : null,
                    'skin_type' => $d['skin'] ?? null,
                    'price' => $price,
                    'old_price' => $old,
                    'stock' => $variants ? null : ($d['stock'] ?? null),
                    'image' => "images/products/{$cardImg}-1.webp",
                    'image_hover' => "images/products/{$cardImg}-2.webp",
                    'is_new_arrival' => $isNew,
                    'is_trending' => $isTrending,
                    'is_featured' => $isFeatured,
                    'is_best_seller' => $isBest,
                    'sort_order' => $i,
                    'meta_description' => Str::limit($d['summary'], 155),
                ],
            );

            // Gallery: both photos of the product (and of every variant).
            $product->images()->delete();
            $paths = $variants
                ? collect($variants)->flatMap(fn ($v) => ["images/products/{$v['img']}-1.webp", "images/products/{$v['img']}-2.webp"])->all()
                : ["images/products/{$img}-1.webp", "images/products/{$img}-2.webp"];
            foreach ($paths as $n => $path) {
                $product->images()->create(['path' => $path, 'sort_order' => $n]);
            }

            // Tag (from the old site) + filter attributes.
            $tag = Tag::updateOrCreate(['slug' => Str::slug($d['tag'])], ['name' => $d['tag']]);
            $product->tags()->sync([$tag->id]);

            $attr = [];
            if (! empty($d['size'])) {
                $attr[] = $valueId($size, $d['size']);
            }
            if (! empty($d['color'])) {
                $attr[] = $valueId($color, $d['color']);
            }

            $product->variants()->delete();
            foreach ($variants as $n => $v) {
                $variant = $product->variants()->create([
                    'sku' => $v['sku'], 'price' => $v['price'], 'old_price' => $v['old'], 'stock' => $v['stock'],
                    'image' => "images/products/{$v['img']}-1.webp", 'sort_order' => $n,
                ]);
                $variant->values()->sync([$valueId($size, $v['size'])]);
                $attr[] = $valueId($size, $v['size']);
            }
            $product->attributeValues()->sync(array_unique(array_filter($attr)));

            $made[$img] = $product;
        }

        // Shop-owner picks for "Frequently bought together": a simple day routine per product group.
        $together = [
            'christian-dean-sun-cream' => ['faceshop-rice-cleanser', 'skin1004-poremizing-ampoule'],
            'missha-sun-milk' => ['faceshop-rice-cleanser', 'beaute-glutathione-cream'],
            '3w-clinic-uv-sunblock' => ['faceshop-rice-cleanser', 'beaute-3d-renewal-cream'],
            'skin1004-sun-serum' => ['skin1004-poremizing-ampoule', 'faceshop-rice-cleanser'],
            'skin1004-poremizing-ampoule' => ['skin1004-sun-serum', 'faceshop-rice-cleanser'],
            'beaute-glutathione-cream' => ['beaute-3d-renewal-cream', 'christian-dean-sun-cream'],
            'beaute-3d-renewal-cream' => ['beaute-glutathione-cream', '3w-clinic-uv-sunblock'],
            'faceshop-rice-cleanser' => ['skin1004-poremizing-ampoule', 'missha-sun-milk'],
            'joyroom-fc2-watch' => ['hoco-cw55-dock', 'hoco-j160b-powerbank'],
            'anker-a30i-earbuds' => ['hoco-j160b-powerbank', 'joyroom-fc2-watch'],
            'hoco-cw55-dock' => ['hoco-j160b-powerbank', 'joyroom-fc2-watch'],
            'hoco-j160b-powerbank' => ['hoco-cw55-dock', 'anker-a30i-earbuds'],
        ];
        DB::table('related_products')->delete();
        foreach ($together as $from => $list) {
            foreach ($list as $n => $to) {
                DB::table('related_products')->insert([
                    'product_id' => $made[$from]->id, 'related_id' => $made[$to]->id, 'type' => 'bought_together', 'sort_order' => $n,
                ]);
            }
        }
    }
}
