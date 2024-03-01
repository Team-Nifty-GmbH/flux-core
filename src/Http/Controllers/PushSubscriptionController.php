<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\PushSubscription\UpsertPushSubscription;
use FluxErp\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use NotificationChannels\WebPush\PushSubscription;

class PushSubscriptionController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(PushSubscription::class);
    }

    public function upsert(Request $request): JsonResponse
    {
        $pushSubscription = UpsertPushSubscription::make($request->all())
            ->validate()
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $pushSubscription,
            statusMessage: 'push subscription upserted'
        );
    }
}
