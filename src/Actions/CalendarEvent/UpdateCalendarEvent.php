<?php

namespace FluxErp\Actions\CalendarEvent;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CalendarEvent;
use FluxErp\Rulesets\CalendarEvent\UpdateCalendarEventRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateCalendarEvent extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateCalendarEventRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [CalendarEvent::class];
    }

    public function performAction(): Model
    {
        $calendarEvent = app(CalendarEvent::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $calendarEvent->fill($this->data);
        $calendarEvent->save();

        SyncCalendarEventInvites::make($this->data)->execute();

        return $calendarEvent->withoutRelations()->fresh();
    }
}
