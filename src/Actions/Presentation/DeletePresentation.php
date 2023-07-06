<?php

namespace FluxErp\Actions\Presentation;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Presentation;
use Illuminate\Support\Facades\Validator;

class DeletePresentation implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:presentations,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'presentation.delete';
    }

    public static function description(): string|null
    {
        return 'delete presentation';
    }

    public static function models(): array
    {
        return [Presentation::class];
    }

    public function execute()
    {
        return Presentation::query()
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
