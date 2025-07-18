<x-modal id="execute-payment-run">
    <div
        class="flex flex-col gap-4 text-sm"
        x-data="{
            getRoute(order) {
                return '{{ route('orders.id', ['id' => ':id']) }}'.replace(
                    ':id',
                    order.id,
                )
            },
        }"
    >
        <div class="grid grid-cols-1">
            <div class="max-h-96 overflow-y-auto">
                <template x-for="order in $wire.paymentRunForm.orders">
                    <x-flux::list-item class="flex justify-between" :item="[]">
                        <x-slot:value>
                            <div x-text="order.invoice_number"></div>
                        </x-slot>
                        <x-slot:sub-value>
                            <div
                                x-html="window.formatters.coloredMoney(order.pivot.amount)"
                            ></div>
                            <div x-text="order.address_invoice.name"></div>
                            <div
                                x-text="order.iban || order.contact_bank_connection?.iban"
                            ></div>
                        </x-slot>
                        <x-slot:actions>
                            <x-button
                                color="red"
                                :text="__('Delete')"
                                wire:click="removeOrder(order.id).then((closeModal) => {if(closeModal) close();})"
                                wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Payment position')]) }}"
                            />
                            <x-button
                                color="indigo"
                                :text="__('Show')"
                                href="#"
                                x-bind:href="getRoute(order)"
                            />
                        </x-slot>
                    </x-flux::list-item>
                </template>
            </div>
            <div class="flex justify-end gap-1.5 pt-4">
                <div>{{ __('Total') }}</div>
                <div
                    x-html="window.formatters.coloredMoney($wire.paymentRunForm.total_amount)"
                ></div>
            </div>
            <hr class="py-4" />
            <div class="flex flex-col gap-4">
                @section('payment-properties')
                <x-select.styled
                    wire:model="paymentRunForm.bank_connection_id"
                    :label="__('Account')"
                    select="label:name|value:id|description:iban"
                    :options="$accounts"
                />
                <x-date
                    wire:model="paymentRunForm.instructed_execution_date"
                    :without-time="true"
                    :label="__('Execution Date')"
                    :min="now()->format('Y-m-d')"
                />
                <div
                    x-cloak
                    x-show="$wire.paymentRunForm.payment_run_type_enum === 'direct_debit'"
                >
                    <x-select.styled
                        wire:model="paymentRunForm.sepa_mandate_type_enum"
                        :label="__('Direct debit type')"
                        :options="\FluxErp\Enums\SepaMandateTypeEnum::valuesLocalized()"
                    />
                </div>
                <x-toggle
                    wire:model="paymentRunForm.is_collective"
                    :label="__('Collective')"
                />
                <x-toggle
                    x-bind:disabled="! $wire.paymentRunForm.is_collective"
                    wire:model="paymentRunForm.is_single_booking"
                    :label="__('Single Booking')"
                />
                <div
                    x-cloak
                    x-show="$wire.paymentRunForm.payment_run_type_enum === 'money_transfer'"
                >
                    <x-toggle
                        wire:model="paymentRunForm.is_instant_payment"
                        :label="__('Is Instant Payment')"
                    />
                </div>
                @show
            </div>
        </div>
    </div>
    <x-slot:footer>
        <div class="flex justify-between">
            <x-button
                flat
                color="red"
                :text="__('Delete')"
                wire:click="delete().then((success) => {if(success) $modalClose('execute-payment-run');})"
                wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Payment Run')]) }}"
            />
            <div class="flex justify-end gap-x-2">
                <x-button
                    color="secondary"
                    light
                    :text="__('Cancel')"
                    x-on:click="$modalClose('execute-payment-run')"
                />
                <x-button
                    color="indigo"
                    :text="__('Execute Payment Run')"
                    loading="executePaymentRun"
                    wire:click="executePaymentRun().then((success) => {if(success) $modalClose('execute-payment-run');})"
                />
            </div>
        </div>
    </x-slot>
</x-modal>
