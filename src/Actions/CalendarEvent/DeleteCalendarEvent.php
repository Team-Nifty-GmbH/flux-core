<?php

namespace FluxErp\Actions\CalendarEvent;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CalendarEvent;
use FluxErp\Rulesets\CalendarEvent\DeleteCalendarEventRuleset;

class DeleteCalendarEvent extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteCalendarEventRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [CalendarEvent::class];
    }

    public function performAction(): ?bool
    {
        return app(CalendarEvent::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
