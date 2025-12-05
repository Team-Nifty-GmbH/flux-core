<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\DataTables\OrderPositionList;
use FluxErp\Models\OrderPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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

    public string $orderBy = 'slug_position';

    public bool $orderAsc = true;

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
        return resolve_static(OrderPosition::class, 'familyTree')
            ->where('order_id', $this->orderId)
            ->whereNull('parent_id');
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

        $positionIds = collect($tree)
            ->filter(
                fn (array $value) => data_get($value, 'is_free_text') == false
                    && data_get($value, 'is_bundle_position') == false
            )
            ->pluck('id')
            ->toArray();

        $signedAmounts = DB::select(
            'WITH RECURSIVE siblings AS (
                SELECT id, origin_position_id, signed_amount
                FROM order_positions
                WHERE order_id = ' . $this->orderId
            . ' AND id IN (' . implode(',', $positionIds) . ')'
            . ' UNION ALL
                SELECT op.id, op.origin_position_id, op.signed_amount
                FROM order_positions op
                INNER JOIN siblings s ON s.id = op.origin_position_id
                WHERE op.deleted_at IS NULL
            )
            SELECT * FROM siblings'
        );

        $maxAmounts = array_reduce(
            $signedAmounts,
            function (?array $carry, object $item) {
                $parentKey = array_find_key(
                    $carry ?? [],
                    fn (array $value) => ! is_null($item->origin_position_id)
                        && in_array(
                            $item->origin_position_id,
                            [
                                $value['id'],
                                $value['origin_position_id'],
                            ]
                        )
                );

                if (is_null($parentKey)) {
                    $carry[] = (array) $item;
                } else {
                    $carry[$parentKey] = array_merge(
                        $carry[$parentKey],
                        [
                            'origin_position_id' => $item->id,
                            'signed_amount' => bcsub(
                                data_get($carry, $parentKey . '.signed_amount'),
                                $item->signed_amount
                            ),
                        ]
                    );
                }

                return $carry;
            }
        );

        foreach ($tree as $key => &$item) {
            if (data_get($item, 'is_free_text')) {
                continue;
            }

            if (data_get($item, 'is_bundle_position')) {
                if (is_null(array_find_key($tree, fn (array $value) => $value['id'] === $item['parent_id']))) {
                    unset($tree[$key]);
                }

                continue;
            }

            $totalAmount = data_get(
                array_find($maxAmounts, fn (array $value) => $value['id'] === data_get($item, 'id')),
                'signed_amount'
            );

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

        return array_values($tree);
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
