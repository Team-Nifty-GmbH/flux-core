<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateEventSubscriptionRequest;
use FluxErp\Http\Requests\UpdateEventSubscriptionRequest;
use FluxErp\Models\EventSubscription;
use FluxErp\Services\EventSubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventSubscriptionController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new EventSubscription();
    }

    public function getEvents(): JsonResponse
    {
        $events = array_keys(app('events')->getRawListeners());

        return ResponseHelper::createResponseFromBase(statusCode: 200, data: $events);
    }

    public function getUserSubscriptions(Request $request): JsonResponse
    {
        $subscriptions = EventSubscription::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('event')
            ->get();

        return ResponseHelper::createResponseFromBase(statusCode: 200, data: $subscriptions);
    }

    public function create(CreateEventSubscriptionRequest $request,
        EventSubscriptionService $eventSubscriptionService): JsonResponse
    {
        $response = $eventSubscriptionService->create($request->validated());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(UpdateEventSubscriptionRequest $request,
        EventSubscriptionService $eventSubscriptionService): JsonResponse
    {
        $response = $eventSubscriptionService->update($request->validated());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, EventSubscriptionService $eventSubscriptionService): JsonResponse
    {
        $response = $eventSubscriptionService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
