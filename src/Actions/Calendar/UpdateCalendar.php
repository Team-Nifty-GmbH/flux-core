<?php

namespace FluxErp\Actions\Calendar;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateCalendarRequest;
use FluxErp\Models\Calendar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateCalendar implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateCalendarRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'calendar.update';
    }

    public static function description(): string|null
    {
        return 'update calendar';
    }

    public static function models(): array
    {
        return [Calendar::class];
    }

    public function execute(): Model
    {
        $calendar = Calendar::query()
            ->whereKey($this->data['id'])
            ->first();

        $calendar->fill($this->data);
        $calendar->save();

        return $calendar->withoutRelations()->fresh();
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
