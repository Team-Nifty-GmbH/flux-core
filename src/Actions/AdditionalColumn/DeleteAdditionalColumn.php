<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AdditionalColumn;

class DeleteAdditionalColumn extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:additional_columns,id',
        ];
    }

    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    public function performAction(): ?bool
    {
        $additionalColumn = AdditionalColumn::query()
            ->whereKey($this->data['id'])
            ->first();

        $additionalColumn->modelValues()->delete();

        return $additionalColumn->delete();
    }
}
