<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index()
    {
        $posts = Post::published()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('pages.blog.index', compact('posts'));
    }

    public function show(string $slug)
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        $seoDescription = $this->buildSeoDescription($post);

        $seoOgImage = null;
        if ($post->featured_image) {
            $seoOgImage = url(Storage::disk('public')->url($post->featured_image));
        }

        $relatedPosts = Post::published()
            ->where('id', '!=', $post->id)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        return view('pages.blog.show', compact('post', 'seoDescription', 'seoOgImage', 'relatedPosts'));
    }

    private function buildSeoDescription(Post $post): string
    {
        $raw = trim((string) ($post->excerpt ?? ''));
        if ($raw === '') {
            $raw = trim(preg_replace('/\s+/', ' ', strip_tags($post->content)));
        }

        $seoDescription = Str::limit($raw, 160, '');
        if ($seoDescription === '') {
            $seoDescription = Str::limit(trim(strip_tags($post->title)), 160, '');
        }

        return $seoDescription;
    }
}
