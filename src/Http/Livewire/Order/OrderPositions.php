<?php

namespace FluxErp\Http\Livewire\Order;

use FluxErp\Actions\OrderPosition\PriceCalculation;
use FluxErp\Helpers\PriceHelper;
use FluxErp\Http\Requests\CreateOrderPositionRequest;
use FluxErp\Http\Requests\UpdateOrderPositionRequest;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Component;
use WireUi\Traits\Actions;

class OrderPositions extends Component
{
    use Actions;

    public int $orderId;

    public array $order;

    public array $groups = [];

    public bool $showGroupAdd = false;

    public bool $showOrderPosition = false;

    public array $position = [
        'product_id' => null,
        'warehouse_id' => null,
        'vat_rate_id' => null,
        'contact_supplier_id' => null,
        'unit_net_price' => null,
        'unit_gross_price' => null,
    ];

    public ?int $productId = null;

    public array $selected = [];

    public array $addresses = [];

    public array $vatRates = [];

    public function getRules(): array
    {
        $orderPosition = ($this->position['id'] ?? false) && is_numeric($this->position['id'])
            ? new UpdateOrderPositionRequest()
            : new CreateOrderPositionRequest();

        return Arr::prependKeysWith($orderPosition->rules(), 'position.');
    }

    public function mount(int $id): void
    {
        $this->getOrder($id);

        $this->vatRates = VatRate::query()
            ->get(['id', 'name', 'rate_percentage'])
            ->toArray();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.order.order-positions');
    }

    private function getOrder(int $id): void
    {
        $this->orderId = $id;

        $this->order = \FluxErp\Models\Order::query()
            ->with(['priceList:id,name,is_net', 'currency:id,symbol'])
            ->whereKey($id)
            ->first()
            ->toArray();
    }

    public function edit(array $orderPosition): void
    {
        $this->resetErrorBag();
        if ($orderPosition['id'] ?? false) {
            if ($orderPosition['is_free_text'] === false) {
                $orderPosition['unit_price'] = $orderPosition['is_net']
                    ? $orderPosition['unit_net_price']
                    : $orderPosition['unit_gross_price'];
                $this->productId = $orderPosition['product_id'] ?? null;
            }
        } else {
            $this->reset(['position', 'productId']);
            $orderPosition['amount'] = 1;
            $orderPosition['order_id'] = $this->orderId;
            $orderPosition['client_id'] = $this->order['client_id'];
            $orderPosition['is_net'] = $this->order['price_list']['is_net'];
            $orderPosition['is_free_text'] = false;
            $orderPosition['is_alternative'] = false;

            $orderPosition = array_merge($this->position, $orderPosition);
        }

        $this->showOrderPosition = true;

        $this->position = $orderPosition;
    }

    private function recalculatePosition(array $orderPosition): array
    {
        $position = new OrderPosition();
        if ($orderPosition['id'] ?? false) {
            $position->setKeyType(gettype($orderPosition['id']));
        }
        $position->forceFill($orderPosition);
        $position->is_net = $position->is_net ?: $this->order['price_list']['is_net'];
        $position->parent_id = $position->parent_id ?: null;

        PriceCalculation::fill($position, $orderPosition);

        $orderPosition = $position->toArray();

        $orderPosition['unit_price'] = $orderPosition['is_net']
            ? $orderPosition['unit_net_price'] ?? null
            : $orderPosition['unit_gross_price'] ?? null;

        return $orderPosition;
    }

    public function updatedProductId(string $id = null): void
    {
        if (is_null($id)) {
            return;
        }

        $product = Product::query()
            ->with(['prices', 'bundleProducts'])
            ->whereKey($id)
            ->first();

        $priceListId = ($this->position['price_list_id'] ?? false) ?: $this->order['price_list_id'];

        /** @var Price $price */
        $price = PriceHelper::make($product)
            ->setPriceList(PriceList::query()->whereKey($priceListId)->first())
            ->price();

        $this->position['product_id'] = $id;
        $this->position['product_number'] = $product?->product_number;
        $this->position['name'] = $product?->name;
        $this->position['amount'] = 1;
        $this->position['vat_rate_id'] = $product?->vat_rate_id;
        $this->position['vat_rate_percentage'] = collect($this->vatRates)->where('id', '=', $product?->vat_rate_id)->first()['rate_percentage'] ?? null;
        $this->position['is_free_text'] = false;
        $this->position['unit_net_price'] = $price?->getNet($this->position['vat_rate_percentage']);
        $this->position['unit_gross_price'] = $price?->getGross($this->position['vat_rate_percentage']);

        $this->position = $this->recalculatePosition($this->position);

        $this->position['children'] = $product?->bundleProducts?->map(function (Product $product) {
            return [
                'product_id' => $product->id,
                'product_number' => $product->product_number,
                'name' => $product->name,
                'amount_bundle' => $product->pivot->count,
                'is_free_text' => true,
                'is_bundle_position' => true,
            ];
        })->toArray();
    }

    public function updatedPositionVatRateId(): void
    {
        $this->position['vat_rate_percentage'] = null;

        $this->recalculatePosition($this->position);
    }

    public function addtoGroup(): void
    {
        // TODO: Implement addtoGroup() method.
    }

    public function remove(array|string $selected = null): void
    {
        $ids = $selected ? (array) $selected : $this->selected;

        $this->dispatch('removeByKey', $ids)->to('data-tables.order-position-list');
    }

    public function save(array $orderPosition, array $orderPositions): array
    {
        $bundleProducts = $orderPosition['children'] ?? [];
        $orderPosition = Validator::make(
            $orderPosition,
            ($orderPosition['id'] ?? false) && is_numeric($orderPosition['id'])
                ? (new UpdateOrderPositionRequest())->rules()
                : (new CreateOrderPositionRequest())->rules()
        )->validated();
        $orderPosition = $this->recalculatePosition($orderPosition);
        $orderPosition['children'] = $bundleProducts;

        if (! ($orderPosition['id'] ?? false)) {
            $sortNumber = $orderPositions ? max(array_column($orderPositions, 'sort_number')) + 1 : 1;
            $slugPosition = $orderPositions
                ? floor(max(array_column($orderPositions, 'slug_position'))) + 1
                : 1;
            $orderPosition = array_merge(
                [
                    'id' => Str::uuid()->toString(),
                    'sort_number' => $sortNumber,
                    'slug_position' => (string) $slugPosition,
                    'client_id' => $this->order['client_id'],
                    'order_id' => $this->orderId,
                    'is_free_text' => false,
                ],
                $orderPosition
            );
        }

        if ($orderPosition['children'] ?? false) {
            foreach ($orderPosition['children'] as $key => &$child) {
                $child = array_merge(
                    [
                        'id' => Str::uuid()->toString(),
                        'parent_id' => $orderPosition['id'],
                        'amount' => bcmul($child['amount_bundle'], $orderPosition['amount']),
                        'sort_number' => $orderPosition['sort_number'] + $key + 1,
                        'slug_position' => $orderPosition['slug_position'] . '.' . $key + 1,
                        'client_id' => $this->order['client_id'],
                        'order_id' => $this->orderId,
                        'depth' => ($orderPosition['depth'] ?? 0) + 1,
                        'is_free_text' => true,
                        'is_bundle_position' => true,
                    ],
                    $child
                );
            }
        }

        $this->showOrderPosition = false;
        $this->skipRender();
        $this->reset('position');

        return $orderPosition;
    }
}
