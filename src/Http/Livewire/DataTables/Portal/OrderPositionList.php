<?php

namespace FluxErp\Http\Livewire\DataTables\Portal;

use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\View\ComponentAttributeBag;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableRowAttributes;

class OrderPositionList extends DataTable
{
    protected string $model = OrderPosition::class;

    /** @locked */
    public int $orderId;

    /** @locked */
    public bool $isLocked = false;

    public bool $isSelectable = true;

    public array $enabledCols = [
        'slug_position',
        'name',
        'unit_net_price',
        'amount',
        'total_net_price',
    ];

    public ?bool $isSearchable = false;

    public bool $isFilterable = false;

    public function mount(): void
    {
        $this->isLocked = Order::query()
            ->select('is_locked')
            ->whereKey($this->orderId)
            ->value('is_locked');

        parent::mount();

        $this->formatters = array_merge(
            $this->formatters,
            [
                'slug_position' => 'string',
                'alternative_tag' => ['state', [__('Alternative') => 'negative']],
            ]
        );
    }

    public function getSelectAttributes(): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'x-show' => '! record.is_bundle_position',
        ]);
    }

    public function getRowAttributes(): DataTableRowAttributes
    {
        return DataTableRowAttributes::make()
            ->bind(
                'class',
                "{
                    'bg-gray-200 dark:bg-secondary-700 font-bold': (record.is_free_text && record.depth === 0 && record.has_children),
                    'opacity-90': record.is_alternative,
                    'opacity-50 sortable-filter': record.is_bundle_position,
                    'font-semibold': record.is_free_text
                }"
            );
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->whereNull('parent_id')
            ->reorder('sort_number', 'asc');
    }

    public function getReturnKeys(): array
    {
        return array_merge(
            parent::getReturnKeys(),
            [
                'client_id',
                'order_id',
                'parent_id',
                'price_id',
                'product_id',
                'vat_rate_id',
                'warehouse_id',
                'amount',
                'amount_bundle',
                'discount_percentage',
                'total_base_gross_price',
                'total_base_net_price',
                'total_gross_price',
                'vat_price',
                'unit_net_price',
                'unit_gross_price',
                'vat_rate_percentage',
                'description',
                'name',
                'product_number',
                'sort_number',
                'is_alternative',
                'is_net',
                'is_free_text',
                'is_bundle_position',
                'depth',
                'has_children',
                'unit_price',
                'alternative_tag',
            ]
        );
    }

    public function getResultFromQuery(Builder $query): array
    {
        $tree = to_flat_tree($query->get()->toArray());
        $returnKeys = $this->getReturnKeys();

        foreach ($tree as &$item) {
            $item = Arr::only(Arr::dot($item), $returnKeys);
            $item['is_locked'] = $this->isLocked;
            $item['indentation'] = '';
            $item['unit_price'] = $item['is_net'] ? $item['unit_net_price'] : $item['unit_gross_price'];
            $item['alternative_tag'] = $item['is_alternative'] ? __('Alternative') : '';

            if ($item['depth'] > 0) {
                $indent = $item['depth'] * 20;
                $item['indentation'] = <<<HTML
                    <div class="text-right indent-icon" style="width:{$indent}px;">
                    </div>
                    HTML;
            }
        }

        return $tree;
    }

    public function getLeftAppends(): array
    {
        return [
            'name' => 'indentation',
        ];
    }

    public function getTopAppends(): array
    {
        return [
            'name' => 'product_number',
        ];
    }

    public function getRightAppends(): array
    {
        return [
            'name' => 'alternative_tag',
        ];
    }
}
