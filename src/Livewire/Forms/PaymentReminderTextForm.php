<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\PaymentReminderText\CreatePaymentReminderText;
use FluxErp\Actions\PaymentReminderText\DeletePaymentReminderText;
use FluxErp\Actions\PaymentReminderText\UpdatePaymentReminderText;

class PaymentReminderTextForm extends FluxForm
{
    public ?int $id = null;

    public ?string $reminder_body = null;

    public ?int $reminder_level = null;

    public ?string $reminder_subject = null;

    protected function getActions(): array
    {
        return [
            'create' => CreatePaymentReminderText::class,
            'update' => UpdatePaymentReminderText::class,
            'delete' => DeletePaymentReminderText::class,
        ];
    }
}
