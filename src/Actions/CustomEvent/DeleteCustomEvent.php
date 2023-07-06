<?php

namespace FluxErp\Actions\CustomEvent;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\CustomEvent;
use Illuminate\Support\Facades\Validator;

class DeleteCustomEvent implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:custom_events,id',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'custom-event.delete';
    }

    public static function description(): string|null
    {
        return 'delete custom event';
    }

    public static function models(): array
    {
        return [CustomEvent::class];
    }

    public function execute()
    {
        return CustomEvent::query()
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
