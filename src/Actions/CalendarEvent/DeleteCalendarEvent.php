<?php

namespace FluxErp\Actions\CalendarEvent;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\CalendarEvent;

class DeleteCalendarEvent extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:calendar_events,id',
        ];
    }

    public static function models(): array
    {
        return [CalendarEvent::class];
    }

    public function execute(): ?bool
    {
        return CalendarEvent::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
