<?php

namespace FluxErp\Actions\CalendarEvent;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\CalendarEvent;
use FluxErp\Rulesets\CalendarEvent\DeleteCalendarEventRuleset;

class DeleteCalendarEvent extends FluxAction
{
    protected function getRulesets(): string|array
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
                    [Carbon::parse(data_get($this->data, 'original_start'))->toDateTimeString()]
                ),
            ])->save(),
            'future' => $event->fill([
                'repeat_end' => Carbon::parse(data_get($this->data, 'original_start'))
                    ->subSecond()
                    ->toDateTimeString(),
            ])->save(),
            default => $event->delete()
        };
    }
}
