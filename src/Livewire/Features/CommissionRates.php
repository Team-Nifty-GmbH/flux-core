<?php

namespace FluxErp\Livewire\Features;

use FluxErp\Actions\CommissionRate\CreateCommissionRate;
use FluxErp\Actions\CommissionRate\DeleteCommissionRate;
use FluxErp\Actions\CommissionRate\UpdateCommissionRate;
use FluxErp\Livewire\DataTables\BaseDataTable;
use FluxErp\Models\Category;
use FluxErp\Models\CommissionRate;
use FluxErp\Models\Product;
use FluxErp\Rulesets\CommissionRate\CreateCommissionRateRuleset;
use FluxErp\Traits\Livewire\Actions;
use Livewire\Attributes\Locked;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class CommissionRates extends BaseDataTable
{
    use Actions;

    public array $categories;

    public array $columnLabels = [
        'user.name' => 'Commission Agent',
    ];

    public array $commissionRate = [
        'user_id' => null,
        'contact_id' => null,
        'category_id' => null,
        'product_id' => null,
        'commission_rate' => null,
    ];

    #[Locked]
    public ?int $contactId = null;

    public bool $create = true;

    public array $enabledCols = [
        'category.name',
        'product.name',
        'commission_rate',
    ];

    public bool $orderAsc = true;

    public string $orderBy = 'user_id';

    public bool $showModal = false;

    #[Locked]
    public ?int $userId = null;

    protected ?string $includeBefore = 'flux::livewire.features.commission-rates';

    protected $listeners = [
        'loadData',
        'setUserId',
    ];

    protected string $model = CommissionRate::class;

    public function mount(): void
    {
        $this->categories = resolve_static(Category::class, 'query')
            ->where('model_type', app(Product::class)->getMorphClass())
            ->get(['id', 'name'])
            ->toArray();

        $this->filters = $this->getFilters();

        if (! is_null($this->contactId)) {
            $this->headline = __('Commission Rates');
        }

        if (is_null($this->userId)) {
            array_unshift($this->enabledCols, 'user.name');
        }

        parent::mount();

        $this->formatters = array_merge(
            $this->formatters,
            [
                'commission_rate' => 'percentage',
            ]
        );
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create'))
                ->color('indigo')
                ->icon('plus')
                ->attributes([
                    'x-on:click' => '$wire.show()',
                ]),
        ];
    }

    public function delete(): void
    {
        try {
            DeleteCommissionRate::make($this->commissionRate)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->loadData();

        $this->showModal = false;
    }

    public function getFilters(): array
    {
        $filters = [];

        $filters[] = ($this->contactId ?? null) ? [
            'column' => 'contact_id',
            'operator' => '=',
            'value' => $this->contactId,
        ] : [
            'column' => 'contact_id',
            'operator' => 'is null',
        ];

        if ($this->userId) {
            $filters[] = [
                'column' => 'user_id',
                'operator' => '=',
                'value' => $this->userId,
            ];
        }

        return $filters;
    }

    public function save(): void
    {
        if ($this->commissionRate['commission_rate']) {
            $this->commissionRate['commission_rate'] /= 100;
        }

        $action = ($this->commissionRate['id'] ?? false) ? UpdateCommissionRate::class : CreateCommissionRate::class;

        try {
            $action::make($this->commissionRate)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            if ($this->commissionRate['commission_rate']) {
                $this->commissionRate['commission_rate'] *= 100;
            }

            return;
        }

        $this->loadData();

        $this->showModal = false;
    }

    public function setUserId(int $id): void
    {
        if ($this->showModal) {
            return;
        }

        $this->userId = $id;

        $this->filters = $this->getFilters();

        $this->loadData();
    }

    public function show(?int $id = null): void
    {
        if ($id) {
            $this->commissionRate = resolve_static(CommissionRate::class, 'query')
                ->whereKey($id)
                ->first()
                ->toArray();

            $this->commissionRate['commission_rate'] *= 100;
        } else {
            $this->commissionRate =
                array_fill_keys(
                    array_keys(resolve_static(CreateCommissionRateRuleset::class, 'getRules')),
                    null
                );

            $this->commissionRate['user_id'] = ($this->userId ?? null);
            $this->commissionRate['contact_id'] = ($this->contactId ?? null);
        }

        $this->create = is_null($id);
        $this->showModal = true;
    }

    public function updatedCommissionRateCategoryId(): void
    {
        $this->commissionRate['product_id'] = null;

        $this->skipRender();
    }

    public function updatedCommissionRateProductId(): void
    {
        $this->commissionRate['category_id'] = null;

        $this->skipRender();
    }

    protected function getBottomAppends(): array
    {
        return [
            'user.name' => 'user.email',
        ];
    }
}
