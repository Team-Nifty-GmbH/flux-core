<?php

namespace FluxErp\Actions\ProductOption;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\ProductOption;

class DeleteProductOption extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:product_options,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [ProductOption::class];
    }

    public function execute(): ?bool
    {
        return ProductOption::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
