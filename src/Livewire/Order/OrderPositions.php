<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Actions\OrderPosition\DeleteOrderPosition;
use FluxErp\Actions\OrderPosition\UpdateOrderPosition;
use FluxErp\Actions\Task\CreateTask;
use FluxErp\Helpers\PriceHelper;
use FluxErp\Livewire\DataTables\OrderPositionList;
use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Livewire\Forms\OrderPositionForm;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\PriceList;
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
    public ?string $cacheKey = 'order.order-positions';

    public ?string $discount = null;

    public array $enabledCols = [
        'slug_position',
        'name',
        'unit_net_price',
        'amount',
        'total_net_price',
    ];

    public bool $hasNoRedirect = true;

    public ?bool $isSearchable = false;

    public bool $isSelectable = true;

    #[Modelable]
    public OrderForm $order;

    public OrderPositionForm $orderPosition;

    public string $orderPositionsView = 'table';

    public int $perPage = 100;

    public array $sortable = [];

    protected ?string $includeAfter = 'flux::livewire.order.order-list-footer';

    protected ?string $includeBefore = 'flux::livewire.order.order-list-header';

    public function mount(): void
    {
        parent::mount();

        $this->reset('filters', 'selected');
        $this->page = 1;
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->icon('bars-3')
                ->wireClick('switchView(\'list\')'),
            DataTableButton::make()
                ->icon('table-cells')
                ->wireClick('switchView(\'table\')'),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->icon('pencil')
                ->color('indigo')
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
                    'wire:click' => 'showProduct(record.product_id)',
                    'x-cloak' => true,
                    'x-show' => 'record.product_id',
                ]),
        ];
    }

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create tasks'))
                ->when(fn () => resolve_static(CreateTask::class, 'canPerformAction', [false]))
                ->xOnClick('$modalOpen(\'create-tasks\')'),
            DataTableButton::make()
                ->text(__('Recalculate prices'))
                ->when(fn () => resolve_static(UpdateOrderPosition::class, 'canPerformAction', [false])
                    && ! $this->order->is_locked
                )
                ->attributes([
                    'wire:flux-confirm.type.warning' => __(
                        'Recalculate prices|Are you sure you want to recalculate the prices?|Cancel|Confirm'
                    ),
                    'wire:click' => 'recalculateOrderPositions(); showSelectedActions = false;',
                ]),
            DataTableButton::make()
                ->text(__('Discount selected positions'))
                ->when(fn () => resolve_static(UpdateOrderPosition::class, 'canPerformAction', [false])
                    && ! $this->order->is_locked
                )
                ->xOnClick(<<<'JS'
                    $modalOpen('edit-position-discount');
                JS),
            DataTableButton::make()
                ->text(__('Replicate'))
                ->when(fn () => resolve_static(CreateOrderPosition::class, 'canPerformAction', [false])
                    && ! $this->order->is_locked
                )
                ->wireClick('replicateSelected()'),
            DataTableButton::make()
                ->text(__('Delete'))
                ->icon('trash')
                ->color('red')
                ->when(fn () => resolve_static(DeleteOrderPosition::class, 'canPerformAction', [false])
                    && ! $this->order->is_locked
                )
                ->attributes([
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Order positions')]),
                    'wire:click' => 'deleteSelectedOrderPositions(); showSelectedActions = false;',
                ]),
        ];
    }

    #[Renderless]
    public function addOrderPosition(bool $reload = true): bool
    {
        if ($this->orderPositionsView !== 'table') {
            $this->forceRender();
        }

        $this->orderPosition->order_id = $this->order->id;
        $this->orderPosition->vat_rate_id = $this->order->vat_rate_id ?? $this->orderPosition->vat_rate_id;

        try {
            $this->orderPosition->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        if ($reload) {
            $this->recalculateOrderTotals();
            $this->loadData();
        }

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
    public function changedProductId(Product $product): void
    {
        $priceList = $this->orderPosition->price_list_id
            ? resolve_static(PriceList::class, 'query')
                ->whereKey($this->orderPosition->price_list_id)
                ->first([
                    'id',
                    'parent_id',
                    'rounding_method_enum',
                    'rounding_precision',
                    'rounding_number',
                    'rounding_mode',
                    'is_net',
                ])
            : $this->order->getPriceList();
        $this->orderPosition->fillFromProduct($product);
        $this->orderPosition->is_net = $this->order->getPriceList()->is_net;
        $this->orderPosition->unit_price = PriceHelper::make($this->orderPosition->getProduct())
            ->setPriceList($priceList ?? $this->order->getPriceList())
            ->setContact($this->order->getContact())
            ->price()
            ?->price ?? 0;
    }

    #[Renderless]
    public function createTasks(int $projectId): void
    {
        foreach ($this->getSelectedModelsQuery()->get(['id', 'name', 'description']) as $orderPosition) {
            // check if the task already exists or the selected order position is not a numeric value
            if (resolve_static(Task::class, 'query')
                ->where('project_id', $projectId)
                ->where('order_position_id', $modelId = $orderPosition->getKey())
                ->where('model_type', $modelType = $orderPosition->getMorphClass())
                ->where('model_id', $modelId)
                ->exists()
            ) {
                continue;
            }

            try {
                CreateTask::make([
                    'project_id' => $projectId,
                    'responsible_user_id' => auth()->id(),
                    'order_position_id' => $modelId,
                    'model_type' => $modelType,
                    'model_id' => $modelId,
                    'name' => $orderPosition->name,
                    'description' => $orderPosition->description,
                    'users' => [
                        auth()->id(),
                    ],
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
    public function deleteOrderPosition(): bool
    {
        if ($this->orderPositionsView !== 'table') {
            $this->forceRender();
        }

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

    #[Renderless]
    public function deleteSelectedOrderPositions(): void
    {
        try {
            $this->getSelectedModelsQuery()->pluck('id')->each(function (int $id): void {
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
    public function discountSelectedPositions(): void
    {
        $discount = bcdiv($this->discount, 100);
        foreach ($this->getSelectedModelsQuery()->get(['id']) as $orderPositions) {
            try {
                UpdateOrderPosition::make([
                    'id' => $orderPositions->id,
                    'discount_percentage' => $discount,
                ])
                    ->checkPermission()
                    ->validate()
                    ->execute();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);
            }
        }

        $this->loadData();
        $this->recalculateOrderTotals();
        $this->discount = null;
    }

    #[Renderless]
    public function editOrderPosition(?OrderPosition $orderPosition = null): void
    {
        $this->orderPosition->is_net = $this->order->getPriceList()->is_net;
        if ($orderPosition->exists) {
            $this->orderPosition->fill($orderPosition);
        } else {
            $this->orderPosition->vat_rate_id ??= resolve_static(VatRate::class, 'default')->getKey();
        }

        $this->js(<<<'JS'
            $modalOpen('edit-order-position');
        JS);
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->where('order_id', $this->order->id)->reorder('slug_position');
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

    public function getListeners(): array
    {
        return array_merge(
            parent::getListeners(),
            [
                'create-tasks' => 'createTasks',
                'order:add-products' => 'addProducts',
            ]
        );
    }

    public function getSelectAttributes(): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'x-show' => '! record.is_bundle_position && ! record.is_locked',
        ]);
    }

    public function getSortableOrderPositions(): array
    {
        resolve_static(OrderPosition::class, 'addGlobalScope', [
            'scope' => 'sorted',
            'implementation' => function (Builder $query): void {
                $query->ordered();
            },
        ]);

        return resolve_static(OrderPosition::class, 'familyTree')
            ->where('order_id', $this->order->id)
            ->whereNull('parent_id')
            ->get()
            ->toArray();
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

    public function movePosition(OrderPosition $position, int $newPosition, ?int $parentId = null): void
    {
        $newPosition = $newPosition + 1;
        if ($position->parent_id === $parentId && $position->sort_number === $newPosition) {
            return;
        }

        try {
            UpdateOrderPosition::make([
                'id' => $position->id,
                'parent_id' => $parentId,
                'sort_number' => $newPosition,
            ])
                ->validate()
                ->execute();

            $this->forceRender();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }
    }

    #[Renderless]
    public function quickAdd(): bool
    {
        $this->orderPosition->fillFromProduct();

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
    public function replicateSelected(): void
    {
        $this->orderPosition->reset();
        foreach ($this->getSelectedModelsQuery()
            ->where('is_bundle_position', false)
            ->get() as $orderPosition
        ) {
            $this->orderPosition->fill($orderPosition);
            $this->orderPosition->reset(
                'id',
                'origin_position_id',
                'parent_id',
                'sort_number',
                'slug_position',
            );
            $this->addOrderPosition(false);
        }

        $this->reset('selected');
        $this->recalculateOrderTotals();
        $this->loadData();
    }

    #[Renderless]
    public function resetOrderPosition(): void
    {
        $this->orderPosition->reset();
    }

    #[Renderless]
    public function showProduct(Product $product): void
    {
        $this->js(<<<JS
            \$openDetailModal('{$product->getUrl()}');
        JS);
    }

    public function switchView(string $view): void
    {
        if ($view === $this->orderPositionsView) {
            return;
        }

        if ($view !== 'table') {
            $this->data = [];
        } else {
            $this->loadData();
        }

        $this->forceRender();

        $this->orderPositionsView = $view;

        $this->cacheState();
    }

    protected function compileStoredLayout(): array
    {
        $savedFilter = parent::compileStoredLayout();

        data_set($savedFilter, 'settings.orderPositionsView', $this->orderPositionsView);

        return $savedFilter;
    }

    protected function getLayout(): string
    {
        return $this->orderPositionsView === 'table'
            ? 'tall-datatables::layouts.table'
            : 'flux::order.sort-order-positions';
    }

    protected function getLeftAppends(): array
    {
        return [
            'name' => 'indentation',
        ];
    }

    protected function getReturnKeys(): array
    {
        return array_merge(
            parent::getReturnKeys(),
            [
                'tenant_id',
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

    protected function recalculateOrderTotals(): void
    {
        $this->js(<<<'JS'
            $wire.$parent.recalculateOrderTotals();
        JS);
    }
}
