<x-modal name="edit-payment-reminder-text">
    <x-card class="flex flex-col gap-4">
        <x-inputs.number
            :label="__('Minimum reminder level')"
            wire:model.number="paymentReminderTextForm.reminder_level"
        />
        <x-input
            :label="__('Payment Reminder Subject')"
            wire:model="paymentReminderTextForm.reminder_subject"
        />
        <x-editor
            :label="__('Payment Reminder Text')"
            wire:model="paymentReminderTextForm.reminder_body"
        />
        <x-input
            :label="__('Payment Reminder Email To')"
            wire:model="paymentReminderTextForm.mail_to"
            :placeholder="__('Leave empty to send to the customer.')"
        />
        <x-input :label="__('Payment Reminder Email CC')"
             wire:model="paymentReminderTextForm.mail_cc"
        />
        <x-input :label="__('Payment Reminder Email Subject')"
             wire:model="paymentReminderTextForm.mail_subject"
        />
        <x-editor :label="__('Payment Reminder Email Text')"
              wire:model="paymentReminderTextForm.mail_body"
        />
        <x-slot:footer>
            <div class="flex justify-end gap-x-4">
                <x-button flat :label="__('Cancel')" x-on:click="close"/>
                <x-button
                    primary
                    :label="__('Save')"
                    wire:click="save().then((success) => {
                            if (success) close();
                        })"
                />
            </div>
        </x-slot:footer>
    </x-card>
</x-modal>
