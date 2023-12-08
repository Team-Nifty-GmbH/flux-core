<?php

namespace FluxErp\Actions\StockPosting;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateStockPostingRequest;
use FluxErp\Models\StockPosting;

class CreateStockPosting extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateStockPostingRequest())->rules();
    }

    public static function models(): array
    {
        return [StockPosting::class];
    }

    public function performAction(): StockPosting
    {
        $stockPosting = new StockPosting($this->data);
        $stockPosting->save();

        return $stockPosting->fresh();
    }
}
