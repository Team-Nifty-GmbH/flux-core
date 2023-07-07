<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\AdditionalColumn;

class DeleteAdditionalColumn extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:additional_columns,id',
        ];
    }

    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    public function execute(): bool|null
    {
        $additionalColumn = AdditionalColumn::query()
            ->whereKey($this->data['id'])
            ->first();

        $additionalColumn->modelValues()->delete();

        return $additionalColumn->delete();
    }
}
