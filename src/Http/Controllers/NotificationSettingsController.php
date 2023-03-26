<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Services\NotificationSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationSettingsController extends Controller
{
    public function update(Request $request, NotificationSettingsService $notificationSettingsService): JsonResponse
    {
        $response = $notificationSettingsService->update($request->all(), true);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function updateUserNotifications(
        Request $request,
        NotificationSettingsService $notificationSettingsService): JsonResponse
    {
        $response = $notificationSettingsService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
