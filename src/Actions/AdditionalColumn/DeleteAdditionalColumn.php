<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\AdditionalColumn;
use Illuminate\Support\Facades\Validator;

class DeleteAdditionalColumn implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:additional_columns,id',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'additional-column.delete';
    }

    public static function description(): string|null
    {
        return 'delete additional column';
    }

    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    public function execute()
    {
        $additionalColumn = AdditionalColumn::query()
            ->whereKey($this->data['id'])
            ->first();

        $additionalColumn->modelValues()->delete();

        return $additionalColumn->delete();
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
