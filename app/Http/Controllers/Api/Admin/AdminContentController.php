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
        $news = News::with(['author.profile', 'translations'])
            ->orderByDesc('created_at')
            ->paginate(20);
        return response()->json(['success' => true, 'data' => $news]);
    }

    public function store(Request $request): JsonResponse
    {
        $translations = $this->decodeTranslations($request);

        $data = $request->validate([
            'slug'         => 'required|string|unique:news,slug',
            'cover_image'  => 'nullable|url',
            'cover'        => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ]);

        $this->validateTranslations($translations);

        $coverUrl = $data['cover_image'] ?? null;
        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store('news', 'public');
            $coverUrl = asset('storage/' . $path);
        }

        $news = News::create([
            'slug'        => $data['slug'],
            'cover_image' => $coverUrl,
            'author_id'   => auth()->id(),
            'status'      => 'draft',
        ]);

        foreach ($translations as $locale => $translation) {
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
        $translations = $this->decodeTranslations($request);

        $data = $request->validate([
            'cover_image' => 'nullable|url',
            'cover'       => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ]);

        $updateFields = [];
        if (array_key_exists('cover_image', $data)) {
            $updateFields['cover_image'] = $data['cover_image'];
        }
        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store('news', 'public');
            $updateFields['cover_image'] = asset('storage/' . $path);
        }
        if ($updateFields) {
            $news->update($updateFields);
        }

        if ($translations) {
            $this->validateTranslations($translations, false);
            foreach ($translations as $locale => $translation) {
                if (empty($translation['title'])) continue;
                NewsTranslation::updateOrCreate(
                    ['news_id' => $news->id, 'locale' => $locale],
                    [
                        'title'   => $translation['title'],
                        'excerpt' => $translation['excerpt'] ?? null,
                        'content' => $translation['content'] ?? '',
                    ]
                );
            }
        }

        return $this->success($news->fresh('translations'));
    }

    private function decodeTranslations(Request $request): array
    {
        $raw = $request->input('translations');
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($raw) ? $raw : [];
    }

    private function validateTranslations(array $translations, bool $ukRequired = true): void
    {
        if ($ukRequired) {
            abort_unless(
                !empty($translations['uk']['title']) && !empty($translations['uk']['content']),
                422,
                'Українська версія (title, content) обов’язкова'
            );
        }
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
