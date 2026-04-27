<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Forms\OrderReplicateForm;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Traits\CalculatesPositionAvailability;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class CreateChildOrder extends Component
{
    use Actions, CalculatesPositionAvailability;

    #[Locked]
    public array $availableOrderTypes = [];

    #[Url]
    public ?int $orderId = null;

    public ?array $parentOrder = null;

    public ?float $percentage = null;

    public OrderReplicateForm $replicateOrder;

    public array $selectedPositions = [];

    #[Url]
    public ?string $type = null;

    public function mount(): void
    {
        $parentOrder = resolve_static(Order::class, 'query')
            ->whereKey($this->orderId)
            ->with([
                'addresses',
                'contact',
                'currency',
                'discounts' => fn (MorphMany $query): MorphMany => $query->ordered(),
                'orderType',
                'priceList',
            ])
            ->first();

        if (
            ! $this->orderId
            || ! $this->type
            || ! in_array($this->type, [OrderTypeEnum::Retoure->value, OrderTypeEnum::SplitOrder->value])
            || ! $parentOrder
        ) {
            $this->redirectRoute('orders.orders', navigate: true);

            return;
        }

        $this->parentOrder = $parentOrder->toArray();
        $this->parentOrder['avatarUrl'] = $parentOrder->getAvatarUrl();

        $this->replicateOrder->fill($this->parentOrder);
        $this->replicateOrder->parent_id = $this->orderId;
        $this->replicateOrder->order_positions = [];

        $this->availableOrderTypes = resolve_static(OrderType::class, 'query')
            ->where('order_type_enum', $this->type)
            ->where('is_active', true)
            ->whereHasTenant($parentOrder->tenant_id)
            ->when(
                $this->type === OrderTypeEnum::SplitOrder->value,
                fn (Builder $query) => $query->where('is_hidden', false)
            )
            ->ordered()
            ->get(['id', 'name'])
            ->toArray();

        if (count($this->availableOrderTypes) === 1) {
            $this->replicateOrder->order_type_id = data_get($this->availableOrderTypes, '0.id');
        }
    }

    public function render(): View
    {
        return view('flux::livewire.order.create-child-order');
    }

    public function getTitle(): string
    {
        return $this->type === OrderTypeEnum::Retoure->value
            ? __('Create Retoure')
            : __('Create Split-Order');
    }

    public function removePosition(int $index): void
    {
        $position = $this->replicateOrder->order_positions[$index] ?? null;
        unset($this->replicateOrder->order_positions[$index]);

        // If removing a block, also remove all descendants recursively
        if ($position && data_get($position, 'is_free_text')) {
            $positionId = data_get($position, 'id');
            $descendantIds = resolve_static(OrderPosition::class, 'query')
                ->whereKey($positionId)
                ->first()
                ?->descendantKeys() ?? [];

            $this->replicateOrder->order_positions = array_filter(
                $this->replicateOrder->order_positions,
                fn ($p) => ! in_array(data_get($p, 'id'), $descendantIds)
            );
        }

        $this->replicateOrder->order_positions = array_values($this->replicateOrder->order_positions);

        $this->dispatchAlreadyTakenPositions();
    }

    #[Renderless]
    public function save(): void
    {
        try {
            if (! $this->replicateOrder->order_positions) {
                throw ValidationException::withMessages([
                    'selectedPositions' => __('Please select at least one order position.'),
                ]);
            }

            $this->replicateOrder->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $successMessage = $this->type === OrderTypeEnum::Retoure->value
            ? __(':model created', ['model' => __('Retoure')])
            : __(':model created', ['model' => __('Split Order')]);

        $this->toast()
            ->success($successMessage)
            ->send();

        $this->redirectRoute('orders.id', ['id' => $this->replicateOrder->id], navigate: true);
    }

    public function takeOrderPositions(): void
    {
        $takeAllWithPercentage = $this->percentage && $this->percentage > 0;
        $positionIds = [];

        if ($takeAllWithPercentage) {
            $this->replicateOrder->order_positions = [];
        } else {
            $alreadySelectedIds = array_column($this->replicateOrder->order_positions, 'id');

            // Resolve wildcard '*' to all available position IDs
            if (in_array('*', $this->selectedPositions)) {
                $this->selectedPositions = resolve_static(OrderPosition::class, 'query')
                    ->where('order_id', $this->orderId)
                    ->pluck('id')
                    ->toArray();
            }

            // Auto-include all ancestor blocks via single CTE query per selected position
            $selectedWithParents = collect($this->selectedPositions);
            $selectedPositions = resolve_static(OrderPosition::class, 'query')
                ->whereKey($this->selectedPositions)
                ->whereNotNull('parent_id')
                ->get();

            $allAncestorIds = $selectedPositions
                ->flatMap(fn (OrderPosition $pos) => $pos->ancestorKeys())
                ->unique();

            if ($allAncestorIds->isNotEmpty()) {
                $selectedWithParents = $selectedWithParents->merge($allAncestorIds);

                // Add free text siblings in ancestor blocks
                $freeTextSiblings = resolve_static(OrderPosition::class, 'query')
                    ->whereIn('parent_id', $allAncestorIds)
                    ->where('is_free_text', true)
                    ->pluck('id');
                $selectedWithParents = $selectedWithParents->merge($freeTextSiblings);
            }

            $positionIds = array_diff($selectedWithParents->unique()->toArray(), $alreadySelectedIds);

            if (! $positionIds) {
                $this->selectedPositions = [];

                return;
            }
        }

        $maxAmounts = $this->calculateMaxAmounts(
            DB::select(
                'WITH RECURSIVE siblings AS (
                    SELECT id, origin_position_id, signed_amount
                    FROM order_positions
                    WHERE order_id = ' . $this->orderId
                . (! $takeAllWithPercentage ? ' AND id IN (' . implode(',', $positionIds) . ')' : '')
                . ' UNION ALL
                    SELECT op.id, op.origin_position_id, op.signed_amount
                    FROM order_positions op
                    INNER JOIN siblings s ON s.id = op.origin_position_id
                    WHERE op.deleted_at IS NULL
                )
                SELECT * FROM siblings'
            )
        );

        $realPositionIds = array_column($maxAmounts, 'id');
        $freeTextIds = resolve_static(OrderPosition::class, 'query')
            ->whereKey($positionIds)
            ->where('is_free_text', true)
            ->pluck('id')
            ->toArray();

        $orderPositions = resolve_static(OrderPosition::class, 'query')
            ->with(['product.unit:id,abbreviation'])
            ->select([
                'id',
                'product_id',
                'name',
                'description',
                'unit_net_price',
                'unit_gross_price',
                'total_net_price',
                'total_gross_price',
                'discount_percentage',
                'is_net',
                'is_free_text',
            ])
            ->whereKey(array_merge($realPositionIds, $freeTextIds))
            ->get();

        foreach ($orderPositions as $orderPosition) {
            if ($orderPosition->is_free_text) {
                $this->replicateOrder->order_positions[] = [
                    'id' => $orderPosition->getKey(),
                    'amount' => 0,
                    'name' => $orderPosition->name,
                    'description' => $orderPosition->description,
                    'unit_net_price' => 0,
                    'unit_gross_price' => 0,
                    'total_net_price' => 0,
                    'total_gross_price' => 0,
                    'discount_percentage' => 0,
                    'is_net' => $orderPosition->is_net,
                    'is_free_text' => true,
                    'unit_abbreviation' => null,
                ];

                continue;
            }

            $amount = data_get(
                array_find($maxAmounts, fn (array $v) => data_get($v, 'id') === $orderPosition->getKey()),
                'signed_amount'
            );

            $amount = $takeAllWithPercentage
                ? bcround(bcmul($amount, bcdiv($this->percentage, 100)), 2)
                : $amount;

            if (bccomp($amount, 0) === 1) {
                $this->replicateOrder->order_positions[] = [
                    'id' => $orderPosition->getKey(),
                    'amount' => $amount,
                    'name' => $orderPosition->name,
                    'description' => $orderPosition->description,
                    'unit_net_price' => $orderPosition->unit_net_price,
                    'unit_gross_price' => $orderPosition->unit_gross_price,
                    'total_net_price' => $orderPosition->total_net_price,
                    'total_gross_price' => $orderPosition->total_gross_price,
                    'discount_percentage' => $orderPosition->discount_percentage,
                    'is_net' => $orderPosition->is_net,
                    'unit_abbreviation' => $orderPosition->product?->unit?->abbreviation,
                ];
            }
        }

        $this->sortPositionsByTreeOrder();

        if ($takeAllWithPercentage) {
            $this->percentage = null;
        }

        $this->selectedPositions = [];

        $this->dispatchAlreadyTakenPositions();
    }

    protected function dispatchAlreadyTakenPositions(): void
    {
        // Exclude block headers so blocks stay visible on the left
        // while they still have remaining children
        $takenIds = collect($this->replicateOrder->order_positions)->pluck('id');
        $blockIds = resolve_static(OrderPosition::class, 'query')
            ->whereKey($takenIds)
            ->where('is_free_text', true)
            ->whereHas('children')
            ->pluck('id');

        $this->dispatch(
            'updateAlreadyTakenPositions',
            alreadyTakenPositions: $takenIds->diff($blockIds)->values()->toArray()
        );
    }

    protected function sortPositionsByTreeOrder(): void
    {
        $treeOrder = to_flat_tree(
            resolve_static(OrderPosition::class, 'familyTree')
                ->where('order_id', $this->orderId)
                ->whereNull('parent_id')
                ->get()
                ->toArray()
        );
        $orderMap = array_flip(array_column($treeOrder, 'id'));

        usort(
            $this->replicateOrder->order_positions,
            fn ($left, $right) => data_get($orderMap, data_get($left, 'id'), PHP_INT_MAX)
                <=> data_get($orderMap, data_get($right, 'id'), PHP_INT_MAX)
        );
    }
}
