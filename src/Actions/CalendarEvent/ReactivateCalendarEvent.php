<?php

namespace FluxErp\Actions\CalendarEvent;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\CalendarEvent;
use FluxErp\Rulesets\CalendarEvent\ReactivateCalendarEventRuleset;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ReactivateCalendarEvent extends FluxAction
{
    public static function models(): array
    {
        return [CalendarEvent::class];
    }

    protected function getRulesets(): string|array
    {
        return ReactivateCalendarEventRuleset::class;
    }

    public function performAction(): ?bool
    {
        $event = resolve_static(CalendarEvent::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $confirmOption = Arr::pull($this->data, 'confirm_option');
        $originalStart = Arr::pull($this->data, 'original_start');

        if ($confirmOption !== 'this') {
            $event->fill($this->getData());
        }

        return match ($confirmOption) {
            'this' => $event->fill(
                [
                    'cancelled' => array_diff(
                        $event->cancelled ?: [],
                        [Carbon::parse($originalStart)->toDateTimeString()]
                    ),
                ]
            )
                ->save(),
            default => $event->fill(
                [
                    'cancelled_at' => null,
                    'cancelled_by' => null,
                ]
            )
                ->save(),
        };
    }

    protected function validateData(): void
    {
        parent::validateData();

        $event = resolve_static(CalendarEvent::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        if ($this->getData('confirm_option') === 'this') {
            $event->start = $this->getData('original_start');
        }

        if (! $event->isCancelled) {
            throw ValidationException::withMessages([
                'id' => ['Calendar event is not cancelled.'],
            ])
                ->errorBag('reactivateCalendarEvent');
        }
    }
}
