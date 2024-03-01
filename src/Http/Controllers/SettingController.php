<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Setting;
use FluxErp\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SettingController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(Setting::class);
    }

    public function create(Request $request, SettingService $settingService): JsonResponse
    {
        $setting = $settingService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $setting,
            statusMessage: 'setting created'
        );
    }

    public function getUserSettings(Request $request): JsonResponse
    {
        $userSettings = $request->user()->settings;

        return ResponseHelper::createResponseFromBase(statusCode: 200, data: $userSettings);
    }

    /**
     * @throws ValidationException
     */
    public function update(Request $request, SettingService $settingService): JsonResponse
    {
        $response = $settingService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
