<?php

namespace FluxErp\Actions\StockPosting;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\StockPosting;

class DeleteStockPosting extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:stock_postings,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [StockPosting::class];
    }

    public function execute(): bool|null
    {
        return StockPosting::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
