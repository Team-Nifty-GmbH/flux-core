<div
    class="flex flex-col gap-4"
    x-data="{
        baseDate(daysToAdd) {
            let result = $wire.$parent.order.invoice_date
                ? new Date($wire.$parent.order.invoice_date)
                : new Date()
            result.setDate(result.getDate() + daysToAdd)
            return '(' + window.formatters.date(result) + ')'
        },
    }"
>
    @include('flux::livewire.transactions.transactions')
    <x-card :header="__('Payment Conditions')">
        <div class="flex flex-col gap-4">
            <div>
                <div
                    class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                >
                    <span>{{ __('Payment target') }}</span>
                    <span
                        x-text="baseDate($wire.$parent.order.payment_target)"
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
                        x-text="baseDate($wire.$parent.order.payment_discount_target)"
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
                    <span>{{ __('Payment Discount Percentage') }}</span>
                </div>
                <x-number
                    step="0.01"
                    min="0"
                    x-model.number="$wire.$parent.order.payment_discount_percent"
                />
            </div>
            <div>
                <div
                    class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                >
                    <span>{{ __('Payment Reminder Days 1') }}</span>
                    <span
                        x-text="
                            baseDate(
                                $wire.$parent.order.payment_reminder_days_1 +
                                    $wire.$parent.order.payment_target,
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
                            baseDate(
                                $wire.$parent.order.payment_reminder_days_2 +
                                    $wire.$parent.order.payment_target +
                                    $wire.$parent.order.payment_reminder_days_1,
                            )
                        "
                    />
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
                            baseDate(
                                $wire.$parent.order.payment_reminder_days_2 +
                                    $wire.$parent.order.payment_target +
                                    $wire.$parent.order.payment_reminder_days_1 +
                                    $wire.$parent.order.payment_reminder_days_2,
                            )
                        "
                    />
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
            <x-input x-model="$wire.$parent.order.bic" :label="__('BIC')" />
            <x-input
                x-model="$wire.$parent.order.bank_name"
                :label="__('Bank Name')"
            />
        </div>
    </x-card>

    @teleport('body')
        <x-modal
            id="order-transaction-modal"
            x-on:open="$focusOn('order-transaction-amount')"
        >
            <x-number
                id="order-transaction-amount"
                :label="__('Amount')"
                wire:model="orderTransactionForm.amount"
                step="0.01"
                :corner-hint="__('Amount')"
                placeholder="0.00"
            />
            <x-slot:footer>
                <x-button
                    color="secondary"
                    :text="__('Cancel')"
                    x-on:click="$modalClose('order-transaction-modal')"
                />
                <x-button
                    :text="__('Save')"
                    x-on:click="$wire.save().then((success) => {
                        if (success) $modalClose('order-transaction-modal');
                    })"
                />
            </x-slot>
        </x-modal>
    @endteleport
</div>
