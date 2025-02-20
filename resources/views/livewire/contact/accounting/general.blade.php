<div class="flex flex-col gap-4" x-bind:class="! $wire.$parent.$parent.edit && 'pointer-events-none'">
    <x-card>
        <div class="flex flex-col gap-1.5">
            <x-select.styled
                x-bind:disabled="! $wire.$parent.$parent.edit"
                wire:model="contact.price_list_id"
                required
                :label="__('Price group')"
                :options="$priceLists"
                select="label:name|value:id"
            />
            <x-select.styled
                x-bind:disabled="! $wire.$parent.$parent.edit"
                wire:model="contact.payment_type_id"
                required
                :label="__('Payment type')"
                :options="$paymentTypes"
                select="label:name|value:id"
            />
            <x-select.styled
                x-bind:disabled="! $wire.$parent.$parent.edit"
                wire:model="contact.purchase_payment_type_id"
                required
                :label="__('Purchase Payment Type')"
                :options="$purchasePaymentTypes"
                select="label:name|value:id"
            />
            <x-select.styled
                x-bind:disabled="! $wire.$parent.$parent.edit"
                wire:model="contact.currency_id"
                required
                :label="__('Currency')"
                :options="$currencies"
                select="label:name|value:id"
            />
            <x-select.styled
                x-bind:disabled="! $wire.$parent.$parent.edit"
                wire:model="contact.vat_rate_id"
                required
                :label="__('Tax Exemption')"
                :options="$vatRates"
                select="label:name|value:id"
            />
            <x-select.styled
                x-bind:disabled="! $wire.$parent.$parent.edit"
                :label="__('Commission Agent')"
                wire:model="contact.agent_id"
                select="label:label|value:id"
                required
                :template="[
                    'name'   => 'user-option',
                ]"
                :request="[
                    'url' => route('search', \FluxErp\Models\User::class),
                    'method' => 'POST',
                    'params' => [
                        'with' => 'media',
                    ],
                ]"
            />
            <x-select.styled
                x-bind:disabled="! $wire.$parent.$parent.edit"
                :label="__('Approval User')"
                wire:model="contact.approval_user_id"
                select="label:label|value:id"
                required
                :template="[
                    'name'   => 'user-option',
                ]"
                :request="[
                    'url' => route('search', \FluxErp\Models\User::class),
                    'method' => 'POST',
                    'params' => [
                        'with' => 'media',
                    ],
                ]"
            />
        </div>
    </x-card>
    <x-card>
        <div class="flex flex-col gap-1.5">
            <x-toggle
                x-bind:disabled="! $wire.$parent.$parent.edit"
                wire:model="contact.has_delivery_lock"
                :label="__('Has Delivery Lock')"
            />
            <x-number
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
            <x-number
                x-bind:disabled="! $wire.$parent.$parent.edit"
                step="1"
                min="0"
                wire:model="contact.payment_target_days"
                :label="__('Payment Target Days')"
            />
            <x-number
                x-bind:disabled="! $wire.$parent.$parent.edit"
                step="1"
                min="1"
                wire:model="contact.payment_reminder_days_1"
                :label="__('Payment Reminder 1 Days')"
            />
            <x-number
                x-bind:disabled="! $wire.$parent.$parent.edit"
                step="1"
                min="1"
                wire:model="contact.payment_reminder_days_2"
                :label="__('Payment Reminder 2 Days')"
            />
            <x-number
                x-bind:disabled="! $wire.$parent.$parent.edit"
                step="1"
                min="1"
                wire:model="contact.payment_reminder_days_3"
                :label="__('Payment Reminder 3 Days')"
            />
            <x-number
                x-bind:disabled="! $wire.$parent.$parent.edit"
                step="1"
                min="1"
                wire:model="contact.payment_discount_days"
                :label="__('Payment Discount Days')"
            />
            <x-number
                x-bind:disabled="! $wire.$parent.$parent.edit"
                step="0.01"
                min="0"
                max="100"
                wire:model="contact.discount_percent"
                :label="__('Payment Discount Percentage')"
            />
        </div>
    </x-card>
    <x-card class="flex flex-col gap-4">
        <x-flux::editor
            wire:model="contact.header"
            :label="__('Header')"
        />
        <x-flux::editor
            wire:model="contact.footer"
            :label="__('Footer')"
        />
    </x-card>
</div>
