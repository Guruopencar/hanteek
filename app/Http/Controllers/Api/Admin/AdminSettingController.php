<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Models\AdminLog;
use App\Models\FeatureFlag;
use App\Models\PlatformSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSettingController extends ApiController
{
    public function index(): JsonResponse
    {
        $settings = PlatformSetting::orderBy('group')->orderBy('key')->get();
        return $this->success($settings);
    }

    public function update(Request $request, string $key): JsonResponse
    {
        $request->validate(['value' => 'required']);

        $old = PlatformSetting::where('key', $key)->value('value');
        PlatformSetting::set($key, $request->value);
        AdminLog::record('update_setting', null, null, "Змінено: {$key}", ['value' => $old], ['value' => $request->value]);

        return $this->success(null, 'Налаштування збережено');
    }

    public function flags(): JsonResponse
    {
        return $this->success(FeatureFlag::orderBy('key')->get());
    }

    public function updateFlag(Request $request, string $key): JsonResponse
    {
        $request->validate(['is_enabled' => 'required|boolean']);

        if ($request->is_enabled) {
            FeatureFlag::enable($key);
        } else {
            FeatureFlag::disable($key);
        }

        AdminLog::record('update_feature_flag', null, null, "Feature flag: {$key} = " . ($request->is_enabled ? 'on' : 'off'));

        return $this->success(null, 'Feature flag оновлено');
    }
}
