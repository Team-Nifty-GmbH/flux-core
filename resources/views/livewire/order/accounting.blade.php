<div
    class="flex flex-col gap-4"
    x-data="{
        calculateReminderDate(days) {
            if (!$wire.$parent.order.payment_target_date) {
                return '';
            }

            return (
                '(' +
                $nuxbe.format.date(
                    dayjs($wire.$parent.order.payment_target_date)
                        .add(days, 'day')
                        .toDate(),
                ) +
                ')'
            );
        },
    }"
>
    @include('flux::livewire.transactions.transactions')
    <x-card :header="__('Payment Reminder')">
        <div class="flex flex-col gap-4">
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <x-date
                        wire:model="order.payment_reminder_next_date"
                        :label="__('Payment Reminder Next Date')"
                    />
                </div>
                @canAction(\FluxErp\Actions\Order\ResetPaymentReminderLevel::class)
                    <div>
                        <x-button
                            :text="__('Set Level')"
                            x-on:click="
                                $tsui.open.modal(
                                    'reset-payment-reminder-level-modal',
                                )
                            "
                        />
                    </div>
                @endcanAction
            </div>
        </div>
    </x-card>
    <x-card :header="__('Payment Conditions')">
        <div class="flex flex-col gap-4">
            <div>
                <div
                    class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                >
                    <span>{{ __('Payment target') }}</span>
                    <span
                        x-show="$wire.$parent.order.payment_target_date"
                        x-text="
                            '(' +
                            $nuxbe.format.date(
                                $wire.$parent.order.payment_target_date,
                            ) +
                            ')'
                        "
                        class="text-xs"
                    ></span>
                </div>
                <x-number
                    step="1"
                    min="0"
                    x-model.number="$wire.$parent.order.payment_target"
                />
            </div>
            <div>
                <div
                    class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                >
                    <span>{{ __('Payment Discount Target') }}</span>
                    <span
                        x-show="
                            $wire.$parent.order.payment_discount_target_date
                        "
                        x-text="
                            '(' +
                            $nuxbe.format.date(
                                $wire.$parent.order
                                    .payment_discount_target_date,
                            ) +
                            ')'
                        "
                        class="text-xs"
                    ></span>
                </div>
                <x-number
                    step="1"
                    min="0"
                    x-model.number="$wire.$parent.order.payment_discount_target"
                />
            </div>
            <div>
                <div
                    class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                >
                    <span>{{ __('Payment Discount Percent') }}</span>
                </div>
                <x-number
                    step="0.01"
                    min="0"
                    x-model.number="
                        $wire.$parent.order.payment_discount_percent
                    "
                />
            </div>
            <div>
                <div
                    class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                >
                    <span>{{ __('Payment Reminder Days 1') }}</span>
                    <span
                        x-text="
                            calculateReminderDate(
                                $wire.$parent.order.payment_reminder_days_1,
                            )
                        "
                    ></span>
                </div>
                <x-number
                    step="1"
                    min="1"
                    x-model.number="$wire.$parent.order.payment_reminder_days_1"
                />
            </div>
            <div>
                <div
                    class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                >
                    <span>{{ __('Payment Reminder Days 2') }}</span>
                    <span
                        x-text="
                            calculateReminderDate(
                                $wire.$parent.order.payment_reminder_days_1 +
                                    $wire.$parent.order.payment_reminder_days_2,
                            )
                        "
                    ></span>
                </div>
                <x-number
                    step="1"
                    min="1"
                    x-model.number="$wire.$parent.order.payment_reminder_days_2"
                />
            </div>
            <div>
                <div
                    class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                >
                    <span>{{ __('Payment Reminder Days 3') }}</span>
                    <span
                        x-text="
                            calculateReminderDate(
                                $wire.$parent.order.payment_reminder_days_1 +
                                    $wire.$parent.order
                                        .payment_reminder_days_2 +
                                    $wire.$parent.order.payment_reminder_days_3,
                            )
                        "
                    ></span>
                </div>
                <x-number
                    step="1"
                    min="1"
                    x-model.number="$wire.$parent.order.payment_reminder_days_3"
                />
            </div>
        </div>
    </x-card>
    <x-card :header="__('Bank Connection')">
        <div class="flex flex-col gap-4">
            <x-input
                x-model="$wire.$parent.order.account_holder"
                :label="__('Account Holder')"
            />
            <x-input x-model="$wire.$parent.order.iban" :label="__('IBAN')" />
            <div x-data="{ patternMatches: [] }" class="flex flex-col gap-1.5">
                <x-input
                    x-model="$wire.$parent.order.payment_purpose_pattern"
                    :label="__('Payment purpose contains')"
                    :placeholder="__('e.g. Loan 0047123456')"
                    :hint="__('Debits whose payment purpose contains this text are assigned to this contract. Case does not matter.')"
                    x-on:input.debounce.500ms="
                        const value = $event.target.value;
                        const matches =
                            await $wire.previewPaymentPurposePattern(value);

                        // Only apply the response if the input has not changed since,
                        // a slower earlier request must not override a newer one.
                        if ($event.target.value === value) {
                            patternMatches = matches;
                        }
                    "
                />
                <div x-cloak x-show="patternMatches.length > 0">
                    <x-label :label="__('Matching recent debits')" />
                    <div
                        class="divide-y divide-gray-200 rounded-lg border border-gray-200 text-xs dark:divide-gray-700 dark:border-gray-700"
                    >
                        <template x-for="match in patternMatches">
                            <div class="flex flex-col gap-0.5 p-2">
                                <div class="flex justify-between font-medium">
                                    <span
                                        x-text="match.counterpart_name"
                                    ></span>
                                    <span x-text="match.amount"></span>
                                </div>
                                <div
                                    class="flex justify-between text-gray-500 dark:text-gray-400"
                                >
                                    <span x-text="match.purpose"></span>
                                    <span x-text="match.booking_date"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <x-input x-model="$wire.$parent.order.bic" :label="__('BIC')" />
            <x-input
                x-model="$wire.$parent.order.bank_name"
                :label="__('Bank Name')"
            />
        </div>
    </x-card>

    <x-modal
        id="order-transaction-modal"
        x-on:open="$tsui.focus('order-transaction-amount')"
    >
        <div class="flex flex-col gap-4">
            <x-number
                id="order-transaction-amount"
                :label="__('Amount')"
                wire:model="orderTransactionForm.amount"
                step="0.01"
                :corner-hint="__('Amount')"
                placeholder="0.00"
            />
            <div
                x-cloak
                x-show="$wire.orderTransactionForm.orderCurrencyIso"
                class="flex flex-col gap-4"
            >
                <x-number
                    :label="__('Exchange Rate')"
                    wire:model="orderTransactionForm.exchange_rate"
                    step="0.0001"
                    placeholder="0.0000"
                    x-on:change="$wire.calcOrderCurrencyAmount()"
                />
                <x-number
                    wire:model="orderTransactionForm.order_currency_amount"
                    step="0.01"
                    placeholder="0.00"
                    x-on:change="$wire.calcExchangeRate()"
                >
                    <x-slot:label>
                        {{ __('Order Currency Amount') }} (
                        <span
                            x-text="$wire.orderTransactionForm.orderCurrencyIso"
                        ></span>
                        )
                    </x-slot:label>
                </x-number>
            </div>
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                :text="__('Cancel')"
                x-on:click="$tsui.close.modal('order-transaction-modal')"
            />
            <x-button
                :text="__('Save')"
                x-on:click="
                    $wire.save().then((success) => {
                        if (success)
                            $tsui.close.modal('order-transaction-modal');
                    })
                "
            />
        </x-slot:footer>
    </x-modal>

    <x-modal
        id="reset-payment-reminder-level-modal"
        x-on:open="$tsui.focus('new-payment-reminder-level')"
    >
        <x-slot:title>
            {{ __('Set Payment Reminder Level') }}
        </x-slot:title>
        <x-number
            id="new-payment-reminder-level"
            wire:model="newPaymentReminderLevel"
            :label="__('New Reminder Level')"
            step="1"
            min="0"
        />
        <x-slot:footer>
            <x-button
                color="secondary"
                :text="__('Cancel')"
                x-on:click="
                    $tsui.close.modal('reset-payment-reminder-level-modal')
                "
            />
            <x-button
                :text="__('Save')"
                x-on:click="
                    $wire.resetPaymentReminderLevel().then((success) => {
                        if (success)
                            $tsui.close.modal(
                                'reset-payment-reminder-level-modal',
                            );
                    })
                "
            />
        </x-slot:footer>
    </x-modal>
</div>
