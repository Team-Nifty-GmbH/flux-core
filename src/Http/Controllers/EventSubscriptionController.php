<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\EventSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventSubscriptionController extends Controller
{
    public function getEvents(): JsonResponse
    {
        $events = array_keys(app('events')->getRawListeners());

        return ResponseHelper::createResponseFromBase(statusCode: 200, data: $events);
    }

    public function getUserSubscriptions(Request $request): JsonResponse
    {
        $subscriptions = resolve_static(EventSubscription::class, 'query')
            ->where('subscribable_type', $request->user()->getMorphClass())
            ->where('subscribable_id', $request->user()->id)
            ->orderBy('channel')
            ->get();

        return ResponseHelper::createResponseFromBase(statusCode: 200, data: $subscriptions);
    }
}
