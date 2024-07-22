<?php

namespace FluxErp\Livewire\Portal;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Carbon;
use TeamNiftyGmbH\Calendar\CalendarComponent;
use TeamNiftyGmbH\Calendar\Models\Calendar;
use TeamNiftyGmbH\Calendar\Models\CalendarEvent;

class Calendars extends CalendarComponent
{
    public function render(): Factory|View|Application
    {
        return view('flux::livewire.portal.calendar-view');
    }

    public function getCalendars(): array
    {
        return resolve_static(Calendar::class, 'query')
            ->where('is_public', true)
            ->get()
            ->map(function (Calendar $calendar) {
                return $calendar->toCalendarObject([
                    'permission' => 'reader',
                    'group' => 'public',
                    'resourceEditable' => false,
                ]);
            })
            ->toArray();
    }

    public function getEvents(array $info, array $calendarAttributes): array
    {
        $calendar = resolve_static(Calendar::class, 'query')->find($calendarAttributes['id']);

        return $calendar->calendarEvents()
            ->where(function ($query) use ($info) {
                $query->whereBetween('start', [
                    Carbon::parse($info['start']),
                    Carbon::parse($info['end']),
                ])
                    ->orWhereBetween('end', [
                        Carbon::parse($info['start']),
                        Carbon::parse($info['end']),
                    ]);
            })
            ->get()
            ->map(function (CalendarEvent $event) {
                $invited = $event->invites()
                    ->where('inviteable_type', auth()->user()->getMorphClass())
                    ->where('inviteable_id', auth()->user()->getKey())
                    ->exists();

                return $event->toCalendarEventObject(['is_editable' => false, 'is_attending' => $invited]);
            })
            ?->toArray();
    }

    public function attendEvent($eventId)
    {
        $event = resolve_static(CalendarEvent::class, 'query')->find($eventId);

        $event->invites()->create([
            'inviteable_type' => auth()->user()->getMorphClass(),
            'inviteable_id' => auth()->user()->getKey(),
            'status' => 'accepted',
        ]);
    }

    public function notAttendEvent($eventId)
    {
        $event = resolve_static(CalendarEvent::class, 'query')->find($eventId);

        $event->invites()
            ->where('inviteable_type', auth()->user()->getMorphClass())
            ->where('inviteable_id', auth()->user()->getKey())
            ->delete();
    }
}
