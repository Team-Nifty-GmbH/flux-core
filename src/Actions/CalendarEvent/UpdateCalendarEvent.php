<?php

namespace FluxErp\Actions\CalendarEvent;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateCalendarEventRequest;
use FluxErp\Models\CalendarEvent;
use Illuminate\Database\Eloquent\Model;

class UpdateCalendarEvent extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateCalendarEventRequest())->rules();
    }

    public static function models(): array
    {
        return [CalendarEvent::class];
    }

    public function performAction(): Model
    {
        $calendarEvent = CalendarEvent::query()
            ->whereKey($this->data['id'])
            ->first();

        $calendarEvent->fill($this->data);
        $calendarEvent->save();

        SyncCalendarEventInvites::make($this->data)->execute();

        return $calendarEvent->withoutRelations()->fresh();
    }
}
