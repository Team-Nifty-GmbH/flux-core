<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Http\Requests\CalendarEventRequest;
use Illuminate\Http\JsonResponse;
use Livewire\Livewire;

class CalendarEventController extends Controller
{
    public function getEvents(CalendarEventRequest $request): JsonResponse
    {
        $info = $request->input('info');
        $calendarAttributes = $request->input('calendar');
        $componentSnapshot = $request->input('componentSnapshot');

        [$component, $context] = Livewire::fromSnapshot($componentSnapshot);

        $events = $component->getEvents($info, $calendarAttributes);

        return response()->json($events);
    }
}
