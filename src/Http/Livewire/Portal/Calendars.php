<?php

namespace FluxErp\Http\Livewire\Portal;

use FluxErp\Http\Livewire\Features\Calendar\Calendar;
use FluxErp\Models\Calendar as CalendarModel;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class Calendars extends Calendar
{
    public function mount(bool $showPersonalCalendar = true): void
    {
        parent::mount();

        $this->calendarEvent['calendar_id'] = null;
    }

    public function getCalendars(): void
    {
        $client = auth()->user()?->contact?->client;
        $setting = $client?->settings()
            ->where('key', 'customerPortal')
            ->first()
            ?->toArray();

        $this->calendars = [];

        $calendars = CalendarModel::query()
            ->whereIntegerInRaw('id', ($setting['settings']['calendars'] ?? []))
            ->get()
            ->toArray();

        $this->toFlatTree($calendars, 'calendars');
    }

    public function invitedEvents(): void
    {
        $invited = $this->mapEvents(auth()->user()
            ->calendarEventInvites()
            ->whereNot('status', 'declined')
            ->whereIntegerNotInRaw('calendar_id', $this->activeCalendars)
            ->where(function (Builder $query) {
                return $query
                    ->whereBetween('calendar_events.starts_at', [$this->gridStartsAt, $this->gridEndsAt])
                    ->orWhereBetween('calendar_events.ends_at', [$this->gridStartsAt, $this->gridEndsAt]);
            })
            ->get())
            ->toArray();

        $invited = Arr::keyBy($invited, 'id');

        $this->events = array_merge($this->events, $invited);
    }

    /**
     * @param string|array|null $status
     */
    public function getInvites(string|array|null $status = null): void
    {
        $query = auth()->user()
            ->calendarEventInvites()
            ->whereIntegerNotInRaw('calendar_id', $this->activeCalendars);

        if (is_null($status)) {
            $query->whereNull('status');
        } else {
            $status = (array) $status;
            $query->whereIn('status', $status);
        }

        $this->invites = $query->orderBy('starts_at')->get()->toArray();
    }

    public function render(): Factory|View
    {
        return view('flux::livewire.features.calendar.calendar')
            ->layout('flux::components.layouts.portal');
    }
}
