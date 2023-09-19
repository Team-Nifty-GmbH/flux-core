<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Actions\OrderPosition\PriceCalculation;
use FluxErp\Actions\OrderPosition\UpdateOrderPosition;
use FluxErp\Helpers\PriceHelper;
use FluxErp\Livewire\DataTables\OrderPositionList;
use FluxErp\Models\Contact;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Modelable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class OrderPositions extends OrderPositionList
{
    use Actions;

    protected string $view = 'flux::livewire.order.order-positions';

    public int $orderId;

    #[Modelable]
    public array $order;

    public array $groups = [];

    public bool $showGroupAdd = false;

    public ?int $selectedIndex = null;

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

    public array $addresses = [];

    public array $vatRates = [];

    public function mount(): void
    {
        $this->filters = [['column' => 'order_id', 'operator' => '=', 'value' => $this->orderId]];

        $this->vatRates = VatRate::query()
            ->get(['id', 'name', 'rate_percentage'])
            ->toArray();

        parent::mount();

        $this->formatters = array_merge(
            $this->formatters,
            [
                'unit_net_price' => [
                    'money',
                    [
                        'currency' => [
                            'iso' => $this->order['currency']['iso'],
                        ],
                    ],
                ],
                'total_net_price' => [
                    'money',
                    [
                        'currency' => [
                            'iso' => $this->order['currency']['iso'],
                        ],
                    ],
                ],
            ]
        );
    }

    public function getRowActions(): array
    {
        return $this->showRowButtons
            ? [
                DataTableButton::make()
                    ->icon('pencil')
                    ->rounded()
                    ->color('primary')
                    ->attributes([
                        'x-on:click' => '$wire.edit(record, index)',
                        'x-show' => '! record.is_bundle_position && ! record.is_locked',
                    ]),
            ]
            : [];
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
                'price_list_id',
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

    public function edit(array $orderPosition, int $index = null): void
    {
        $this->selectedIndex = $index;
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

    public function replaceByIndex(array $data, int $index): void
    {
        $this->data[$index] = array_merge($this->data[$index], $data);

        $this->skipRender();
    }

    public function removeByKey(string|int|array $key): void
    {
        $keyed = Arr::keyBy($this->data, $this->modelKeyName);

        $this->data = array_values(Arr::except($keyed, $key));

        $this->skipRender();
    }

    public function addToBottom(array $data): void
    {
        $children = null;
        if ($data['children'] ?? false) {
            $children = Arr::pull($data, 'children');
        }

        $data['alternative_tag'] = ($data['is_alternative'] ?? false) ? __('Alternative') : '';
        $this->data[] = $data;

        if ($children ?? false) {
            foreach ($children as $child) {
                if ($child['depth'] > 0) {
                    $child['alternative_tag'] = $data['is_alternative'] ? __('Alternative') : '';
                    $indent = $child['depth'] * 20;
                    $child['indentation'] = <<<HTML
                    <div class="text-right indent-icon" style="width:{$indent}px;">
                    </div>
                    HTML;
                }

                $child['warehouse_id'] = $data['warehouse_id'];

                $this->data[] = $child;
            }
        }

        $this->skipRender();
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
            ->setContact(Contact::query()->whereKey($this->order['contact_id'])->first())
            ->price();

        $this->position['product_id'] = $id;
        $this->position['product_number'] = $product?->product_number;
        $this->position['name'] = $product?->name;
        $this->position['amount'] = 1;
        $this->position['vat_rate_id'] = $product?->vat_rate_id;
        $this->position['vat_rate_percentage'] = collect($this->vatRates)
            ->where('id', '=', $product?->vat_rate_id)
            ->first()['rate_percentage'] ?? null;
        $this->position['is_free_text'] = false;
        $this->position['unit_net_price'] = $price?->getNet($this->position['vat_rate_percentage']);
        $this->position['unit_gross_price'] = $price?->getGross($this->position['vat_rate_percentage']);
        $this->position['purchase_price'] = $product?->purchasePrice();

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

    public function addToGroup(): void
    {
        // TODO: Implement addToGroup() method.
    }

    public function remove(array|string $selected = null): void
    {
        $ids = $selected ? (array) $selected : $this->selected;
        $this->removeByKey($ids);
    }

    public function save(array $orderPosition): false|array
    {
        $this->skipRender();
        $bundleProducts = $orderPosition['children'] ?? [];

        $action = ($orderPosition['id'] ?? false) && is_numeric($orderPosition['id'])
            ? UpdateOrderPosition::class
            : CreateOrderPosition::class;
        try {
            $orderPosition = $action::make($orderPosition)->validate()->getData();
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }
        $orderPosition = $this->recalculatePosition($orderPosition);

        $orderPosition['children'] = $bundleProducts;

        if (! ($orderPosition['id'] ?? false)) {
            $sortNumber = $this->data ? max(array_column($this->data, 'sort_number')) + 1 : 1;
            $slugPosition = $this->data
                ? floor(max(array_column($this->data, 'slug_position'))) + 1
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

        if ($this->selectedIndex) {
            $this->replaceByIndex($orderPosition, $this->selectedIndex);
        } else {
            $this->addToBottom($orderPosition);
        }
        $this->showOrderPosition = false;
        $this->reset('position');

        return [
            'order' => $this->recalculateOrder(),
            'orderPositions' => $this->data,
        ];
    }

    public function recalculateOrder(): array
    {
        $order['total_net_price'] = 0;
        $order['total_gross_price'] = 0;
        $order['total_vats'] = [];

        foreach ($this->data as $orderPosition) {
            if (data_get($orderPosition, 'is_alternative')
                || data_get($orderPosition, 'is_bundle_position')
                || data_get($orderPosition, 'is_free_text')
            ) {
                continue;
            }

            $order['total_net_price'] = bcadd($order['total_net_price'], $orderPosition['total_net_price']);
            $order['total_gross_price'] = bcadd($order['total_gross_price'], $orderPosition['total_gross_price']);
            $order['total_vats'][$orderPosition['vat_rate_id']]['total_vat_price'] =
                bcadd(
                    $order['total_vats'][$orderPosition['vat_rate_id']]['total_vat_price'] ?? 0,
                    bcsub($orderPosition['total_gross_price'], $orderPosition['total_net_price'])
                );
            $order['total_vats'][$orderPosition['vat_rate_id']]['vat_rate_percentage'] =
                $orderPosition['vat_rate_percentage'];
        }

        $this->skipRender();

        return $order;
    }
}
