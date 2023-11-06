<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\PushSubscription\UpsertPushSubscription;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\UpsertPushSubscriptionRequest;
use Illuminate\Http\JsonResponse;
use NotificationChannels\WebPush\PushSubscription;

class PushSubscriptionController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new PushSubscription();
    }

    public function upsert(UpsertPushSubscriptionRequest $request): JsonResponse
    {
        $pushSubscription = UpsertPushSubscription::make($request->validated())->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $pushSubscription,
            statusMessage: 'push subscription upserted'
        );
    }
}
