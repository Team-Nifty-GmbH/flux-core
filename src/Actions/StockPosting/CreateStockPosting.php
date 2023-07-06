<?php

namespace FluxErp\Actions\StockPosting;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateStockPostingRequest;
use FluxErp\Models\StockPosting;
use Illuminate\Support\Facades\Validator;

class CreateStockPosting implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateStockPostingRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'stock-posting.create';
    }

    public static function description(): string|null
    {
        return 'create stock posting';
    }

    public static function models(): array
    {
        return [StockPosting::class];
    }

    public function execute(): StockPosting
    {
        $this->data['stock'] = $this->getLatestStock(
            $this->data['warehouse_id'], $this->data['product_id'], $this->data['posting']
        );

        $stockPosting = new StockPosting($this->data);
        $stockPosting->save();

        return $stockPosting;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }

    private function getLatestStock(int $warehouseId, int $productId, float $posting): float
    {
        $latestPosting = StockPosting::query()
            ->where('warehouse_id', '=', $warehouseId)
            ->where('product_id', '=', $productId)
            ->latest('id')
            ->first();

        if (empty($latestPosting->stock)) {
            return $posting;
        }

        return $latestPosting->stock + $posting;
    }
}
