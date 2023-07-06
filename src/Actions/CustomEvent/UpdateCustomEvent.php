<?php

namespace FluxErp\Actions\CustomEvent;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateCustomEventRequest;
use FluxErp\Models\CustomEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateCustomEvent implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateCustomEventRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'custom-event.update';
    }

    public static function description(): string|null
    {
        return 'update custom event';
    }

    public static function models(): array
    {
        return [CustomEvent::class];
    }

    public function execute(): Model
    {
        $customEvent = CustomEvent::query()
            ->whereKey($this->data['id'])
            ->first();

        $customEvent->fill($this->data);
        $customEvent->save();

        return $customEvent->withoutRelations()->fresh();
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
