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

    public ?bool $isSingleBooking = false;

    public ?bool $isInstantPayment = false;

    #[Locked]
    public array $orders = [];

    #[Locked]
    public ?string $orders_sum_order_payment_runamount = null;

    protected function getActions(): array
    {
        return [
            'create' => CreatePaymentRun::class,
            'update' => UpdatePaymentRun::class,
            'delete' => DeletePaymentRun::class,
        ];
    }
}
