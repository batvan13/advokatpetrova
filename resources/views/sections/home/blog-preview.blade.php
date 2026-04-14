@php
    use App\Models\Post;
    $previewPosts = Post::published()
        ->orderByDesc('published_at')
        ->orderByDesc('id')
        ->limit(3)
        ->get();
@endphp

@if ($previewPosts->isNotEmpty())
<section class="bg-[#1E2A3B] py-20 lg:py-24">
    <div class="mx-auto w-full max-w-[1400px] px-6 lg:px-12">

        <h2 class="mb-12 text-center font-cormorant text-3xl font-normal italic text-petrova-gold lg:text-4xl xl:text-[2.5rem]">
            Последно от нашия блог
        </h2>

        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($previewPosts as $post)
                @include('partials.blog-card', ['post' => $post])
            @endforeach
        </div>

    </div>
</section>
@endif
