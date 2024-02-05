<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Payment\CreatePaymentRun;
use FluxErp\Actions\Payment\DeletePaymentRun;
use FluxErp\Actions\Payment\UpdatePaymentRun;
use Livewire\Attributes\Locked;

class PaymentRunForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $bank_connection_id = null;

    #[Locked]
    public ?string $payment_run_type_enum = null;

    public ?string $instructed_execution_date = null;

    public ?bool $is_single_booking = false;

    public ?bool $is_instant_payment = false;

    #[Locked]
    public array $orders = [];

    #[Locked]
    public ?string $total_amount = null;

    protected function getActions(): array
    {
        return [
            'create' => CreatePaymentRun::class,
            'update' => UpdatePaymentRun::class,
            'delete' => DeletePaymentRun::class,
        ];
    }
}
