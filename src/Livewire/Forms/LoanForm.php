<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Loan\CreateLoan;
use FluxErp\Actions\Loan\DeleteLoan;
use FluxErp\Actions\Loan\UpdateLoan;
use Livewire\Attributes\Locked;

class LoanForm extends FluxForm
{
    public bool $allows_extra_repayments = true;

    public ?float $amount = null;

    public ?int $contact_id = null;

    public ?string $ends_at = null;

    public ?float $extra_repayment_allowance_amount = null;

    public ?float $extra_repayment_allowance_percentage = null;

    public ?int $grace_period_installments = null;

    #[Locked]
    public ?int $id = null;

    public ?float $installment_amount = null;

    public ?string $installment_interval_enum = null;

    public ?float $interest_rate = null;

    public ?int $ledger_account_id = null;

    public ?string $name = null;

    public ?int $number_of_installments = null;

    public ?string $number = null;

    public ?int $order_id = null;

    public ?string $repayment_type_enum = null;

    public ?string $starts_at = null;

    public ?int $tenant_id = null;

    public function fill($values): void
    {
        parent::fill($values);

        // the interest rate is stored as a factor, but entered as a percentage.
        // sprintf() keeps the float out of scientific notation, bcmath rejects
        // "1.0E-5" with a ValueError
        $this->interest_rate = is_null($this->interest_rate)
            ? null
            : bcmul(sprintf('%.10F', $this->interest_rate), '100', 8);
    }

    public function toActionData(): array
    {
        $data = parent::toActionData();
        $data['interest_rate'] = is_null($this->interest_rate)
            ? null
            : bcdiv(sprintf('%.10F', $this->interest_rate), '100', 10);

        return $data;
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateLoan::class,
            'update' => UpdateLoan::class,
            'delete' => DeleteLoan::class,
        ];
    }
}
