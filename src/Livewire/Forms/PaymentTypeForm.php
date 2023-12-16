<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\PaymentType\CreatePaymentType;
use FluxErp\Actions\PaymentType\UpdatePaymentType;
use Livewire\Attributes\Locked;
use Livewire\Form;

class PaymentTypeForm extends Form
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

    public bool $is_active = true;

    public function save(): void
    {
        $action = $this->id ? UpdatePaymentType::make($this->toArray()) : CreatePaymentType::make($this->toArray());

        $response = $action->validate()->execute();

        $this->fill($response);
    }
}
