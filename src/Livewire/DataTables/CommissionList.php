<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\Commission\CreateCommissionCreditNotes;
use FluxErp\Models\Commission;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class CommissionList extends BaseDataTable
{
    public array $columnLabels = [
        'user.name' => 'Commission Agent',
    ];

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

    protected string $model = Commission::class;

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

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('View Order'))
                ->color('indigo')
                ->icon('eye')
                ->href('#')
                ->attributes([
                    'x-bind:href' => '\'/orders/\' + record.order_id',
                ]),
        ];
    }

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->color('indigo')
                ->text(__('Create credit notes'))
                ->attributes([
                    'wire:click' => 'createCreditNotes',
                    'wire:flux-confirm' => __('wire:confirm.commission-credit-notes'),
                ]),
        ];
    }

    public function createCreditNotes(): bool
    {
        $selected = $this->getSelectedModelsQuery()
            ->whereHas('user')
            ->whereDoesntHave('creditNoteOrderPosition')
            ->get(['id'])
            ->toArray();

        try {
            CreateCommissionCreditNotes::make(['commissions' => $selected])
                ->checkPermission()
                ->validate()
                ->executeAsync();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->reset('selected');

        return true;
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

    protected function itemToArray($item): array
    {
        $item->commission_rate = $item->commission_rate['commission_rate'];

        return parent::itemToArray($item);
    }
}
