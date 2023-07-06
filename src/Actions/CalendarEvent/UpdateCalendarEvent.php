<?php

namespace FluxErp\Actions\CalendarEvent;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateCalendarEventRequest;
use FluxErp\Models\CalendarEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateCalendarEvent implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateCalendarEventRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'calendar-event.update';
    }

    public static function description(): string|null
    {
        return 'update calendar event';
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
