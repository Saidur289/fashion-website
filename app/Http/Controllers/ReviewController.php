<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Reviews from verified buyers only (a delivered order containing the product). */
class ReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $user = $request->user();
        $back = route('product.show', $product).'#reviews';

        $order = $user->deliveredOrderWith($product->id);
        if (! $order) {
            return redirect($back)->with('review_error', 'Only customers who received this product can review it.');
        }
        if (Review::where('product_id', $product->id)->where('user_id', $user->id)->exists()) {
            return redirect($back)->with('review_error', 'You have already reviewed this product. Thank you!');
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'body' => ['required', 'string', 'min:10', 'max:2000'],
            'photos' => ['nullable', 'array', 'max:3'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], ['body.min' => 'Please write at least a few words (10 characters).', 'photos.max' => 'You can add up to 3 photos.']);

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'rating' => $data['rating'],
            'body' => $data['body'],
            'is_approved' => (bool) config('safebuy.reviews_auto_approve'),
        ]);

        foreach ($request->file('photos', []) as $file) {
            if ($path = $this->savePhoto($file->getRealPath())) {
                $review->images()->create(['path' => $path]);
            }
        }

        if ($review->is_approved) {
            $product->refreshRating();
        }

        return redirect($back)->with('review_status', $review->is_approved
            ? 'Thank you! Your review is published.'
            : 'Thank you! Your review will appear after the shop checks it.');
    }

    /**
     * Re-encode the upload as a WebP (max 1200px) in public/uploads/reviews.
     * Re-encoding drops anything hidden in the file (including phone GPS data).
     */
    private function savePhoto(string $source): ?string
    {
        $data = @file_get_contents($source);
        $img = $data ? @imagecreatefromstring($data) : false;
        if (! $img) {
            return null;
        }
        $w = imagesx($img);
        $h = imagesy($img);
        $scale = min(1, 1200 / max($w, $h));
        if ($scale < 1) {
            $resized = imagescale($img, (int) round($w * $scale), (int) round($h * $scale));
            imagedestroy($img);
            $img = $resized;
        }

        $dir = public_path('uploads/reviews');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = Str::random(24).'.webp';
        imagewebp($img, $dir.'/'.$name, 82);
        imagedestroy($img);

        return 'uploads/reviews/'.$name;
    }
}
