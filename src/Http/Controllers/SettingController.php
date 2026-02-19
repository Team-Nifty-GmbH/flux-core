<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\SettingsRequest;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SettingController extends BaseController
{
    public function getSettings(SettingsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $groups = $validated['group'] ?? $validated['groups'] ?? null;

        $settings = DB::table('settings')
            ->when($groups, fn (Builder $query) => $query->whereIn('group', Arr::wrap($groups)))
            ->get()
            ->toArray();

        return ResponseHelper::createResponseFromBase(200, data: $settings);
    }

    public function getUserSettings(Request $request): JsonResponse
    {
        $userSettings = $request->user()->settings;

        return ResponseHelper::createResponseFromBase(statusCode: 200, data: $userSettings);
    }
}
