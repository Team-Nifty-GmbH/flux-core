@php use FluxErp\Facades\EditorVariable;use FluxErp\Models\PaymentReminder; @endphp
<x-modal id="edit-payment-reminder-text-modal">
    <div class="flex flex-col gap-1.5">
        <x-number
            :label="__('Minimum reminder level')"
            wire:model.number="paymentReminderTextForm.reminder_level"
        />
        <x-input
            :label="__('Payment Reminder Subject')"
            wire:model="paymentReminderTextForm.reminder_subject"
        />
        <x-flux::editor
            :label="__('Payment Reminder Text')"
            wire:model="paymentReminderTextForm.reminder_body"
            :blade-variables="EditorVariable::getTranslatedWithGlobals(PaymentReminder::class)"
        />
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('edit-payment-reminder-text-modal')"
            />
            <x-button
                color="indigo"
                :text="__('Save')"
                wire:click="save().then((success) => { if (success) $modalClose('edit-payment-reminder-text-modal'); })"
            />
        </x-slot>
    </div>
</x-modal>
