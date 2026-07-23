<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Loan\CreateLoan;
use FluxErp\Actions\Loan\DeleteLoan;
use FluxErp\Actions\Loan\UpdateLoan;
use Livewire\Attributes\Locked;

class LoanForm extends FluxForm
{
    public ?float $amount = null;

    public ?int $contact_id = null;

    public ?string $ends_at = null;

    #[Locked]
    public ?int $id = null;

    public ?float $installment_amount = null;

    public ?float $interest_rate = null;

    public ?int $ledger_account_id = null;

    public ?string $name = null;

    public ?string $note = null;

    public ?int $number_of_installments = null;

    public ?string $number = null;

    public ?int $order_id = null;

    public ?string $repayment_type_enum = null;

    public ?string $starts_at = null;

    public ?int $tenant_id = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateLoan::class,
            'update' => UpdateLoan::class,
            'delete' => DeleteLoan::class,
        ];
    }
}
