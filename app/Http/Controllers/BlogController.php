<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $catSlug = trim((string) $request->query('category', ''));
        $tagSlug = trim((string) $request->query('tag', ''));
        $viewMode = $request->query('view') === 'list' ? 'list' : 'grid';

        $query = BlogPost::published()->with(['category', 'author', 'tags']);

        $activeCategory = null;
        if ($catSlug !== '') {
            $activeCategory = BlogCategory::where('slug', $catSlug)->first();
            if ($activeCategory) {
                $query->where('blog_category_id', $activeCategory->id);
            }
        }

        $activeTag = null;
        if ($tagSlug !== '') {
            $activeTag = BlogTag::where('slug', $tagSlug)->first();
            if ($activeTag) {
                $query->whereHas('tags', fn ($tq) => $tq->where('blog_tags.id', $activeTag->id));
            }
        }

        if ($q !== '') {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
            $query->where(fn ($sq) => $sq->where('title', 'like', $like)->orWhere('excerpt', 'like', $like)->orWhere('body', 'like', $like));
        }

        $posts = $query->paginate(9)->withQueryString();

        $categories = BlogCategory::orderBy('sort_order')
            ->withCount(['posts' => fn ($pq) => $pq->where('is_published', true)])
            ->get();

        $tags = BlogTag::whereHas('posts', fn ($pq) => $pq->where('is_published', true))
            ->withCount(['posts' => fn ($pq) => $pq->where('is_published', true)])
            ->orderByDesc('posts_count')
            ->get();

        return view('blog.index', compact('posts', 'categories', 'tags', 'activeCategory', 'activeTag', 'q', 'viewMode'));
    }

    public function show(BlogPost $post): View
    {
        abort_unless($post->is_published, 404);

        $post->increment('views');
        $post->load([
            'category',
            'author',
            'tags',
            'products' => fn ($q) => $q->where('is_active', true)->with(['brand', 'variants.values.attribute']),
            'approvedComments',
        ]);

        $related = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->when($post->blog_category_id, fn ($q) => $q->orderByRaw('CASE WHEN blog_category_id = ? THEN 0 ELSE 1 END', [$post->blog_category_id]))
            ->with('category')
            ->take(3)
            ->get();

        return view('blog.show', compact('post', 'related'));
    }

    public function storeComment(Request $request, BlogPost $post): RedirectResponse
    {
        abort_unless($post->is_published, 404);

        $user = $request->user();
        $data = $request->validate([
            'name' => [$user ? 'nullable' : 'required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'body' => ['required', 'string', 'min:4', 'max:1500'],
        ]);

        $post->comments()->create([
            'user_id' => $user?->id,
            'name' => $user ? $user->name : trim($data['name']),
            'email' => $user ? $user->email : ($data['email'] ?? null),
            'body' => trim($data['body']),
            'is_approved' => false,
        ]);

        return redirect()->to(route('blog.show', $post).'#comments')
            ->with('comment_status', 'Thank you! Your comment has been submitted and will appear once approved.');
    }
}
