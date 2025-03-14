<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends BaseController
{
    public function getUserSettings(Request $request): JsonResponse
    {
        $userSettings = $request->user()->settings;

        return ResponseHelper::createResponseFromBase(statusCode: 200, data: $userSettings);
    }
}
