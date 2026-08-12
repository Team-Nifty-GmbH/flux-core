<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Loan\FinanceOrder;
use Livewire\Attributes\Locked;

class FinanceOrderForm extends FluxForm
{
    public ?float $amount = null;

    public ?string $booking_date = null;

    public ?string $booking_text = null;

    public ?int $debit_ledger_account_id = null;

    #[Locked]
    public ?int $loan_id = null;

    public ?string $note = null;

    public ?int $order_id = null;

    protected function getActions(): array
    {
        return [
            'create' => FinanceOrder::class,
        ];
    }
}
