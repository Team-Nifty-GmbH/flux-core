<?php

namespace FluxErp\Actions\Calendar;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Calendar;
use Illuminate\Support\Facades\Validator;

class DeleteCalendar implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:calendars,id',
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'calendar.delete';
    }

    public static function description(): string|null
    {
        return 'delete calendar';
    }

    public static function models(): array
    {
        return [Calendar::class];
    }

    public function execute(): bool|null
    {
        return Calendar::query()
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
