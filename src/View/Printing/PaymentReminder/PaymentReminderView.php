<?php

namespace FluxErp\View\Printing\PaymentReminder;

use FluxErp\Models\PaymentReminder;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class PaymentReminderView extends PrintableView
{
    public PaymentReminder $model;

    public function __construct(PaymentReminder $paymentReminder)
    {
        $this->model = $paymentReminder;
    }

    public function getModel(): PaymentReminder
    {
        return $this->model;
    }

    public function render(): View|Factory
    {
        return view('print::payment-reminder.payment-reminder', [
            'model' => $this->model,
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
