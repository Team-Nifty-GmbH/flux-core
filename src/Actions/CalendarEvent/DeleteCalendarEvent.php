<?php

namespace FluxErp\Actions\CalendarEvent;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CalendarEvent;
use FluxErp\Rulesets\CalendarEvent\DeleteCalendarEventRuleset;

class DeleteCalendarEvent extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return DeleteCalendarEventRuleset::class;
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
