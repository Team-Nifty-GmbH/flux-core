<x-modal id="edit-payment-reminder-text-modal">
    <div
        class="flex flex-col gap-4"
        x-data="{
            addReceiver($event, type) {
                let value = $event.target.value;
                if ($event instanceof KeyboardEvent && $event.which !== 13) {
                    value = value.slice(0, -1);
                }

                value = value.trim();

                if (value && ($event instanceof FocusEvent || ($event.code === 'Comma' || $event.code === 'Enter' || $event.code === 'Space'))) {
                    if (! Array.isArray($wire.paymentReminderTextForm[type])) {
                        $wire.paymentReminderTextForm[type] = [];
                    }

                    const email = value.match(/<([^>]*)>/);
                    if (email && email[1]) {
                        value = email[1];
                    }

                    $wire.paymentReminderTextForm[type].push(value);
                    $event.target.value = null;
                }
            }
        }"
    >
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
        />
        <div class="flex gap-1">
            <template x-for="to in $wire.paymentReminderTextForm.mail_to || []">
                <x-badge flat color="indigo" cl>
                    <x-slot:text>
                        <span x-text="to"></span>
                    </x-slot:text>
                    <x-slot
                        name="right"
                        class="relative flex items-center w-2 h-2"
                    >
                        <button
                            type="button"
                            x-on:click="$wire.paymentReminderTextForm.mail_to.splice($wire.paymentReminderTextForm.mail_to.indexOf(to), 1)"
                        >
                            <x-icon
                                name="x-mark"
                                class="w-4 h-4"
                            />
                        </button>
                    </x-slot>
                </x-badge>
            </template>
        </div>
        <x-input
            :text="__('Payment Reminder Email To')"
            :placeholder="__('Add a new to')"
            x-on:blur="addReceiver($event, 'mail_to')"
            x-on:keyup="addReceiver($event, 'mail_to')"
            :placeholder="__('Leave empty to send to the customer.')"
        />
        <div class="flex gap-1">
            <template x-for="to in $wire.paymentReminderTextForm.mail_cc || []">
                <x-badge flat color="indigo" cl>
                    <x-slot:text>
                        <span x-text="to"></span>
                    </x-slot:text>
                    <x-slot
                        name="right"
                        class="relative flex items-center w-2 h-2"
                    >
                        <button
                            type="button"
                            x-on:click="$wire.paymentReminderTextForm.mail_cc.splice($wire.paymentReminderTextForm.mail_cc.indexOf(to), 1)"
                        >
                            <x-icon
                                name="x-mark"
                                class="w-4 h-4"
                            />
                        </button>
                    </x-slot>
                </x-badge>
            </template>
        </div>
        <x-input
            :text="__('Payment Reminder Email CC')"
            :placeholder="__('Add a new bcc')"
            x-on:blur="addReceiver($event, 'mail_cc')"
            x-on:keyup="addReceiver($event, 'mail_cc')"
        />
        <x-input :label="__('Payment Reminder Email Subject')"
             wire:model="paymentReminderTextForm.mail_subject"
        />
        <x-flux::editor :label="__('Payment Reminder Email Text')"
              wire:model="paymentReminderTextForm.mail_body"
        />
        <x-slot:footer>
            <div class="flex justify-end gap-x-4">
                <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-payment-reminder-text-modal')"/>
                <x-button
                    color="indigo"
                    :text="__('Save')"
                    wire:click="save().then((success) => { if (success) $modalClose('edit-payment-reminder-text-modal'); })"
                />
            </div>
        </x-slot:footer>
    </div>
</x-modal>
