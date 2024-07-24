<?php

namespace FluxErp\Actions\StockPosting;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\StockPosting;
use FluxErp\Rulesets\StockPosting\DeleteStockPostingRuleset;

class DeleteStockPosting extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteStockPostingRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [StockPosting::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(StockPosting::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
