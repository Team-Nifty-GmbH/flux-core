<x-layouts.print>
    <x-print.first-page-header :address="$model->order->addressInvoice" />
    <main>
        <div>
            {!! $model->order->paymentType->payment_reminder_text ?? $model->order->paymentType->payment_reminder_email_text !!}
        </div>
    </main>
</x-layouts.print>
