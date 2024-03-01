<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\CalendarEvent;
use FluxErp\Services\CalendarEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarEventController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(CalendarEvent::class);
    }

    public function create(Request $request, CalendarEventService $calendarEventService): JsonResponse
    {
        $calendarEvent = $calendarEventService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $calendarEvent,
            statusMessage: 'calendar event created'
        );
    }

    public function update(Request $request, CalendarEventService $calendarEventService): JsonResponse
    {
        $response = $calendarEventService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, CalendarEventService $calendarEventService): JsonResponse
    {
        $response = $calendarEventService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
