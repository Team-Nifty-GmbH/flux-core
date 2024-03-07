<div class="flex flex-col gap-4" x-bind:class="! $wire.$parent.$parent.edit && 'pointer-events-none'">
    <x-card>
        <div class="flex flex-col gap-1.5">
            <x-select
                x-bind:disabled="! $wire.$parent.$parent.edit"
                wire:model="contact.price_list_id"
                :clearable="false"
                :label="__('Price group')"
                :options="$priceLists"
                option-label="name"
                option-value="id"
            />
            <x-select
                x-bind:disabled="! $wire.$parent.$parent.edit"
                wire:model="contact.payment_type_id"
                :clearable="false"
                :label="__('Payment type')"
                :options="$paymentTypes"
                option-label="name"
                option-value="id"
            />
            <x-select
                x-bind:disabled="! $wire.$parent.$parent.edit"
                wire:model="contact.purchase_payment_type_id"
                :clearable="false"
                :label="__('Purchase Payment Type')"
                :options="$purchasePaymentTypes"
                option-label="name"
                option-value="id"
            />
            <x-select
                x-bind:disabled="! $wire.$parent.$parent.edit"
                :label="__('Commission Agent')"
                wire:model="contact.agent_id"
                option-value="id"
                option-label="label"
                :clearable="false"
                :template="[
                    'name'   => 'user-option',
                ]"
                :async-data="[
                    'api' => route('search', \FluxErp\Models\User::class),
                    'method' => 'POST',
                    'params' => [
                        'with' => 'media',
                    ]
                ]"
            />
        </div>
    </x-card>
    <x-card>
        <div class="flex flex-col gap-1.5">
            <x-inputs.number
                x-bind:disabled="! $wire.$parent.$parent.edit"
                wire:model="contact.credit_line"
                :label="__('Credit line')"
            />
            <x-input
                x-bind:disabled="! $wire.$parent.$parent.edit"
                wire:model="contact.creditor_number"
                :label="__('Creditor number')"
            />
            <x-input
                x-bind:disabled="! $wire.$parent.$parent.edit"
                wire:model="contact.debtor_number"
                :label="__('Debtor number')"
            />
            <x-input
                x-bind:disabled="! $wire.$parent.$parent.edit"
                wire:model="contact.vat_id"
                :label="__('VAT number')"
            />
        </div>
    </x-card>
    <x-card>
        <div class="flex flex-col gap-1.5">
            <x-inputs.number
                x-bind:disabled="! $wire.$parent.$parent.edit"
                step="1"
                min="0"
                wire:model="contact.payment_target_days"
                :label="__('Payment Target Days')"
            />
            <x-inputs.number
                x-bind:disabled="! $wire.$parent.$parent.edit"
                step="1"
                min="1"
                wire:model="contact.payment_reminder_days_1"
                :label="__('Payment Reminder 1 Days')"
            />
            <x-inputs.number
                x-bind:disabled="! $wire.$parent.$parent.edit"
                step="1"
                min="1"
                wire:model="contact.payment_reminder_days_2"
                :label="__('Payment Reminder 2 Days')"
            />
            <x-inputs.number
                x-bind:disabled="! $wire.$parent.$parent.edit"
                step="1"
                min="1"
                wire:model="contact.payment_reminder_days_3"
                :label="__('Payment Reminder 3 Days')"
            />
            <x-inputs.number
                x-bind:disabled="! $wire.$parent.$parent.edit"
                step="1"
                min="1"
                wire:model="contact.payment_discount_days"
                :label="__('Payment Discount Days')"
            />
            <x-inputs.number
                x-bind:disabled="! $wire.$parent.$parent.edit"
                step="0.01"
                min="0"
                max="100"
                wire:model="contact.payment_discount_percent"
                :label="__('Payment Discount Percentage')"
            />
        </div>
    </x-card>
</div>
