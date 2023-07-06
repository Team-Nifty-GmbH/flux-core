<?php

namespace FluxErp\Actions\CalendarEvent;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateCalendarEventRequest;
use FluxErp\Models\CalendarEvent;
use Illuminate\Support\Facades\Validator;

class CreateCalendarEvent implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateCalendarEventRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'calendar-event.create';
    }

    public static function description(): string|null
    {
        return 'create calendar event';
    }

    public static function models(): array
    {
        return [CalendarEvent::class];
    }

    public function execute(): CalendarEvent
    {
        $calendarEvent = new CalendarEvent($this->data);
        $calendarEvent->save();

        SyncCalendarEventInvites::make(array_merge($this->data, ['id' => $calendarEvent->id]))->execute();

        return $calendarEvent;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
