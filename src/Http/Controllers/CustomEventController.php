<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateCustomEventRequest;
use FluxErp\Http\Requests\DispatchCustomEventRequest;
use FluxErp\Models\CustomEvent;
use FluxErp\Services\CustomEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

/**
 * @deprecated
 */
class CustomEventController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new CustomEvent();
    }

    public function create(CreateCustomEventRequest $request, CustomEventService $customEventService): JsonResponse
    {
        $customEvent = $customEventService->create($request->validated());

        return ResponseHelper::createResponseFromBase(statusCode: 201, data: $customEvent);
    }

    public function update(Request $request, CustomEventService $customEventService): JsonResponse
    {
        $response = $customEventService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, CustomEventService $customEventService): JsonResponse
    {
        $response = $customEventService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function dispatchCustomEvent(DispatchCustomEventRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $customEvent = CustomEvent::query()
            ->where('name', $validated['event'])
            ->first();

        $response = Event::dispatch(
            $customEvent->name,
            array_key_exists('payload', $validated) ? $validated['payload'] : $customEvent
        );

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $response,
            statusMessage: 'Event \'' . $customEvent->name . '\' dispatched.'
        );
    }
}
