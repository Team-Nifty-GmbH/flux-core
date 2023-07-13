<?php

namespace FluxErp\Actions\CalendarEvent;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateCalendarEventRequest;
use FluxErp\Models\CalendarEvent;
use Illuminate\Database\Eloquent\Model;

class UpdateCalendarEvent extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateCalendarEventRequest())->rules();
    }

    public static function models(): array
    {
        return [CalendarEvent::class];
    }

    public function execute(): Model
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
