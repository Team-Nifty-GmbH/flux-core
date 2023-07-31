<?php

namespace FluxErp\Actions\CalendarEvent;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CalendarEvent;

class DeleteCalendarEvent extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:calendar_events,id',
        ];
    }

    public static function models(): array
    {
        return [CalendarEvent::class];
    }

    public function performAction(): ?bool
    {
        return CalendarEvent::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
