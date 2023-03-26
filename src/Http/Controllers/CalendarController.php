<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateCalendarRequest;
use FluxErp\Models\Calendar;
use FluxErp\Services\CalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Calendar();
    }

    public function create(CreateCalendarRequest $request, CalendarService $calendarService): JsonResponse
    {
        $calendar = $calendarService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $calendar,
            statusMessage: 'calendar created'
        );
    }

    public function update(Request $request, CalendarService $calendarService): JsonResponse
    {
        $response = $calendarService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, CalendarService $calendarService): JsonResponse
    {
        $response = $calendarService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
