<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\Commission\CreateCommissionCreditNotes;
use FluxErp\Jobs\Accounting\CreateCommissionCreditNotesJob;
use FluxErp\Models\Commission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class CommissionList extends BaseDataTable
{
    protected string $model = Commission::class;

    public array $enabledCols = [
        'user.name',
        'order.order_number',
        'order.address_invoice.name',
        'order_position.name',
        'total_net_price',
        'commission_rate',
        'commission',
    ];

    public bool $hasNoRedirect = true;

    public bool $isSelectable = true;

    public array $columnLabels = [
        'user.name' => 'Commission Agent',
    ];

    public function mount(): void
    {
        parent::mount();

        $this->formatters = array_merge(
            $this->formatters,
            [
                'commission_rate' => 'percentage',
                'commission' => 'coloredMoney',
                'total_net_price' => 'coloredMoney',
            ]
        );
    }

    public function createCreditNotes(): bool
    {
        $selected = $this->getSelectedModelsQuery()
            ->whereHas('user', function (Builder $query) {
                $query->whereHas('contact');
            })
            ->whereDoesntHave('creditNoteOrderPosition')
            ->get(['id'])
            ->toArray();

        try {
            CreateCommissionCreditNotes::make($selected)
                ->validate();
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->selected = [];
        CreateCommissionCreditNotesJob::dispatch($selected);

        return true;
    }

    protected function itemToArray($item): array
    {
        $item->commission_rate = $item->commission_rate['commission_rate'];

        return parent::itemToArray($item);
    }

    protected function getReturnKeys(): array
    {
        return array_merge(
            parent::getReturnKeys(),
            [
                'order_id',
            ]
        );
    }

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->color('primary')
                ->label(__('Create credit notes'))
                ->attributes([
                    'wire:click' => 'createCreditNotes',
                    'wire:flux-confirm' => __('wire:confirm.commission-credit-notes'),
                ]),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make(label: __('View Order'))
                ->color('primary')
                ->icon('eye')
                ->href('#')
                ->attributes([
                    'x-bind:href' => '\'/orders/\' + record.order_id',
                ]),
        ];
    }
}
