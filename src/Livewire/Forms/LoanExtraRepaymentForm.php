<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\LoanExtraRepayment\CreateLoanExtraRepayment;
use FluxErp\Enums\ScheduleAdjustmentTypeEnum;
use Livewire\Attributes\Locked;

class LoanExtraRepaymentForm extends FluxForm
{
    public ?float $amount = null;

    public ?string $executed_at = null;

    #[Locked]
    public ?int $id = null;

    #[Locked]
    public ?int $loan_id = null;

    public ?string $note = null;

    public ?string $schedule_adjustment_type_enum = ScheduleAdjustmentTypeEnum::ShortenTerm->value;

    protected function getActions(): array
    {
        return [
            'create' => CreateLoanExtraRepayment::class,
        ];
    }
}
