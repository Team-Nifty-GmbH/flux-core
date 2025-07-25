<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\DataTables\OrderPositionList;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\View\ComponentAttributeBag;
use Livewire\Attributes\Modelable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableRowAttributes;

class ReplicateOrderPositionList extends OrderPositionList
{
    public array $alreadyTakenPositions = [];

    public array $enabledCols = [
        'slug_position',
        'name',
        'unit_net_price',
        'totalAmount',
        'total_net_price',
    ];

    public bool $isFilterable = false;

    public ?bool $isSearchable = false;

    public bool $isSelectable = true;

    public ?int $orderId;

    #[Modelable]
    public array $selected = [];

    protected function getSelectedActions(): array
    {
        return [];
    }

    public function getFormatters(): array
    {
        return array_merge(
            parent::getFormatters(),
            [
                'slug_position' => 'string',
                'alternative_tag' => ['state', [__('Alternative') => 'red']],
            ]
        );
    }

    public function getSelectAttributes(): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'x-show' => '! record.is_bundle_position',
        ]);
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder
            ->where('order_id', $this->orderId)
            ->withSum(['descendants as descendantsAmount' => function (Builder $query): void {
                $query->whereNull('deleted_at');
            }], 'amount')
            ->whereNull('parent_id')
            ->orderBy('sort_number');
    }

    protected function getLeftAppends(): array
    {
        return [
            'name' => 'indentation',
        ];
    }

    protected function getResultFromQuery(Builder $query): array
    {
        $tree = to_flat_tree($query->get()->toArray());
        $returnKeys = $this->getReturnKeys();

        foreach ($tree as $key => &$item) {
            $totalAmount = bcsub($item['amount'], $item['descendantsAmount'] ?? 0, 2);
            if (bccomp($totalAmount, 0) !== 1 || in_array($item['id'], $this->alreadyTakenPositions)) {
                unset($tree[$key]);

                continue;
            }

            $item = Arr::only(Arr::dot($item), $returnKeys);
            $item['totalAmount'] = $totalAmount;
            $item['indentation'] = '';
            $item['unit_price'] = $item['is_net'] ? ($item['unit_net_price'] ?? 0) : ($item['unit_gross_price'] ?? 0);
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

    protected function getReturnKeys(): array
    {
        return array_merge(
            parent::getReturnKeys(),
            [
                'amount',
                'is_alternative',
                'is_net',
                'is_free_text',
                'is_bundle_position',
                'totalAmount',
                'descendantsAmount',
                'depth',
                'has_children',
                'unit_price',
                'alternative_tag',
                'indentation',
            ]
        );
    }

    protected function getRightAppends(): array
    {
        return [
            'name' => 'alternative_tag',
        ];
    }

    protected function getRowAttributes(): DataTableRowAttributes
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

    protected function getTopAppends(): array
    {
        return [
            'name' => 'product_number',
        ];
    }
}
