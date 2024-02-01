<x-modal name="execute-payment-run">
    <x-card>
        <div class="flex flex-col gap-4 text-sm">
            <div class="flex grid grid-cols-1">
                <div class="overflow-y-auto max-h-96">
                    <template x-for="order in $wire.paymentRunForm.orders">
                        <x-list-item class="flex justify-between" :item="[]">
                            <x-slot:value>
                                <div x-text="order.invoice_number"></div>
                            </x-slot:value>
                            <x-slot:sub-value>
                                <div x-html="window.formatters.coloredMoney(order.pivot.amount)"></div>
                                <div x-text="order.address_invoice.name"></div>
                                <div x-text="order.contact_bank_connection?.iban"></div>
                            </x-slot:sub-value>
                            <x-slot:actions>
                                <x-button
                                    negative
                                    :label="__('Delete')"
                                    wire:click="removeOrder(order.id).then((closeModal) => {if(closeModal) close();})"
                                    wire:confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Payment position')]) }}"
                                />
                            </x-slot:actions>
                        </x-list-item>
                    </template>
                </div>
                <div class="flex justify-end pt-4 gap-1.5">
                    <div>{{ __('Total') }}</div>
                    <div x-html="window.formatters.coloredMoney($wire.paymentRunForm.total_amount)"></div>
                </div>
                <hr class="py-4" />
                <div class="flex flex-col gap-4">
                    <x-select
                        wire:model="paymentRunForm.bank_connection_id"
                        :label="__('Account')"
                        :options="$accounts"
                        option-label="name"
                        option-value="id"
                        option-description="iban"
                    />
                    <x-datetime-picker
                        wire:model="paymentRunForm.instructed_execution_date"
                        :without-time="true"
                        :label="__('Execution Date')"
                        :min="now()->format('Y-m-d')"
                    />
                    <div x-cloak x-show="$wire.paymentRunForm.payment_run_type_enum === 'direct_debit'">
                        <x-toggle wire:model="paymentRunForm.is_single_booking" :label="__('Single Booking')" />
                    </div>
                    <div x-cloak x-show="$wire.paymentRunForm.payment_run_type_enum === 'money_transfer'">
                        <x-toggle wire:model="paymentRunForm.is_instant_payment" :label="__('Is Instant Payment')" />
                    </div>
                </div>
            </div>
        </div>
        <x-slot:footer>
            <div class="flex justify-between">
                <x-button
                    flat
                    negative
                    :label="__('Delete')"
                    wire:click="delete().then((success) => {if(success) close();})"
                    wire:confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Payment Run')]) }}"
                />
                <div class="flex justify-end gap-1.5">
                    <x-button :label="__('Cancel')" x-on:click="close()" />
                    <x-button
                        primary
                        :label="__('Execute Payment Run')"
                        wire:click="executePaymentRun().then((success) => {if(success) close();})"
                    />
                </div>
            </div>
        </x-slot:footer>
    </x-card>
</x-modal>
