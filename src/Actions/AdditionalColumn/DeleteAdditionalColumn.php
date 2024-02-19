<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Rules\ModelExists;

class DeleteAdditionalColumn extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => [
                'required',
                'integer',
                new ModelExists(AdditionalColumn::class),
            ],
        ];
    }

    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    public function performAction(): ?bool
    {
        $additionalColumn = app(AdditionalColumn::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $additionalColumn->modelValues()->delete();

        return $additionalColumn->delete();
    }
}
