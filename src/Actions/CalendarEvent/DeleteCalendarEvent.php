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
        $event = resolve_static(CalendarEvent::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        return match ($this->data['confirm_option']) {
            'this' => $event->fill([
                'excluded' => array_merge(
                    $event->excluded ?: [],
                    [$event->start->toDateTimeString()]
                ),
            ])->save(),
            'future' => $event->fill([
                'repeat_end' => $event->start->subSecond()->toDateTimeString(),
            ])->save(),
            default => $event->delete()
        };
    }
}
