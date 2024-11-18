<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Actions\OrderPosition\DeleteOrderPosition;
use FluxErp\Actions\OrderPosition\UpdateOrderPosition;
use FluxErp\Actions\Task\CreateTask;
use FluxErp\Helpers\PriceHelper;
use FluxErp\Livewire\DataTables\OrderPositionList;
use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Livewire\Forms\OrderPositionForm;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Product;
use FluxErp\Models\Task;
use FluxErp\Models\VatRate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\View\ComponentAttributeBag;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableRowAttributes;

class OrderPositions extends OrderPositionList
{
    #[Modelable]
    public OrderForm $order;

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

    public array $sortable = [];

    public OrderPositionForm $orderPosition;

    protected string $view = 'flux::livewire.order.order-positions';

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->where('order_id', $this->order->id)->reorder('slug_position');
    }

    public function getListeners(): array
    {
        return array_merge(
            parent::getListeners(),
            [
                'order:add-products' => 'addProducts',
            ]
        );
    }

    public function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'vatRates' => resolve_static(VatRate::class, 'query')
                    ->get(['id', 'name', 'rate_percentage'])
                    ->toArray(),
            ]
        );
    }

    public function getSelectAttributes(): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'x-show' => '! record.is_bundle_position && ! record.is_locked',
        ]);
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

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->icon('pencil')
                ->color('primary')
                ->when(fn () => resolve_static(UpdateOrderPosition::class, 'canPerformAction', [false])
                    && ! $this->order->is_locked
                )
                ->attributes([
                    'wire:click' => <<<'JS'
                            editOrderPosition(record.id);
                        JS,
                    'x-show' => '! record.is_bundle_position',
                    'x-cloak' => true,
                ]),
            DataTableButton::make()
                ->icon('eye')
                ->attributes([
                    'x-cloak' => 'true',
                    'x-show' => 'record.product_id',
                    'wire:click' => 'showProduct(record.product_id)',
                ]),
        ];
    }

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Delete'))
                ->icon('trash')
                ->color('negative')
                ->when(fn () => resolve_static(DeleteOrderPosition::class, 'canPerformAction', [false])
                    && ! $this->order->is_locked
                )
                ->attributes([
                    'wire:flux-confirm.icon.error' => __('wire:confirm.delete', ['model' => __('Order positions')]),
                    'wire:click' => 'deleteSelectedOrderPositions(); showSelectedActions = false;',
                ]),
            DataTableButton::make()
                ->label(__('Create tasks'))
                ->when(fn () => resolve_static(CreateTask::class, 'canPerformAction', [false]))
                ->xOnClick('$openModal(\'create-tasks\')'),
            DataTableButton::make()
                ->label(__('Recalculate prices'))
                ->when(fn () => resolve_static(UpdateOrderPosition::class, 'canPerformAction', [false])
                    && ! $this->order->is_locked
                )
                ->attributes([
                    'wire:flux-confirm.icon.warning' => __(
                        'Recalculate prices|Are you sure you want to recalculate the prices?|Cancel|Confirm'
                    ),
                    'wire:click' => 'recalculateOrderPositions(); showSelectedActions = false;',
                ]),
        ];
    }

    public function getFormatters(): array
    {
        return array_merge(
            parent::getFormatters(),
            [
                'slug_position' => 'string',
                'alternative_tag' => ['state', [__('Alternative') => 'negative']],
            ]
        );
    }

    protected function getLeftAppends(): array
    {
        return [
            'name' => 'indentation',
        ];
    }

    protected function getRightAppends(): array
    {
        return [
            'name' => 'alternative_tag',
        ];
    }

    protected function getTopAppends(): array
    {
        return [
            'name' => 'product_number',
        ];
    }

    protected function getReturnKeys(): array
    {
        return array_merge(
            parent::getReturnKeys(),
            [
                'client_id',
                'ledger_account_id',
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
                'purchase_price',
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
                'indentation',
            ]
        );
    }

    protected function itemToArray($item): array
    {
        $item = parent::itemToArray($item);

        $item['indentation'] = '';
        $item['unit_price'] = data_get($item, 'is_net')
            ? data_get($item, 'unit_net_price', 0)
            : data_get($item, 'unit_gross_price', 0);
        $item['alternative_tag'] = data_get($item, 'is_alternative') ? __('Alternative') : '';

        if (($depth = str_word_count(data_get($item, 'slug_position', ''), 0, '.')) > 0) {
            $indent = $depth * 20;
            $item['indentation'] = <<<HTML
                    <div class="text-right indent-icon" style="width:{$indent}px;">
                    </div>
                    HTML;
        }

        return $item;
    }

    #[Renderless]
    public function showProduct(Product $product): void
    {
        $this->js(<<<JS
            \$openDetailModal('{$product->getUrl()}');
        JS);
    }

    #[Renderless]
    public function createTasks(int $projectId): void
    {
        foreach ($this->getSelectedModelsQuery()->get(['id', 'name', 'description']) as $orderPosition) {
            // check if the task already exists or the selected order position is not a numeric value
            if (resolve_static(Task::class, 'query')
                ->where('project_id', $projectId)
                ->where('model_type', morph_alias(OrderPosition::class))
                ->where('model_id', $orderPosition->getKey())
                ->exists()
            ) {
                continue;
            }

            try {
                CreateTask::make([
                    'project_id' => $projectId,
                    'model_type' => morph_alias(OrderPosition::class),
                    'model_id' => $orderPosition->getKey(),
                    'name' => $orderPosition->name,
                    'description' => $orderPosition->description,
                ])
                    ->checkPermission()
                    ->validate()
                    ->execute();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);
            }
        }
    }

    #[Renderless]
    public function editOrderPosition(?OrderPosition $orderPosition = null): void
    {
        $this->orderPosition->fill($orderPosition);

        $this->js(<<<'JS'
            $openModal('edit-order-position');
        JS);
    }

    #[Renderless]
    public function changedProductId(Product $product): void
    {
        $this->orderPosition->fillFromProduct($product);
    }

    #[Renderless]
    public function addOrderPosition(): bool
    {
        $this->orderPosition->order_id = $this->order->id;

        try {
            $this->orderPosition->save();
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->recalculateOrderTotals();
        $this->loadData();
        $this->orderPosition->reset();

        return true;
    }

    #[Renderless]
    public function addProducts(array|int $products): void
    {
        foreach (Arr::wrap($products) as $product) {
            if (is_array($product)) {
                $this->orderPosition->fill($product);
            } else {
                $this->orderPosition->product_id = $product;
            }

            $this->quickAdd();
        }
    }

    #[Renderless]
    public function resetOrderPosition(): void
    {
        $this->orderPosition->reset();
    }

    #[Renderless]
    public function quickAdd(): bool
    {
        $this->orderPosition->fillFromProduct();

        $this->orderPosition->unit_price = PriceHelper::make($this->orderPosition->getProduct())
            ->setPriceList($this->order->getPriceList())
            ->setContact($this->order->getContact())
            ->price()
            ?->price
            ?? 0;

        return $this->addOrderPosition();
    }

    #[Renderless]
    public function recalculateOrderPositions(): void
    {
        $products = resolve_static(Product::class, 'query')
            ->whereIntegerInRaw('id', $this->getSelectedModelsQuery()->pluck('product_id'))
            ->get(['id', 'vat_rate_id'])
            ->keyBy('id');

        $contact = $this->order->getContact();
        $priceList = $this->order->getPriceList();

        foreach ($this->getSelectedModels() as $orderPosition) {
            if (data_get($orderPosition, 'is_bundle_position') || ! data_get($orderPosition, 'product_id')) {
                continue;
            }

            $this->orderPosition->reset();
            $this->orderPosition->fill($orderPosition);

            $this->orderPosition->unit_price = PriceHelper::make($products[$orderPosition['product_id']])
                ->setPriceList($priceList)
                ->setContact($contact)
                ->price()
                ->price;

            $this->orderPosition->save();
        }

        $this->loadData();
        $this->recalculateOrderTotals();

        $this->orderPosition->reset();
    }

    #[Renderless]
    public function deleteSelectedOrderPositions(): void
    {
        try {
            $this->getSelectedModelsQuery()->pluck('id')->each(function (int $id) {
                DeleteOrderPosition::make(['id' => $id])
                    ->checkPermission()
                    ->validate()
                    ->execute();
            });
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }

        $this->loadData();
        $this->recalculateOrderTotals();

        $this->reset('selected');
    }

    #[Renderless]
    public function deleteOrderPosition(): bool
    {
        try {
            $this->orderPosition->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();
        $this->recalculateOrderTotals();

        return true;
    }

    protected function recalculateOrderTotals(): void
    {
        $this->js(<<<'JS'
            $wire.$parent.recalculateOrderTotals();
        JS);
    }
}
