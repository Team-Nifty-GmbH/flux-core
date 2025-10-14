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
use Illuminate\Database\Query\JoinClause;
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
            $positionIds = array_diff($this->selectedPositions, $alreadySelectedIds);

            if (! $positionIds) {
                $this->selectedPositions = [];

                return;
            }
        }

        $query = resolve_static(OrderPosition::class, 'query')
            ->where('order_positions.order_id', $this->orderId)
            ->with(['product.unit:id,abbreviation'])
            ->leftJoin('order_positions AS descendants', function (JoinClause $join): void {
                $join->on('order_positions.id', '=', 'descendants.origin_position_id')
                    ->whereNull('descendants.deleted_at');
            })
            ->leftJoin('order_positions AS subDescendants', function (JoinClause $join): void {
                $join->on('descendants.id', '=', 'subDescendants.origin_position_id')
                    ->whereNull('subDescendants.deleted_at');
            })
            ->selectRaw(
                'order_positions.id'
                . ', order_positions.amount'
                . ', order_positions.name'
                . ', order_positions.description'
                . ', order_positions.unit_net_price'
                . ', order_positions.unit_gross_price'
                . ', order_positions.total_net_price'
                . ', order_positions.total_gross_price'
                . ', order_positions.is_net'
                . ', SUM(COALESCE(descendants.amount, 0)) AS descendantAmount'
                . ', SUM(COALESCE(subDescendants.amount, 0)) AS subDescendantAmount'
                . ', order_positions.amount'
                . ' - SUM(COALESCE(descendants.amount, 0)) + SUM(COALESCE(subDescendants.amount, 0)) AS totalAmount'
            )
            ->groupBy([
                'order_positions.id',
                'order_positions.amount',
                'order_positions.name',
                'order_positions.description',
                'order_positions.unit_net_price',
                'order_positions.unit_gross_price',
                'order_positions.total_net_price',
                'order_positions.total_gross_price',
                'order_positions.is_net',
            ])
            ->where('order_positions.is_bundle_position', false)
            ->havingRaw('totalAmount > 0');

        if (! $takeAllWithPercentage) {
            $query->whereKey($positionIds);
        }

        $orderPositions = $query->get();

        foreach ($orderPositions as $orderPosition) {
            $amount = $takeAllWithPercentage
                ? bcround($orderPosition->totalAmount * ($this->percentage / 100), 2)
                : $orderPosition->totalAmount;

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
