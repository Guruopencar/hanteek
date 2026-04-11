<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Models\Locale;
use App\Models\Translation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminTranslationController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Translation::query()
            ->when($request->locale, fn($q) => $q->where('locale', $request->locale))
            ->when($request->group, fn($q) => $q->where('group', $request->group))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where('key', 'like', '%'.$request->search.'%')
                ->orWhere('value', 'like', '%'.$request->search.'%'))
            ->orderBy('group')->orderBy('key');

        return response()->json(['success' => true, 'data' => $query->paginate(50)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'value'  => 'required|string',
            'status' => 'nullable|in:translated,untranslated,needs_review',
        ]);

        $translation = Translation::findOrFail($id);
        $translation->update([
            'value'  => $data['value'],
            'status' => $data['status'] ?? 'translated',
        ]);

        // Кеш очищається автоматично через model observer

        return $this->success($translation);
    }

    public function export(Request $request): JsonResponse
    {
        $locale = $request->locale ?? 'uk';

        $translations = Translation::where('locale', $locale)
            ->orderBy('group')->orderBy('key')
            ->get()
            ->groupBy('group')
            ->map(fn($items) => $items->pluck('value', 'key'));

        return $this->success($translations);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'locale'       => 'required|string|size:2',
            'translations' => 'required|array',
        ]);

        $count = 0;
        foreach ($request->translations as $group => $keys) {
            foreach ($keys as $key => $value) {
                Translation::updateOrCreate(
                    ['locale' => $request->locale, 'group' => $group, 'key' => $key],
                    ['value' => $value, 'status' => 'translated']
                );
                $count++;
            }
        }

        // Очистити весь кеш перекладів
        Cache::flush();

        return $this->success(['imported' => $count], "Імпортовано {$count} рядків");
    }

    public function addLocale(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'        => 'required|string|max:5|unique:locales,code',
            'name'        => 'required|string|max:100',
            'native_name' => 'required|string|max:100',
            'flag_emoji'  => 'nullable|string',
        ]);

        $locale = Locale::create([...$data, 'is_active' => true, 'is_default' => false]);

        return $this->created($locale, 'Мову додано');
    }
}
