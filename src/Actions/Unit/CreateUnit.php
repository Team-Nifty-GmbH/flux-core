<?php

namespace FluxErp\Actions\Unit;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateUnitRequest;
use FluxErp\Models\Unit;
use Illuminate\Support\Facades\Validator;

class CreateUnit implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateUnitRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'unit.create';
    }

    public static function description(): string|null
    {
        return 'create unit';
    }

    public static function models(): array
    {
        return [Unit::class];
    }

    public function execute(): Unit
    {
        $unit = new Unit($this->data);
        $unit->save();

        return $unit;
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
