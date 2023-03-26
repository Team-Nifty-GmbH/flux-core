<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateSettingRequest;
use FluxErp\Http\Requests\UpdateSettingRequest;
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
        $this->model = new Setting();
    }

    public function create(CreateSettingRequest $request, SettingService $settingService): JsonResponse
    {
        $setting = $settingService->create($request->validated());

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
    public function update(UpdateSettingRequest $request, SettingService $settingService): JsonResponse
    {
        $response = $settingService->update($request->validated());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
