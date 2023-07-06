<?php

namespace FluxErp\Actions\CalendarEvent;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\CalendarEvent;
use Illuminate\Support\Facades\Validator;

class DeleteCalendarEvent implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:calendar_events,id',
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'calendar-event.delete';
    }

    public static function description(): string|null
    {
        return 'delete calendar event';
    }

    public static function models(): array
    {
        return [CalendarEvent::class];
    }

    public function execute(): bool|null
    {
        return CalendarEvent::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
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
