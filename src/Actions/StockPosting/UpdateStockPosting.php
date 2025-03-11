<?php

namespace FluxErp\Actions\StockPosting;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\StockPosting;
use FluxErp\Rulesets\StockPosting\UpdateStockPostingRuleset;

class UpdateStockPosting extends FluxAction
{
    public static function models(): array
    {
        return [StockPosting::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateStockPostingRuleset::class;
    }

    public function performAction(): StockPosting
    {
        $stockPosting = resolve_static(StockPosting::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $stockPosting->fill($this->data);
        $stockPosting->save();

        return $stockPosting->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void {}
}
