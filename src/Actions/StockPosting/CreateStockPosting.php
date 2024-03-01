<?php

namespace FluxErp\Actions\StockPosting;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\StockPosting;
use FluxErp\Rulesets\StockPosting\CreateStockPostingRuleset;

class CreateStockPosting extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateStockPostingRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [StockPosting::class];
    }

    public function performAction(): StockPosting
    {
        $stockPosting = app(StockPosting::class, ['attributes' => $this->data]);
        $stockPosting->save();

        return $stockPosting->fresh();
    }
}
