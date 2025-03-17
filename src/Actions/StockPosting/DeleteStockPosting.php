<?php

namespace FluxErp\Actions\StockPosting;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\StockPosting;
use FluxErp\Rulesets\StockPosting\DeleteStockPostingRuleset;

class DeleteStockPosting extends FluxAction
{
    public static function models(): array
    {
        return [StockPosting::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteStockPostingRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(StockPosting::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
