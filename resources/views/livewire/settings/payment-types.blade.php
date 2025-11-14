<x-modal id="edit-payment-type-modal" :title="__('Payment Type')">
    <div class="flex flex-col gap-1.5">
        <x-input wire:model="paymentType.name" :label="__('Name')" />
        <div class="mt-2">
            <x-toggle
                wire:model.boolean="paymentType.is_active"
                :label="__('Is Active')"
            />
        </div>
        <x-toggle
            wire:model.boolean="paymentType.is_default"
            :label="__('Is Default')"
        />
        <x-toggle
            wire:model.boolean="paymentType.is_purchase"
            :label="__('Is Purchase')"
        />
        <x-toggle
            wire:model.boolean="paymentType.is_sales"
            :label="__('Is Sales')"
        />
        <x-toggle
            wire:model.boolean="paymentType.is_direct_debit"
            :label="__('Is Direct Debit')"
        />
        <x-toggle
            wire:model.boolean="paymentType.requires_manual_transfer"
            :label="__('Requires Manual Transfer')"
        />
        <x-select.styled
            :label="__('Tenant')"
            multiple
            autocomplete="off"
            wire:model="paymentType.tenants"
            select="label:name|value:id"
            :options="$tenants"
        />
        <x-number
            wire:model="paymentType.payment_reminder_days_1"
            :label="__('Payment Reminder Days 1')"
        />
        <x-number
            wire:model="paymentType.payment_reminder_days_2"
            :label="__('Payment Reminder Days 2')"
        />
        <x-number
            wire:model="paymentType.payment_reminder_days_3"
            :label="__('Payment Reminder Days 3')"
        />
        <x-number
            wire:model="paymentType.payment_target"
            :label="__('Payment Target')"
        />
        <x-number
            wire:model="paymentType.payment_discount_target"
            :label="__('Payment Discount Target')"
        />
        <x-number
            wire:model="paymentType.payment_discount_percentage"
            :label="__('Payment Discount Percentage')"
        />
        <x-flux::editor
            wire:model="paymentType.description"
            :label="__('Description')"
        />
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('edit-payment-type-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            wire:click="save().then((success) => { if(success) $modalClose('edit-payment-type-modal')})"
        />
    </x-slot>
</x-modal>
