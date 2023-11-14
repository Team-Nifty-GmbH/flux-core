<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Transaction;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class TransactionList extends DataTable
{
    protected string $model = Transaction::class;

    protected string $view = 'flux::livewire.transactions.transactions';

    public array $enabledCols = [
        'value_date',
        'amount',
        'counterpart_name',
        'purpose',
        'order.invoice_number',
    ];

    public array $formatters = [
        'amount' => 'coloredMoney',
    ];

    public bool $showFilterInputs = true;

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Show unassigned payments'))
                ->color('primary')
                ->attributes([
                    'x-on:click' => '$wire.showUnassignedPayments()',
                ]),
        ];
    }
    public function showTransaction(Transaction $transaction): array
    {
        return $transaction->toArray();
    }
    public function showUnassignedPayments(): void
    {
        $this->userFilters = array_merge(
            $this->userFilters,
            [
                [
                    [
                        'column' => 'order_id',
                        'operator' => 'is null',
                        'value' => '',
                    ],
                ],
            ],
        );

        $this->loadData();
    }
}
