<?php

namespace FluxErp\View\Printing\PaymentReminder;

use FluxErp\Models\PaymentReminder;
use FluxErp\Models\PaymentReminderText;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;

class PaymentReminderView extends PrintableView
{
    public PaymentReminder $model;

    public ?PaymentReminderText $paymentReminderText;

    public function __construct(PaymentReminder $paymentReminder)
    {
        $this->model = $paymentReminder;
        $this->paymentReminderText = app(PaymentReminderText::class)
            ->where('reminder_level', '<=', $this->model->reminder_level)
            ->orderBy('reminder_level', 'desc')
            ->first();
    }

    public function getModel(): PaymentReminder
    {
        return $this->model;
    }

    public function render(): View|Factory
    {
        return view('print::payment-reminder.payment-reminder', [
            'model' => $this->model,
            'text' => Blade::render(
                html_entity_decode($this->paymentReminderText->reminder_body ?? ''),
                ['paymentReminder' => $this->model]
            ),
            'subject' => Blade::render(
                html_entity_decode($this->paymentReminderText->reminder_subject ?? $this->getSubject()),
                ['paymentReminder' => $this->model]
            ),
        ]);
    }

    public function getFileName(): string
    {
        return $this->getSubject();
    }

    public function getSubject(): string
    {
        return $this->model->reminder_level . ' '
            . __('Payment Reminder') . ' '
            . $this->model->order->orderType->name . ' '
            . $this->model->order->invoice_number;
    }

    protected function getCollectionName(): string
    {
        return 'payment-reminders';
    }
}
