<?php

namespace FluxErp\Actions\Calendar;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateCalendarRequest;
use FluxErp\Models\Calendar;
use Illuminate\Support\Facades\Validator;

class CreateCalendar implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateCalendarRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'calendar.create';
    }

    public static function description(): string|null
    {
        return 'create calendar';
    }

    public static function models(): array
    {
        return [Calendar::class];
    }

    public function execute(): Calendar
    {
        $calendar = new Calendar($this->data);
        $calendar->save();

        return $calendar;
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
