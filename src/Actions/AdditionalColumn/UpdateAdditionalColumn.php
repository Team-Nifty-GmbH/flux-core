<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Rulesets\AdditionalColumn\UpdateAdditionalColumnRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateAdditionalColumn extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateAdditionalColumnRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    public function performAction(): Model
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

        $additionalColumn = app(AdditionalColumn::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $additionalColumn->fill($this->data);
        $additionalColumn->save();

        return $additionalColumn->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $additionalColumn = app(AdditionalColumn::class)->query()
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
    }
}
