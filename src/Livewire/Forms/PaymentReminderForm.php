<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\PaymentReminder\CreatePaymentReminder;
use FluxErp\Actions\PaymentReminder\DeletePaymentReminder;
use FluxErp\Actions\PaymentReminder\UpdatePaymentReminder;
use Livewire\Attributes\Locked;

class PaymentReminderForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $media_id = null;

    public ?int $order_id = null;

    public ?int $reminder_level = null;

    protected function getActions(): array
    {
        return [
            'create' => CreatePaymentReminder::class,
            'update' => UpdatePaymentReminder::class,
            'delete' => DeletePaymentReminder::class,
        ];
    }
}
