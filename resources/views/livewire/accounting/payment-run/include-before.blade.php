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
                <template x-for="position in $wire.paymentRunForm.positions">
                    <div class="flex flex-col gap-2 pb-4">
                        <div class="flex justify-between font-medium">
                            <div>
                                <div x-text="position.account_holder"></div>
                                <div
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                    x-text="position.iban"
                                ></div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div
                                    x-html="
                                        $nuxbe.format.money(position.amount, {
                                            colored: true,
                                        })
                                    "
                                ></div>
                                <x-button
                                    icon="paper-airplane"
                                    color="secondary"
                                    size="sm"
                                    flat
                                    :title="__('Send Payment Advice')"
                                    x-on:click.stop="
                                        $wire.openPaymentAdviceModal(
                                            position.id,
                                        )
                                    "
                                />
                            </div>
                        </div>
                        <template x-for="order in position.orders">
                            <x-flux::list-item
                                class="flex justify-between"
                                :item="[]"
                            >
                                <x-slot:value>
                                    <div x-text="order.invoice_number"></div>
                                </x-slot:value>
                                <x-slot:sub-value>
                                    <div
                                        x-html="
                                            $nuxbe.format.money(
                                                order.pivot.amount,
                                                {
                                                    colored: true,
                                                },
                                            )
                                        "
                                    ></div>
                                    <div
                                        x-text="order.address_invoice.name"
                                    ></div>
                                    <div
                                        x-text="
                                            order.iban ||
                                            order.contact_bank_connection?.iban
                                        "
                                    ></div>
                                </x-slot:sub-value>
                                <x-slot:actions>
                                    <x-button
                                        x-cloak
                                        x-show="Number(position.amount) !== 0"
                                        color="red"
                                        :text="__('Delete')"
                                        x-on:click="
                                            $wire
                                                .removeOrder(order.id)
                                                .then((closeModal) => {
                                                    if (closeModal)
                                                        $tsui.close.modal(
                                                            'execute-payment-run',
                                                        );
                                                })
                                        "
                                        wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Payment position')]) }}"
                                    />
                                    <x-button
                                        color="indigo"
                                        :text="__('Show')"
                                        href="#"
                                        x-bind:href="getRoute(order)"
                                    />
                                </x-slot:actions>
                            </x-flux::list-item>
                        </template>
                    </div>
                </template>
            </div>
            <div class="flex justify-end gap-1.5 pt-4">
                <div>{{ __('Total') }}</div>
                <div
                    x-html="
                        $nuxbe.format.money($wire.paymentRunForm.total_amount, {
                            colored: true,
                        })
                    "
                ></div>
            </div>
            <hr class="py-4" />
            <div class="flex flex-col gap-6">
                @section('payment-properties')
                    <x-select.styled
                        wire:model="paymentRunForm.bank_connection_id"
                        searchable
                        :label="__('Account')"
                        select="label:name|value:id|description:iban"
                        :options="$accounts"
                    />
                    <x-date
                        wire:model="paymentRunForm.instructed_execution_date"
                        :without-time="true"
                        :label="__('Execution Date')"
                        :hint="__('Day the bank should carry the order out.')"
                        :min="now()->format('Y-m-d')"
                    />
                    <div
                        x-cloak
                        x-show="
                            $wire.paymentRunForm.payment_run_type_enum ===
                            'direct_debit'
                        "
                    >
                        <x-select.styled
                            wire:model="paymentRunForm.sepa_mandate_type_enum"
                            :label="__('Direct debit type')"
                            :hint="__('BASIC works with any debtor and stays reversible for eight weeks. B2B is for business debtors only, gives them no right of return, and needs their bank to know the mandate beforehand.')"
                            :options="\FluxErp\Enums\SepaMandateTypeEnum::valuesLocalized()"
                        />
                    </div>
                    <div class="flex flex-col gap-1">
                        <x-toggle
                            wire:model="paymentRunForm.is_collective"
                            :label="__('Collective')"
                        />
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Send all positions to the bank as one order instead of submitting each on its own.') }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <x-toggle
                            x-bind:disabled="
                                !$wire.paymentRunForm.is_collective
                            "
                            wire:model="paymentRunForm.is_single_booking"
                            :label="__('Single Booking')"
                        />
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Bank lists every position separately on the statement. Only possible together with a collective order.') }}
                        </span>
                    </div>
                    <div
                        x-cloak
                        x-show="
                            $wire.paymentRunForm.payment_run_type_enum ===
                            'money_transfer'
                        "
                    >
                        <div class="flex flex-col gap-1">
                            <x-toggle
                                wire:model="paymentRunForm.is_instant_payment"
                                :label="__('Is Instant Payment')"
                            />
                            <span
                                class="text-xs text-gray-500 dark:text-gray-400"
                            >
                                {{ __('Money reaches the recipient within seconds.') }}
                            </span>
                        </div>
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
                x-on:click="
                    $wire.delete().then((success) => {
                        if (success) $tsui.close.modal('execute-payment-run');
                    })
                "
                wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Payment Run')]) }}"
            />
            <div class="flex justify-end gap-x-2">
                <x-button
                    color="secondary"
                    light
                    :text="__('Cancel')"
                    x-on:click="$tsui.close.modal('execute-payment-run')"
                />
                @stack('payment-run-execute-actions')
                <x-button
                    color="indigo"
                    :text="__('Execute Payment Run')"
                    loading="executePaymentRun"
                    x-on:click="
                        $wire.executePaymentRun().then((success) => {
                            if (success)
                                $tsui.close.modal('execute-payment-run');
                        })
                    "
                />
            </div>
        </div>
    </x-slot:footer>
</x-modal>

{{ $this->renderCreateDocumentsModal() }}
