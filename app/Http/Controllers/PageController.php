<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Page;
use App\Models\Product;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        $page = Page::where('slug', 'about')->where('is_active', true)->first();
        $team = TeamMember::where('is_active', true)->orderBy('sort_order')->take(4)->get();

        return view('pages.about', compact('page', 'team'));
    }

    public function team(): View
    {
        $team = TeamMember::where('is_active', true)->orderBy('sort_order')->get();

        return view('pages.team', compact('team'));
    }

    public function faq(Request $request): View
    {
        $category = trim((string) $request->query('category', ''));
        $q = trim((string) $request->query('q', ''));

        $query = Faq::where('is_active', true)->orderBy('sort_order')->orderBy('id');

        if ($category !== '' && array_key_exists($category, Faq::CATEGORIES)) {
            $query->where('category', $category);
        }
        if ($q !== '') {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
            $query->where(fn ($sq) => $sq->where('question', 'like', $like)->orWhere('answer', 'like', $like));
        }

        $faqs = $query->get();
        $grouped = $faqs->groupBy('category');

        return view('pages.faq', compact('faqs', 'grouped', 'category', 'q'));
    }

    public function policy(string $slug): View
    {
        $page = Page::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('pages.policy', compact('page'));
    }

    public function verifyProduct(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $matches = collect();

        if ($q !== '') {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
            $matches = Product::active()
                ->with(['brand', 'category', 'variants'])
                ->where(fn ($sq) => $sq
                    ->where('sku', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhereHas('variants', fn ($vq) => $vq->where('sku', 'like', $like))
                    ->orWhereHas('brand', fn ($bq) => $bq->where('name', 'like', $like)))
                ->take(12)
                ->get();
        }

        return view('pages.verify-product', compact('q', 'matches'));
    }
}
