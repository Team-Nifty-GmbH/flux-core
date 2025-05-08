<?php

namespace FluxErp\Livewire\Features;

use FluxErp\Livewire\DataTables\CommissionRateList;
use FluxErp\Livewire\Forms\CommissionRateForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;
use Livewire\Attributes\Locked;

class CommissionRates extends CommissionRateList
{
    use Actions, DataTableHasFormEdit {
        DataTableHasFormEdit::save as parentSave;
        DataTableHasFormEdit::edit as parentEdit;
    }

    public array $columnLabels = [
        'user.name' => 'Commission Agent',
    ];

    #[DataTableForm]
    public CommissionRateForm $commissionRate;

    #[Locked]
    public ?int $contactId = null;

    public bool $orderAsc = true;

    public string $orderBy = 'user_id';

    #[Locked]
    public ?int $userId = null;

    protected ?string $includeBefore = 'flux::livewire.features.commission-rates';

    public function mount(): void
    {
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

    public function save(): bool
    {
        $this->commissionRate->contact_id ??= $this->contactId;
        $this->commissionRate->user_id ??= $this->userId;

        return $this->parentSave();
    }

    public function updatedCommissionRateCategoryId(): void
    {
        $this->commissionRate->product_id = null;

        $this->skipRender();
    }

    public function updatedCommissionRateProductId(): void
    {
        $this->commissionRate->category_id = null;

        $this->skipRender();
    }

    protected function getBottomAppends(): array
    {
        return [
            'user.name' => 'user.email',
        ];
    }
}
