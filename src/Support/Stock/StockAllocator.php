<?php

namespace FluxErp\Support\Stock;

use FluxErp\Enums\StockRemovalStrategyEnum;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StockAllocator
{
    protected ?int $productId = null;

    protected ?int $warehouseId = null;

    protected ?array $binIds = null;

    protected ?StockRemovalStrategyEnum $strategy = null;

    public function forProduct(int $productId): static
    {
        $this->productId = $productId;

        return $this;
    }

    public function inWarehouse(?int $warehouseId): static
    {
        $this->warehouseId = $warehouseId;

        return $this;
    }

    public function inBins(array $binIds): static
    {
        $this->binIds = $binIds;

        return $this;
    }

    public function withStrategy(?StockRemovalStrategyEnum $strategy): static
    {
        $this->strategy = $strategy;

        return $this;
    }

    public function query(): Builder
    {
        $query = resolve_static(StockPosting::class, 'query')
            ->where('stock_postings.product_id', $this->productId)
            ->where('stock_postings.warehouse_id', $this->warehouseId)
            ->where('stock_postings.remaining_stock', '>', 0)
            ->where(fn (Builder $query) => $query
                ->whereNull('stock_postings.warehouse_bin_id')
                ->orWhereHas('warehouseBin', fn (Builder $query) => $query->where('is_active', true))
            )
            ->where(fn (Builder $query) => $query
                ->whereNull('stock_postings.lot_id')
                ->orWhereHas('lot', fn (Builder $query) => $query->whereNull('blocked_at'))
            );

        if (! is_null($this->binIds)) {
            $query->whereIn('stock_postings.warehouse_bin_id', $this->binIds);
        }

        return match ($this->resolveStrategy()) {
            StockRemovalStrategyEnum::Lifo => $query->orderByDesc('stock_postings.id'),
            StockRemovalStrategyEnum::Fefo => $query
                ->leftJoin('lots', 'lots.id', '=', 'stock_postings.lot_id')
                ->select('stock_postings.*')
                ->orderByRaw('lots.expires_at IS NULL')
                ->orderBy('lots.expires_at')
                ->orderBy('stock_postings.id'),
            default => $query->orderBy('stock_postings.id'),
        };
    }

    public function allocate(string|int|float $amount): Collection
    {
        $open = is_float($amount) ? sprintf('%.10F', $amount) : (string) $amount;
        $allocation = collect();

        foreach ($this->query()->get() as $stockPosting) {
            if (bccomp($open, '0', 10) <= 0) {
                break;
            }

            $take = bccomp($open, (string) $stockPosting->remaining_stock, 10) === 1
                ? (string) $stockPosting->remaining_stock
                : $open;

            $allocation->push(['stockPosting' => $stockPosting, 'amount' => $take]);
            $open = bcsub($open, $take, 10);
        }

        return $allocation;
    }

    protected function resolveStrategy(): StockRemovalStrategyEnum
    {
        return $this->strategy
            ?? resolve_static(Product::class, 'query')
                ->whereKey($this->productId)
                ->value('stock_removal_strategy_enum')
            ?? resolve_static(Warehouse::class, 'query')
                ->whereKey($this->warehouseId)
                ->value('stock_removal_strategy_enum')
            ?? StockRemovalStrategyEnum::Fifo;
    }
}
