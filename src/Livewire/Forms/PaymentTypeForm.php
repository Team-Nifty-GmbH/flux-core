<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\PaymentType\CreatePaymentType;
use FluxErp\Actions\PaymentType\DeletePaymentType;
use FluxErp\Actions\PaymentType\UpdatePaymentType;
use Livewire\Attributes\Locked;

class PaymentTypeForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $client_id = null;

    public ?string $name = null;

    public ?string $description = null;

    public ?int $payment_reminder_days_1 = null;

    public ?int $payment_reminder_days_2 = null;

    public ?int $payment_reminder_days_3 = null;

    public ?int $payment_target = null;

    public ?int $payment_discount_target = null;

    public ?int $payment_discount_percentage = null;

    public ?string $payment_reminder_text = null;

    public ?string $payment_reminder_email_text = null;

    public bool $is_active = true;

    public bool $is_default = false;

    protected function getActions(): array
    {
        return [
            'create' => CreatePaymentType::class,
            'update' => UpdatePaymentType::class,
            'delete' => DeletePaymentType::class,
        ];
    }
}
