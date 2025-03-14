<x-input
    autofocus
    placeholder="e.g. Invoice…"
    wire:model="paymentTypeForm.name"
    :label="__('Name')"
/>
<x-number
    min="1"
    step="1"
    placeholder="e.g. 5 for 5 days after invoice…"
    wire:model="paymentTypeForm.payment_target"
    :label="__('Payment Target')"
/>
<x-number
    min="1"
    step="1"
    placeholder="e.g. 5 for first payment reminder 5 days after payment target…"
    wire:model="paymentTypeForm.payment_reminder_days_1"
    :label="__('Payment Reminder Days 1')"
/>
<x-number
    min="1"
    step="1"
    placeholder="e.g. 5 for first payment reminder 5 days after first reminder…"
    wire:model="paymentTypeForm.payment_reminder_days_2"
    :label="__('Payment Reminder Days 2')"
/>
<x-number
    min="1"
    step="1"
    placeholder="e.g. 5 for first payment reminder 5 days after second reminder…"
    wire:model="paymentTypeForm.payment_reminder_days_3"
    :label="__('Payment Reminder Days 3')"
/>
