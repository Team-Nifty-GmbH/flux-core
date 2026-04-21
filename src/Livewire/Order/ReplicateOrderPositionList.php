<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\DataTables\OrderPositionList;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Traits\CalculatesPositionAvailability;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use Illuminate\View\ComponentAttributeBag;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableRowAttributes;

class ReplicateOrderPositionList extends OrderPositionList
{
    use CalculatesPositionAvailability;

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

    #[Locked]
    public ?string $type = null;

    #[Modelable]
    public array $selected = [];

    public string $orderBy = 'slug_position';

    public bool $orderAsc = true;

    #[Locked]
    public array $countOnly = [];

    protected function getSelectedActions(): array
    {
        return [];
    }

    #[On('updateAlreadyTakenPositions')]
    public function updateAlreadyTakenPositions(array $alreadyTakenPositions): void
    {
        $this->alreadyTakenPositions = $alreadyTakenPositions;
        $this->selected = [];
        $this->loadData();
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
            'x-show' => '! record.is_bundle_position && ! (record.is_free_text && record.has_children)',
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

        $maxAmounts = $positionIds
            ? $this->calculateMaxAmounts(
                DB::select(
                    'WITH RECURSIVE siblings AS (
                        SELECT id, origin_position_id, signed_amount
                        FROM order_positions
                        WHERE order_id = ' . $this->orderId
                    . ' AND id IN (' . implode(',', $positionIds) . ')'
                    . ' UNION ALL
                        SELECT op.id, op.origin_position_id, op.signed_amount
                        FROM order_positions op
                        INNER JOIN siblings s ON s.id = op.origin_position_id
                        WHERE op.deleted_at IS NULL'
                    . ($this->countOnly ? ' AND op.order_id IN (' . implode(',', $this->countOnly) . ')' : '')
                    . ')
                    SELECT * FROM siblings'
                )
            )
            : [];

        // First pass: remove taken positions
        foreach ($tree as $key => $item) {
            // Free text and bundle positions taken by ID
            if (in_array(data_get($item, 'id'), $this->alreadyTakenPositions)) {
                unset($tree[$key]);

                continue;
            }

            // Real positions also checked by signed_amount
            if (! data_get($item, 'is_free_text') && ! data_get($item, 'is_bundle_position')) {
                $totalAmount = data_get(
                    array_find(
                        $maxAmounts,
                        fn (array $value): bool => data_get($value, 'id') === data_get($item, 'id')
                    ),
                    'signed_amount'
                );

                if (bccomp($totalAmount, 0) !== 1) {
                    unset($tree[$key]);
                }
            }
        }

        // Second pass: remove bundle positions without parent and
        // free text blocks without remaining real children
        foreach ($tree as $key => $item) {
            if (data_get($item, 'is_bundle_position')) {
                $parentExists = collect($tree)
                    ->contains(fn (array $v) => data_get($v, 'id') === data_get($item, 'parent_id'));

                if (! $parentExists) {
                    unset($tree[$key]);
                }

                continue;
            }

            if (data_get($item, 'is_free_text') && data_get($item, 'has_children')) {
                $hasRemainingChildren = collect($tree)
                    ->contains(fn (array $child) => data_get($child, 'parent_id') === data_get($item, 'id')
                        && ! data_get($child, 'is_free_text'));

                if (! $hasRemainingChildren) {
                    unset($tree[$key]);
                }
            }
        }

        // Third pass: format remaining items for display
        $currencyIso = data_get(
            resolve_static(Order::class, 'query')
                ->whereKey($this->orderId)
                ->with('currency:id,iso')
                ->first(['id', 'currency_id']),
            'currency.iso',
            'EUR'
        );

        foreach ($tree as $key => &$item) {
            $totalAmount = null;

            if (! data_get($item, 'is_free_text') && ! data_get($item, 'is_bundle_position')) {
                $totalAmount = data_get(
                    array_find(
                        $maxAmounts,
                        fn (array $value): bool => data_get($value, 'id') === data_get($item, 'id')
                    ),
                    'signed_amount'
                );
            }

            $item = Arr::only(Arr::dot($item), $returnKeys);
            $item['totalAmount'] = $totalAmount;
            $item['indentation'] = '';
            $item['unit_price'] = data_get($item, 'is_net')
                ? data_get($item, 'unit_net_price', 0)
                : data_get($item, 'unit_gross_price', 0);
            $item['alternative_tag'] = data_get($item, 'is_alternative') ? __('Alternative') : '';

            // Format money values
            foreach (['unit_net_price', 'total_net_price'] as $moneyCol) {
                $raw = data_get($item, $moneyCol);

                if (! is_null($raw)) {
                    $formatted = Number::currency((float) $raw, $currencyIso);
                    $item[$moneyCol] = ['raw' => $raw, 'display' => e($formatted)];
                }
            }

            if (data_get($item, 'depth', 0) > 0) {
                $indent = data_get($item, 'depth') * 20;
                $item['indentation'] = <<<HTML
                    <div class="text-right indent-icon" style="width:{$indent}px;">
                    </div>
                    HTML;
            }
        }

        return ['data' => array_values($tree)];
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
