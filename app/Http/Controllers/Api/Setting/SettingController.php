<?php

namespace App\Http\Controllers\Api\Setting;

use App\Http\Controllers\Api\ApiController;
use App\Models\Locale;
use App\Models\Translation;
use App\Models\PlatformSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends ApiController
{
    public function public(): JsonResponse
    {
        return $this->success([
            'platform_name'     => PlatformSetting::get('platform_name', 'Hunteek'),
            'platform_currency' => PlatformSetting::get('platform_currency', 'USD'),
            'commission_rate'   => PlatformSetting::get('commission_rate', 5),
            'review_deadline'   => PlatformSetting::get('review_deadline_days', 14),
        ]);
    }

    public function locales(): JsonResponse
    {
        return $this->success(Locale::getActive());
    }

    public function translations(string $locale): JsonResponse
    {
        $groups = ['auth', 'nav', 'buttons', 'home', 'contracts',
                   'projects', 'wallet', 'reviews', 'profile',
                   'rating', 'messages', 'support', 'errors'];

        $translations = [];
        foreach ($groups as $group) {
            $translations[$group] = Translation::getGroup($locale, $group);
        }

        return $this->success($translations);
    }
}
