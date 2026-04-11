<?php

namespace App\Http\Controllers\Api\News;

use App\Http\Controllers\Api\ApiController;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $locale = $request->header('Accept-Language', 'uk');

        $news = News::published()
            ->with(['translations' => fn($q) => $q->where('locale', $locale)])
            ->orderByDesc('published_at')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data'    => $news->map(fn($n) => [
                'id'           => $n->id,
                'slug'         => $n->slug,
                'cover_image'  => $n->cover_image,
                'published_at' => $n->published_at?->toISOString(),
                'views_count'  => $n->views_count,
                'title'        => $n->translation($locale)?->title,
                'excerpt'      => $n->translation($locale)?->excerpt,
            ]),
            'meta' => [
                'current_page' => $news->currentPage(),
                'last_page'    => $news->lastPage(),
                'total'        => $news->total(),
            ],
        ]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $locale = $request->header('Accept-Language', 'uk');

        $news = News::where('slug', $slug)->published()->firstOrFail();
        $news->increment('views_count');
        $news->load('translations');

        $translation = $news->translation($locale);

        return $this->success([
            'id'           => $news->id,
            'slug'         => $news->slug,
            'cover_image'  => $news->cover_image,
            'published_at' => $news->published_at?->toISOString(),
            'views_count'  => $news->views_count,
            'title'        => $translation?->title,
            'excerpt'      => $translation?->excerpt,
            'content'      => $translation?->content,
        ]);
    }
}
