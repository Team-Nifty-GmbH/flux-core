<?php

namespace FluxErp\Actions\CustomEvent;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateCustomEventRequest;
use FluxErp\Models\CustomEvent;
use Illuminate\Support\Facades\Validator;

class CreateCustomEvent implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateCustomEventRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'custom-event.create';
    }

    public static function description(): string|null
    {
        return 'create custom event';
    }

    public static function models(): array
    {
        return [CustomEvent::class];
    }

    public function execute(): CustomEvent
    {
        $customEvent = new CustomEvent($this->data);
        $customEvent->save();

        return $customEvent;
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
