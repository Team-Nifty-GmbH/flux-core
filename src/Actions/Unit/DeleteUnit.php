<?php

namespace FluxErp\Actions\Unit;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Unit;
use Illuminate\Support\Facades\Validator;

class DeleteUnit implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:units,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'unit.delete';
    }

    public static function description(): string|null
    {
        return 'delete unit';
    }

    public static function models(): array
    {
        return [Unit::class];
    }

    public function execute(): bool|null
    {
        return Unit::query()
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
