<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Models\Calendar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CalendarEventController extends Controller
{
    public function getEvents(Request $request): JsonResponse
    {
        $info = $request->input('info');
        $calendarAttributes = $request->input('calendar');

        if (data_get($calendarAttributes, 'hasNoEvents')) {
            return response()->json();
        }

        if (data_get($calendarAttributes, 'modelType')
            && data_get($calendarAttributes, 'isVirtual', false)
        ) {
            $events = $this->getCalendarEventsFromModelType(
                data_get($calendarAttributes, 'modelType'),
                data_get($info, 'start'),
                data_get($info, 'end'),
                $calendarAttributes
            );

            return response()->json($events);
        }

        $calendar = resolve_static(Calendar::class, 'query')
            ->whereKey(data_get($calendarAttributes, 'id'))
            ->first();

        if (! $calendar) {
            return response()->json();
        }

        $calendarEvents = $calendar->calendarEvents()
            ->whereNull('repeat')
            ->where(function (Builder $query) use ($info): void {
                $query->where('start', '<=', Carbon::parse(data_get($info, 'end')))
                    ->where('end', '>=', Carbon::parse(data_get($info, 'start')));
            })
            ->with('invited', fn (Builder $query): Builder => $query->withPivot('status'))
            ->get()
            ->merge(
                $calendar->invitesCalendarEvents()
                    ->addSelect('calendar_events.*')
                    ->addSelect('inviteables.status')
                    ->addSelect('inviteables.model_calendar_id AS calendar_id')
                    ->whereIn('inviteables.status', ['accepted', 'maybe'])
                    ->get()
                    ->each(fn (Model $event) => $event->is_invited = true)
            );

        $events = $calendarEvents->map(function (Model $event) use ($calendarAttributes, $calendar): array {
            return $event->toCalendarEventObject([
                'is_editable' => data_get($calendarAttributes, 'permission') !== 'reader',
                'invited' => $this->getInvited($event),
                'is_repeatable' => data_get($calendar, 'has_repeatable_events', false),
                'has_repeats' => ! is_null(data_get($event, 'repeat')),
            ]);
        })->toArray();

        return response()->json($events);
    }

    protected function getCalendarEventsFromModelType(
        string $modelType,
        string $start,
        string $end,
        array $calendarAttributes
    ): array {
        return resolve_static(morphed_model($modelType), 'query')
            ->inTimeframe($start, $end, $calendarAttributes)
            ->get()
            ->map(fn (Model $model) => $model->toCalendarEvent(['start' => $start, 'end' => $end]))
            ->toArray();
    }

    protected function getInvited(Model $event): array
    {
        return $event->invitedModels()
            ->map(function (Model $inviteable) {
                return [
                    'id' => $inviteable->getKey(),
                    'label' => $inviteable->getLabel(),
                    'pivot' => $inviteable->pivot,
                ];
            })
            ->toArray();
    }
}
