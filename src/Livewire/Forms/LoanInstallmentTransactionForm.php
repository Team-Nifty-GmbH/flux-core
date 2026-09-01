<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\LoanInstallmentTransaction\CreateLoanInstallmentTransaction;
use FluxErp\Actions\LoanInstallmentTransaction\DeleteLoanInstallmentTransaction;
use FluxErp\Actions\LoanInstallmentTransaction\UpdateLoanInstallmentTransaction;
use FluxErp\Support\Livewire\Attributes\ExcludeFromActionData;
use Livewire\Attributes\Locked;

class LoanInstallmentTransactionForm extends FluxForm
{
    public ?float $amount = null;

    public bool $is_accepted = true;

    public ?int $loan_installment_id = null;

    public ?string $note = null;

    #[Locked]
    public ?int $pivot_id = null;

    #[Locked]
    public ?int $transaction_id = null;

    #[Locked]
    #[ExcludeFromActionData]
    public ?float $transactionBalance = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateLoanInstallmentTransaction::class,
            'update' => UpdateLoanInstallmentTransaction::class,
            'delete' => DeleteLoanInstallmentTransaction::class,
        ];
    }

    protected function getKey(): string
    {
        return 'pivot_id';
    }
}
