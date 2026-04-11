<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Models\AdminLog;
use App\Models\Contract;
use App\Models\News;
use App\Models\NewsTranslation;
use App\Models\Review;
use App\Models\Vacancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminContentController extends ApiController
{
    public function contracts(Request $request): JsonResponse
    {
        $contracts = Contract::with(['owner.profile', 'developer.profile'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $contracts]);
    }

    public function vacancies(Request $request): JsonResponse
    {
        $vacancies = Vacancy::with(['owner.profile', 'project'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $vacancies]);
    }

    public function reviews(Request $request): JsonResponse
    {
        $reviews = Review::with(['reviewer.profile', 'reviewee.profile', 'contract'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $reviews]);
    }

    public function deleteReview(Review $review): JsonResponse
    {
        AdminLog::record('delete_review', 'Review', $review->id, null, $review->toArray());
        $review->update(['status' => 'deleted', 'is_published' => false]);
        return $this->success(null, 'Відгук видалено');
    }

    public function hideReview(Request $request, Review $review): JsonResponse
    {
        $request->validate(['reason' => 'required|string']);
        $review->update([
            'status'           => 'hidden',
            'is_published'     => false,
            'moderated_by'     => auth()->id(),
            'moderated_at'     => now(),
            'moderation_note'  => $request->reason,
        ]);
        AdminLog::record('hide_review', 'Review', $review->id, $request->reason);
        return $this->success(null, 'Відгук приховано');
    }

    // News CRUD
    public function index(Request $request): JsonResponse
    {
        $news = News::with('author.profile')
            ->orderByDesc('created_at')
            ->paginate(20);
        return response()->json(['success' => true, 'data' => $news]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'slug'           => 'required|string|unique:news,slug',
            'cover_image'    => 'nullable|url',
            'translations'   => 'required|array',
            'translations.uk.title'   => 'required|string',
            'translations.uk.content' => 'required|string',
            'translations.en.title'   => 'nullable|string',
            'translations.en.content' => 'nullable|string',
        ]);

        $news = News::create([
            'slug'      => $data['slug'],
            'cover_image' => $data['cover_image'] ?? null,
            'author_id' => auth()->id(),
            'status'    => 'draft',
        ]);

        foreach ($data['translations'] as $locale => $translation) {
            if (!empty($translation['title'])) {
                NewsTranslation::create([
                    'news_id' => $news->id,
                    'locale'  => $locale,
                    'title'   => $translation['title'],
                    'excerpt' => $translation['excerpt'] ?? null,
                    'content' => $translation['content'] ?? '',
                ]);
            }
        }

        return $this->created($news->load('translations'));
    }

    public function update(Request $request, News $news): JsonResponse
    {
        $data = $request->validate([
            'cover_image'  => 'nullable|url',
            'translations' => 'nullable|array',
        ]);

        $news->update(collect($data)->except('translations')->toArray());

        if (!empty($data['translations'])) {
            foreach ($data['translations'] as $locale => $translation) {
                NewsTranslation::updateOrCreate(
                    ['news_id' => $news->id, 'locale' => $locale],
                    $translation
                );
            }
        }

        return $this->success($news->fresh('translations'));
    }

    public function destroy(News $news): JsonResponse
    {
        $news->delete();
        return $this->success(null, 'Новину видалено');
    }

    public function publishNews(News $news): JsonResponse
    {
        $news->update(['status' => 'published', 'published_at' => now()]);
        return $this->success(null, 'Новину опубліковано');
    }
}
