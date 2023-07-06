<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateAdditionalColumnRequest;
use FluxErp\Models\AdditionalColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateAdditionalColumn implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateAdditionalColumnRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'additional-column.update';
    }

    public static function description(): string|null
    {
        return 'update additional column';
    }

    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    public function execute(): Model
    {
        if ($this->data['values'] ?? false) {
            $this->data['validations'] = null;
        } elseif (array_key_exists('values', $this->data)) {
            $this->data['values'] = null;
        }

        if ($this->data['validations'] ?? false) {
            $this->data['values'] = null;
        } elseif (array_key_exists('validations', $this->data)) {
            $this->data['validations'] = null;
        }

        $additionalColumn = AdditionalColumn::query()
            ->whereKey($this->data['id'])
            ->first();

        $additionalColumn->fill($this->data);
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

        $additionalColumn = AdditionalColumn::query()
            ->whereKey($this->data['id'])
            ->first();

        if ($additionalColumn->values !== null
            && $this->data['values'] !== null
            && $additionalColumn->modelValues()->whereNotIn('meta.value', $this->data['values'])->exists()
        ) {
            throw ValidationException::withMessages([
                'values' => [__('Models with differing values exist')],
            ]);
        }

        return $this;
    }
}
