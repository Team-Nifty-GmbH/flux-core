<?php

namespace FluxErp\Actions\CalendarEvent;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\CalendarEvent;
use FluxErp\Rulesets\CalendarEvent\CancelCalendarEventRuleset;

class CancelCalendarEvent extends FluxAction
{
    public static function models(): array
    {
        return [CalendarEvent::class];
    }

    protected function getRulesets(): string|array
    {
        return CancelCalendarEventRuleset::class;
    }

    public function performAction(): ?bool
    {
        $event = resolve_static(CalendarEvent::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        return match ($this->data['confirm_option']) {
            'this' => $event->fill(
                [
                    'cancelled' => array_merge(
                        $event->cancelled ?: [],
                        [Carbon::parse(data_get($this->data, 'original_start'))->toDateTimeString()]
                    ),
                ]
            )
                ->save(),
            default => $event->fill(
                [
                    'cancelled_at' => Carbon::now()->toDateTimeString(),
                    'cancelled_by' => auth()->user()
                        ? auth()->user()->getMorphClass() . ':' . auth()->user()->getKey()
                        : null,
                ]
            )
                ->save(),
        };
    }
}
