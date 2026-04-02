<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Service;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $pages = [
            route('home'),
            route('about'),
            route('services'),
            route('blog'),
            route('contacts'),
            route('practice'),
            route('terms'),
            route('cookie_policy'),
            route('privacy'),
        ];

        foreach (Service::active()->ordered()->get() as $service) {
            $pages[] = route('services.show', $service->slug);
        }

        foreach (Post::published()->orderByDesc('published_at')->orderByDesc('id')->get() as $post) {
            $pages[] = route('blog.show', $post->slug);
        }

        return response()
            ->view('sitemap', ['pages' => $pages])
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
