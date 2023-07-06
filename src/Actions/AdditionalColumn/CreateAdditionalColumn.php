<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateAdditionalColumnRequest;
use FluxErp\Models\AdditionalColumn;
use Illuminate\Support\Facades\Validator;

class CreateAdditionalColumn implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateAdditionalColumnRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'additional-column.create';
    }

    public static function description(): string|null
    {
        return 'create additional column';
    }

    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    public function execute(): AdditionalColumn
    {
        if (! ($this->data['validations'] ?? false)) {
            $this->data['validations'] = null;
        }

        if (! ($this->data['values'] ?? false)) {
            $this->data['values'] = null;
        }

        $additionalColumn = new AdditionalColumn($this->data);
        $additionalColumn->save();

        return $additionalColumn;
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
