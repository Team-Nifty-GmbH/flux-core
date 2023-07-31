<?php

namespace FluxErp\Actions\StockPosting;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\StockPosting;

class DeleteStockPosting extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:stock_postings,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [StockPosting::class];
    }

    public function performAction(): ?bool
    {
        return StockPosting::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
