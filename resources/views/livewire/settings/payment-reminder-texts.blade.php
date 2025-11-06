<x-modal
    id="edit-payment-reminder-text-modal"
    :title="__('Payment Reminder Text')"
    persistent
>
    <div class="flex flex-col gap-1.5">
        <x-number
            :label="__('Minimum reminder level')"
            wire:model.number="paymentReminderTextForm.reminder_level"
        />
        <x-select.styled
            :label="__('Email Template')"
            wire:model="paymentReminderTextForm.email_template_id"
            select="label:label|value:id"
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\EmailTemplate::class),
                'method' => 'POST',
                'params' => [
                    'searchFields' => [
                        'name',
                    ],
                    'where' => [
                        [
                            'model_type',
                            '=',
                            morph_alias(\FluxErp\Models\PaymentReminder::class),
                        ],
                    ],
                    'whereNull' => [
                        'model_type',
                        'or',
                    ],
                ],
            ]"
        />
        <x-input
            :label="__('Payment Reminder Subject')"
            wire:model="paymentReminderTextForm.reminder_subject"
        />
        <x-flux::editor
            :label="__('Payment Reminder Text')"
            wire:model="paymentReminderTextForm.reminder_body"
            :blade-variables="\FluxErp\Facades\EditorVariable::getTranslated(\FluxErp\Models\PaymentReminder::class)"
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
