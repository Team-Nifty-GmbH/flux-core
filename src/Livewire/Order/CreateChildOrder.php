<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Forms\OrderReplicateForm;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class CreateChildOrder extends Component
{
    use Actions;

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
            ->where('client_id', $parentOrder->client_id)
            ->where('order_type_enum', $this->type)
            ->where('is_active', true)
            ->when(
                $this->type === OrderTypeEnum::SplitOrder->value,
                fn (Builder $query) => $query->where('is_hidden', false)
            )
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
        unset($this->replicateOrder->order_positions[$index]);
        $this->replicateOrder->order_positions = array_values($this->replicateOrder->order_positions);
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

        $this->notification()
            ->success($successMessage)
            ->send();

        $this->redirectRoute('orders.id', ['id' => $this->replicateOrder->id], navigate: true);
    }

    public function takeOrderPositions(): void
    {
        $takeAllWithPercentage = $this->percentage && $this->percentage > 0;

        if ($takeAllWithPercentage) {
            $this->replicateOrder->order_positions = [];
        } else {
            $alreadySelectedIds = array_column($this->replicateOrder->order_positions, 'id');
            $positionIds = array_diff($this->selectedPositions, $alreadySelectedIds, ['*']);

            if (! $positionIds) {
                $this->selectedPositions = [];

                return;
            }
        }

        $maxAmounts = array_reduce(
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
            ),
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

        $orderPositions = resolve_static(OrderPosition::class, 'query')
            ->whereKey(array_column($maxAmounts, 'id'))
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
                'is_net',
            ])
            ->get()
            ->map(function (OrderPosition $position) use ($maxAmounts) {
                $position->setRelation(
                    'maxAmount',
                    data_get(
                        array_find($maxAmounts, fn (array $value) => $value['id'] === $position->getKey()),
                        'signed_amount'
                    )
                );

                return $position;
            });

        foreach ($orderPositions as $orderPosition) {
            $amount = $takeAllWithPercentage
                ? bcround($orderPosition->maxAmount * ($this->percentage / 100), 2)
                : $orderPosition->maxAmount;

            if (bccomp($amount, 0) === 1) {
                $this->replicateOrder->order_positions[] = [
                    'id' => $orderPosition->id,
                    'amount' => $amount,
                    'name' => $orderPosition->name,
                    'description' => $orderPosition->description,
                    'unit_net_price' => $orderPosition->unit_net_price,
                    'unit_gross_price' => $orderPosition->unit_gross_price,
                    'total_net_price' => $orderPosition->total_net_price,
                    'total_gross_price' => $orderPosition->total_gross_price,
                    'is_net' => $orderPosition->is_net,
                    'unit_abbreviation' => $orderPosition->product?->unit?->abbreviation,
                ];
            }
        }

        if ($takeAllWithPercentage) {
            $this->percentage = null;
        }

        $this->selectedPositions = [];
    }
}
