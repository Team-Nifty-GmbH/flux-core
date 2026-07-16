<div
    x-on:refresh-transactions.window="init()"
    x-data="{
        data: [],
        async init() {
            this.data = await $wire.loadTransactions();
        },
    }"
>
    <x-modal id="transaction-assign-orders-modal" size="7xl">
        <x-slot:title>
            <div class="flex w-full flex-col">
                <div class="flex flex-row gap-2">
                    <span
                        x-text="$wire.transactionForm.counterpart_name"
                    ></span>
                    <span
                        class="text-red-600"
                        x-html="
                            $nuxbe.format.money($wire.transactionForm.amount, {
                                colored: true,
                            })
                        "
                    ></span>
                </div>
                <div
                    class="text-xs"
                    x-text="
                        $nuxbe.format.date($wire.transactionForm.booking_date)
                    "
                ></div>
                <div
                    class="mt-2 flex w-full flex-row justify-between border-t border-slate-200 pt-2"
                >
                    <div x-text="$wire.transactionForm.purpose"></div>
                </div>
            </div>
        </x-slot:title>
        <livewire:accounting.order-list wire:model="selectedOrders" lazy />
        <x-slot:footer>
            <x-button
                color="secondary"
                :text="__('Cancel')"
                x-on:click="
                    $tsui.close.modal('transaction-assign-orders-modal')
                "
            />
            @stack('transaction-assign-orders-modal-footer')
            <x-button
                :text="__('Assign')"
                wire:click="assignOrders()"
                loading="assignOrders()"
            />
        </x-slot:footer>
    </x-modal>

    <x-modal id="transaction-comments-modal">
        <x-slot:title>
            <div class="flex w-full flex-col">
                <div class="flex flex-row gap-2">
                    <span
                        x-text="$wire.transactionForm.counterpart_name"
                    ></span>
                    <span
                        class="text-red-600"
                        x-html="
                            $nuxbe.format.money($wire.transactionForm.amount, {
                                colored: true,
                            })
                        "
                    ></span>
                </div>
                <div
                    class="text-xs"
                    x-text="
                        $nuxbe.format.date($wire.transactionForm.booking_date)
                    "
                ></div>
                <div
                    class="mt-2 flex w-full flex-row justify-between border-t border-slate-200 pt-2"
                >
                    <div x-text="$wire.transactionForm.purpose"></div>
                </div>
            </div>
        </x-slot:title>
        <livewire:accounting.transactions.comments
            :model-type="\FluxErp\Models\Transaction::class"
            wire:model="transactionForm.id"
            lazy
            :is-public="false"
        />
        <x-slot:footer>
            <x-button
                color="secondary"
                :text="__('Cancel')"
                x-on:click="$tsui.close.modal('transaction-comments-modal')"
            />
            @stack('transaction-comments-modal-footer')
        </x-slot:footer>
    </x-modal>

    <x-modal id="transaction-attachment-modal" :title="__('Attachment')">
        <x-flux::features.media.upload-form-object
            wire:model="attachment"
            :multiple="false"
            accept="application/pdf, image/jpeg, image/png, image/svg+xml"
        />
        <x-slot:footer>
            <x-button
                color="secondary"
                :text="__('Cancel')"
                x-on:click="$tsui.close.modal('transaction-attachment-modal')"
            />
            @stack('transaction-attachment-modal-footer')
            <x-button
                :text="__('Save')"
                x-on:click="
                    $wire.saveAttachment().then((success) => {
                        if (success)
                            $tsui.close.modal('transaction-attachment-modal');
                    })
                "
                loading="saveAttachment()"
            />
        </x-slot:footer>
    </x-modal>

    <x-modal
        id="order-transaction-modal"
        x-on:open="$tsui.focus('order-transaction-amount')"
    >
        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-2">
                <x-number
                    id="order-transaction-amount"
                    :label="__('Amount')"
                    wire:model="orderTransactionForm.amount"
                    step="0.01"
                    :corner-hint="__('Amount')"
                    placeholder="0.00"
                />
                <div class="flex flex-wrap gap-2">
                    <x-button
                        sm
                        color="secondary"
                        x-cloak
                        x-show="
                            $wire.orderTransactionForm.orderGrossTotal !== null
                        "
                        x-on:click="
                            $wire.orderTransactionForm.amount =
                                $wire.orderTransactionForm.orderGrossTotal
                        "
                    >
                        <x-slot:text>
                            {{ __('Apply Gross Total') }} (<span
                                x-html="
                                    $nuxbe.format.money(
                                        $wire.orderTransactionForm
                                            .orderGrossTotal,
                                    )
                                "
                            ></span
                            >)
                        </x-slot:text>
                    </x-button>
                    <x-button
                        sm
                        color="secondary"
                        x-cloak
                        x-show="
                            $wire.orderTransactionForm.orderBalance !== null &&
                            $wire.orderTransactionForm.orderBalance !==
                                $wire.orderTransactionForm.orderGrossTotal
                        "
                        x-on:click="
                            $wire.orderTransactionForm.amount =
                                $wire.orderTransactionForm.orderBalance
                        "
                    >
                        <x-slot:text>
                            {{ __('Apply Balance Amount') }} (<span
                                x-html="
                                    $nuxbe.format.money(
                                        $wire.orderTransactionForm.orderBalance,
                                    )
                                "
                            ></span
                            >)
                        </x-slot:text>
                    </x-button>
                    <x-button
                        sm
                        color="secondary"
                        x-cloak
                        x-show="
                            $wire.orderTransactionForm.transactionAmount !==
                                null &&
                            $wire.orderTransactionForm.transactionAmount !==
                                $wire.orderTransactionForm.orderGrossTotal &&
                            $wire.orderTransactionForm.transactionAmount !==
                                $wire.orderTransactionForm.orderBalance
                        "
                        x-on:click="
                            $wire.orderTransactionForm.amount =
                                $wire.orderTransactionForm.transactionAmount
                        "
                    >
                        <x-slot:text>
                            {{ __('Apply Payment Amount') }} (<span
                                x-html="
                                    $nuxbe.format.money(
                                        $wire.orderTransactionForm
                                            .transactionAmount,
                                    )
                                "
                            ></span
                            >)
                        </x-slot:text>
                    </x-button>
                </div>
            </div>
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
            @stack('order-transaction-modal-footer')
            <x-button
                :text="__('Save')"
                x-on:click="$wire.saveOrderTransaction()"
                loading="saveOrderTransaction()"
            />
        </x-slot:footer>
    </x-modal>

    <x-modal
        id="ledger-account-transaction-modal"
        :title="__('Assign ledger account')"
    >
        <div class="flex flex-col gap-4">
            <x-select.styled
                :label="__('Ledger Account')"
                wire:model.number="ledgerAccountTransactionForm.ledger_account_id"
                select="label:name|value:id|description:number"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\LedgerAccount::class),
                    'method' => 'POST',
                ]"
            />
            <div class="flex flex-col gap-2">
                <x-number
                    :label="__('Amount')"
                    wire:model="ledgerAccountTransactionForm.amount"
                    step="0.01"
                    placeholder="0.00"
                />
                <div>
                    <x-button
                        sm
                        color="secondary"
                        x-cloak
                        x-show="
                            $wire.ledgerAccountTransactionForm
                                .transactionBalance !== null &&
                            $wire.ledgerAccountTransactionForm.amount !==
                                $wire.ledgerAccountTransactionForm
                                    .transactionBalance
                        "
                        x-on:click="
                            $wire.ledgerAccountTransactionForm.amount =
                                $wire.ledgerAccountTransactionForm.transactionBalance
                        "
                    >
                        <x-slot:text>
                            {{ __('Apply transaction amount') }} (<span
                                x-html="
                                    $nuxbe.format.money(
                                        $wire.ledgerAccountTransactionForm
                                            .transactionBalance,
                                    )
                                "
                            ></span
                            >)
                        </x-slot:text>
                    </x-button>
                </div>
            </div>
            <x-input
                :label="__('Note')"
                wire:model="ledgerAccountTransactionForm.note"
            />
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                :text="__('Cancel')"
                x-on:click="
                    $tsui.close.modal('ledger-account-transaction-modal')
                "
            />
            @stack('ledger-account-transaction-modal-footer')
            <x-button
                :text="__('Save')"
                x-on:click="$wire.saveLedgerAccountTransaction()"
                loading="saveLedgerAccountTransaction()"
            />
        </x-slot:footer>
    </x-modal>

    <div class="flex flex-col gap-4 text-sm">
        <div class="flex flex-col">
            <x-tab wire:model.live="tab">
                <x-tab.items :tab="__('All')" />
                <x-tab.items :tab="__('Assignment suggestions')">
                    <x-slot:right>
                        <x-badge round>
                            <x-slot:text>
                                <span
                                    x-text="$wire.suggestionCount ?? 0"
                                ></span>
                            </x-slot:text>
                        </x-badge>
                    </x-slot:right>
                </x-tab.items>
                <x-tab.items :tab="__('Open transactions')">
                    <x-slot:right>
                        <x-badge round>
                            <x-slot:text>
                                <span
                                    x-text="$wire.unassignedCount ?? 0"
                                ></span>
                            </x-slot:text>
                        </x-badge>
                    </x-slot:right>
                </x-tab.items>
                <div class="flex flex-row items-center gap-2">
                    <div class="w-full lg:w-1/2">
                        <x-input
                            icon="magnifying-glass"
                            wire:model.live.debounce="search"
                            :placeholder="__('Search in :model…', ['model' => __('Transactions')])"
                            type="search"
                        />
                    </div>
                    <div class="hidden w-1/2 flex-row gap-2 lg:flex">
                        <div class="w-1/2">
                            <x-date
                                wire:model.live="range"
                                range
                                :placeholder="__('Choose range')"
                            />
                        </div>
                        <div class="w-1/2">
                            <x-select.styled
                                wire:model.live="bankAccounts"
                                multiple
                                :placeholder="__('Choose bank account')"
                                select="label:name|value:id|description:iban"
                                :options="$bankConnections"
                            />
                        </div>
                    </div>
                    <div class="flex w-fit lg:hidden">
                        <x-dropdown>
                            <x-slot:action>
                                <x-button.circle
                                    x-on:click="show = !show"
                                    icon="funnel"
                                />
                            </x-slot:action>
                            <x-dropdown.items>
                                <div class="w-full">
                                    <x-date
                                        wire:model.live="range"
                                        range
                                        :placeholder="__('Choose range')"
                                    />
                                </div>
                            </x-dropdown.items>
                            <x-dropdown.items>
                                <div class="w-full">
                                    <x-select.styled
                                        wire:model.live="bankAccounts"
                                        multiple
                                        :placeholder="__('Choose bank account')"
                                        select="label:name|value:id|description:iban"
                                        :options="$bankConnections"
                                    />
                                </div>
                            </x-dropdown.items>
                        </x-dropdown>
                    </div>
                </div>
                <div class="mt-2 flex flex-col gap-2">
                    <template x-for="transaction in data.data">
                        <div
                            class="flex flex-col rounded border-2 border-slate-200 lg:flex-row"
                        >
                            <div
                                class="flex w-full flex-col p-4 lg:w-1/2 lg:border-r-2 lg:border-b-0"
                            >
                                <div class="flex justify-between gap-4">
                                    <div class="flex gap-4">
                                        <x-avatar
                                            borderless
                                            :image="route('icons', ['name' => 'user'])"
                                            class="ring-4 ring-offset-2"
                                            x-bind:class="
                                                parseFloat(
                                                    transaction.balance,
                                                ) === 0
                                                    ? 'ring-emerald-500'
                                                    : transaction.order_transactions_count >
                                                        0
                                                      ? 'ring-amber-500'
                                                      : 'ring-red-500'
                                            "
                                            x-bind:src="transaction?.avatar_url ?? '{{ route('icons', ['name' => 'user']) }}'"
                                        />
                                        <div
                                            class="flex flex-col justify-between"
                                        >
                                            <div
                                                class="w-full text-lg font-semibold"
                                                x-text="transaction.counterpart_name ?? '{{ __('Unknown') }}'"
                                            ></div>
                                            <div
                                                class="w-full font-semibold text-slate-400"
                                                x-text="
                                                    transaction.counterpart_iban
                                                "
                                            ></div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <div
                                            class="flex w-full justify-end text-lg font-semibold"
                                            x-html="
                                                $nuxbe.format.money(
                                                    transaction.amount,
                                                    { colored: true },
                                                )
                                            "
                                        ></div>
                                        <div
                                            class="flex w-full flex-row items-center justify-end gap-2 font-semibold"
                                        >
                                            <span
                                                x-text="
                                                    $nuxbe.format.date(
                                                        transaction.booking_date,
                                                    )
                                                "
                                            ></span>
                                            <x-dropdown icon="banknotes">
                                                <div class="p-2">
                                                    <b
                                                        x-text="
                                                            transaction
                                                                .bank_connection
                                                                .bank_name
                                                        "
                                                    ></b>
                                                    <br />
                                                    <span
                                                        x-text="
                                                            transaction
                                                                .bank_connection
                                                                .iban
                                                        "
                                                    ></span>
                                                </div>
                                            </x-dropdown>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="mt-2 flex flex-row justify-between border-t border-slate-200 pt-2"
                                >
                                    <div x-text="transaction.purpose"></div>
                                </div>
                            </div>
                            <div
                                class="flex w-full flex-col gap-2 py-2 lg:w-1/2"
                            >
                                <template x-for="order in transaction.orders">
                                    <div
                                        class="group flex flex-row items-center gap-2 px-2"
                                    >
                                        <div
                                            class="group/button relative w-fit rounded-full"
                                        >
                                            <x-button.circle
                                                x-cloak
                                                x-show="order.pivot.is_accepted"
                                                color="emerald"
                                                rounded
                                                icon="link"
                                                class="block transition-opacity duration-200 group-hover/button:opacity-0"
                                            />
                                            <x-button.circle
                                                x-cloak
                                                x-show="
                                                    !order.pivot.is_accepted
                                                "
                                                color="amber"
                                                rounded
                                                icon="link"
                                                class="block transition-opacity duration-200 group-hover/button:opacity-0"
                                            />
                                            <x-button.circle
                                                color="red"
                                                rounded
                                                icon="link-slash"
                                                wire:click="deleteOrderTransaction(order.pivot.pivot_id)"
                                                wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Assignment')]) }}"
                                                class="absolute top-0 left-0 opacity-0 transition-opacity duration-200 group-hover/button:opacity-100"
                                            />
                                        </div>
                                        <div
                                            class="flex w-full justify-between rounded bg-slate-100 p-2 font-semibold"
                                        >
                                            <div class="flex gap-2">
                                                <div class="block">
                                                    <x-button
                                                        class="h-full"
                                                        color="secondary"
                                                        sm
                                                        icon="eye"
                                                        x-on:click="$nuxbe.openDetailModal('{{ route('orders.id', ['id' => '__key__']) }}'.replace('__key__', order.id))"
                                                    />
                                                </div>
                                                <div>
                                                    <div
                                                        x-text="
                                                            order
                                                                .address_invoice
                                                                ?.name
                                                        "
                                                    ></div>
                                                    <div
                                                        x-text="
                                                            order.invoice_number
                                                        "
                                                    ></div>
                                                </div>
                                            </div>
                                            <div class="flex gap-2">
                                                <div>
                                                    <div
                                                        class="flex w-full justify-end font-semibold"
                                                        x-html="
                                                            $nuxbe.format.money(
                                                                order.pivot
                                                                    .amount,
                                                                {
                                                                    colored: true,
                                                                },
                                                            )
                                                        "
                                                    ></div>
                                                    <div
                                                        x-cloak
                                                        x-show="
                                                            order.pivot
                                                                .order_currency_amount
                                                        "
                                                        class="flex w-full justify-end text-xs text-slate-500"
                                                    >
                                                        <span
                                                            x-text="
                                                                (order.currency
                                                                    ?.iso ??
                                                                    '') +
                                                                ' ' +
                                                                parseFloat(
                                                                    order.pivot
                                                                        .order_currency_amount ||
                                                                        0,
                                                                ).toFixed(2)
                                                            "
                                                        ></span>
                                                    </div>
                                                    <div
                                                        class="flex w-full flex-row items-center justify-end gap-2 font-semibold"
                                                    >
                                                        <span
                                                            x-html="
                                                                $nuxbe.format.date(
                                                                    order.invoice_date,
                                                                )
                                                            "
                                                        ></span>
                                                    </div>
                                                </div>
                                                <div class="block">
                                                    <x-button
                                                        class="h-full"
                                                        color="secondary"
                                                        sm
                                                        icon="pencil"
                                                        wire:click="editOrderTransaction(order.pivot.pivot_id)"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template
                                    x-for="
                                        ledgerAccountTransaction in
                                        transaction.ledger_account_transactions
                                    "
                                >
                                    <div
                                        class="group flex flex-row items-center gap-2 px-2"
                                    >
                                        <div
                                            class="group/button relative w-fit rounded-full"
                                        >
                                            <x-button.circle
                                                color="emerald"
                                                rounded
                                                icon="book-open"
                                                class="block transition-opacity duration-200 group-hover/button:opacity-0"
                                            />
                                            <x-button.circle
                                                color="red"
                                                rounded
                                                icon="link-slash"
                                                wire:click="deleteLedgerAccountTransaction(ledgerAccountTransaction.pivot_id)"
                                                wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Assignment')]) }}"
                                                class="absolute top-0 left-0 opacity-0 transition-opacity duration-200 group-hover/button:opacity-100"
                                            />
                                        </div>
                                        <div
                                            class="flex w-full justify-between rounded bg-slate-100 p-2 font-semibold"
                                        >
                                            <div>
                                                <div
                                                    x-text="
                                                        ledgerAccountTransaction
                                                            .ledger_account
                                                            ?.number +
                                                        ' ' +
                                                        ledgerAccountTransaction
                                                            .ledger_account
                                                            ?.name
                                                    "
                                                ></div>
                                                <div
                                                    class="text-xs font-normal text-slate-500"
                                                    x-text="
                                                        ledgerAccountTransaction.note
                                                    "
                                                ></div>
                                            </div>
                                            <div class="flex gap-2">
                                                <div
                                                    class="flex items-center justify-end font-semibold"
                                                    x-html="
                                                        $nuxbe.format.money(
                                                            ledgerAccountTransaction.amount,
                                                            {
                                                                colored: true,
                                                            },
                                                        )
                                                    "
                                                ></div>
                                                <div class="block">
                                                    <x-button
                                                        class="h-full"
                                                        color="secondary"
                                                        sm
                                                        icon="pencil"
                                                        wire:click="editLedgerAccountTransaction(ledgerAccountTransaction.pivot_id)"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <div class="px-2 pt-2">
                                    <div class="flex flex-col">
                                        <div
                                            class="flex flex-col-reverse gap-2 border-t border-slate-200 pt-2 lg:flex-row lg:items-center lg:justify-between"
                                        >
                                            <div
                                                class="flex flex-row flex-wrap gap-2"
                                            >
                                                <x-button
                                                    sm
                                                    x-cloak
                                                    x-show="
                                                        transaction.suggestions >
                                                        0
                                                    "
                                                    color="emerald"
                                                    wire:click="acceptAll(transaction.id)"
                                                    :text="__('Accept')"
                                                />
                                                <x-button
                                                    sm
                                                    light
                                                    color="gray"
                                                    wire:click="showComments(transaction.id)"
                                                    :text="__('Add comment')"
                                                >
                                                    <x-slot:text>
                                                        <div>
                                                            <span>
                                                                {{ __('Add comment') }}
                                                            </span>
                                                            <span
                                                                x-cloak
                                                                x-show="
                                                                    transaction.comments_count
                                                                "
                                                                x-text="
                                                                    '(' +
                                                                    transaction.comments_count +
                                                                    ')'
                                                                "
                                                            ></span>
                                                        </div>
                                                    </x-slot:text>
                                                </x-button>
                                                <x-button
                                                    sm
                                                    light
                                                    color="gray"
                                                    icon="paper-clip"
                                                    wire:click="attachmentModal(transaction.id)"
                                                >
                                                    <x-slot:text>
                                                        <span
                                                            x-text="
                                                                transaction
                                                                    .media?.[0]
                                                                    ?.file_name ??
                                                                '{{ __('Attachment') }}'
                                                            "
                                                        ></span>
                                                    </x-slot:text>
                                                </x-button>
                                                <x-button
                                                    sm
                                                    light
                                                    color="gray"
                                                    wire:click="assignOrdersModal(transaction.id)"
                                                    x-cloak
                                                    x-show="
                                                        parseFloat(
                                                            transaction.balance,
                                                        ) !== 0
                                                    "
                                                    :text="__('Assign order')"
                                                />
                                                <x-button
                                                    sm
                                                    light
                                                    color="gray"
                                                    wire:click="assignLedgerAccountModal(transaction.id)"
                                                    x-cloak
                                                    x-show="
                                                        parseFloat(
                                                            transaction.balance,
                                                        ) !== 0
                                                    "
                                                    :text="__('Assign ledger account')"
                                                />
                                                <x-button
                                                    sm
                                                    color="red"
                                                    wire:click="toggleIgnoreTransaction(transaction.id)"
                                                    x-cloak
                                                    x-show="
                                                        transaction.order_transactions_count ===
                                                            0 &&
                                                        transaction.ledger_account_transactions_count ===
                                                            0 &&
                                                        !transaction.is_ignored
                                                    "
                                                    :text="__('Ignore transaction')"
                                                />
                                                <x-button
                                                    sm
                                                    color="emerald"
                                                    wire:click="toggleIgnoreTransaction(transaction.id)"
                                                    x-cloak
                                                    x-show="
                                                        transaction.is_ignored
                                                    "
                                                    :text="__('Dont ignore transaction')"
                                                />
                                            </div>
                                            <div
                                                class="flex flex-col items-end gap-y-1 lg:pr-2"
                                            >
                                                <div
                                                    x-cloak
                                                    x-show="
                                                        parseFloat(
                                                            transaction.unassigned_amount ??
                                                                0,
                                                        ) !== 0
                                                    "
                                                    class="flex flex-row gap-2"
                                                >
                                                    <span class="font-semibold">
                                                        {{ __('Suggested') }}:
                                                    </span>
                                                    <span
                                                        x-html="
                                                            $nuxbe.format.money(
                                                                transaction.unassigned_amount,
                                                                {
                                                                    colored: true,
                                                                },
                                                            )
                                                        "
                                                    ></span>
                                                </div>
                                                <div
                                                    class="flex flex-row gap-2"
                                                >
                                                    <span class="font-semibold">
                                                        {{ __('Open') }}:
                                                    </span>
                                                    <span
                                                        x-html="
                                                            $nuxbe.format.money(
                                                                transaction.balance,
                                                                {
                                                                    colored: true,
                                                                },
                                                            )
                                                        "
                                                    ></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div>
                        <x-button
                            class="w-full"
                            color="emerald"
                            wire:flux-confirm.type.warning="{{ __('Accept all assignments|All suggested transaction assignments will be accepted') }}"
                            wire:click="acceptAll()"
                            loading="acceptAll()"
                            :text="__('Accept all')"
                            x-cloak
                            x-show="$wire.tab === '{{ __('Assignment suggestions') }}' && data.data?.length > 0"
                        />
                    </div>
                    <div
                        x-cloak
                        x-show="
                            (data.last_page ?? 1) > 1 || (data.total ?? 0) > 0
                        "
                        class="dark:border-secondary-700/50 flex items-center justify-between border-t border-gray-100 px-3 py-2.5"
                    >
                        <div class="flex flex-1 justify-between sm:hidden">
                            <x-button
                                color="secondary"
                                flat
                                sm
                                :text="__('Previous')"
                                x-bind:disabled="(data.current_page ?? 1) <= 1"
                                x-on:click="
                                    $wire.gotoPage((data.current_page ?? 1) - 1)
                                "
                            />
                            <x-button
                                color="secondary"
                                flat
                                sm
                                :text="__('Next')"
                                x-bind:disabled="
                                    (data.current_page ?? 1) >=
                                    (data.last_page ?? 1)
                                "
                                x-on:click="
                                    $wire.gotoPage((data.current_page ?? 1) + 1)
                                "
                            />
                        </div>
                        <div
                            class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between"
                        >
                            <div
                                class="flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400"
                            >
                                {{ __('Showing') }}
                                <span
                                    class="font-medium"
                                    x-text="data.from ?? 0"
                                ></span>
                                {{ __('to') }}
                                <span
                                    class="font-medium"
                                    x-text="data.to ?? 0"
                                ></span>
                                {{ __('of') }}
                                <span
                                    class="font-medium"
                                    x-text="data.total ?? 0"
                                ></span>
                                {{ __('results') }}
                                <x-select.native
                                    class="ml-1 border-0 bg-transparent py-0 pr-6 pl-1 text-sm text-gray-600 focus:ring-0 dark:text-gray-300"
                                    wire:model.live="perPage"
                                >
                                    <option value="15">
                                        15 {{ __('per page') }}
                                    </option>
                                    <option value="25">
                                        25 {{ __('per page') }}
                                    </option>
                                    <option value="50">
                                        50 {{ __('per page') }}
                                    </option>
                                    <option value="100">
                                        100 {{ __('per page') }}
                                    </option>
                                </x-select.native>
                            </div>
                            <nav
                                class="isolate inline-flex space-x-1 rounded-md"
                                aria-label="Pagination"
                            >
                                <x-button
                                    color="secondary"
                                    flat
                                    sm
                                    icon="chevron-left"
                                    x-bind:disabled="
                                        (data.current_page ?? 1) <= 1
                                    "
                                    x-on:click="
                                        $wire.gotoPage(
                                            (data.current_page ?? 1) - 1,
                                        )
                                    "
                                />
                                <template
                                    x-for="
                                        link in
                                        (data.links ?? []).filter(
                                            (l) =>
                                                /^\d+$/.test(l.label) ||
                                                l.label === '...',
                                        )
                                    "
                                    :key="link.label + '-' + (link.url ?? '')"
                                >
                                    <x-button
                                        color="secondary"
                                        flat
                                        sm
                                        x-bind:disabled="
                                            link.active || link.url === null
                                        "
                                        x-text="link.label"
                                        x-bind:class="{
                                            'bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400':
                                                link.active,
                                        }"
                                        x-on:click="
                                            link.url !== null &&
                                                !link.active &&
                                                $wire.gotoPage(
                                                    parseInt(link.label),
                                                )
                                        "
                                    />
                                </template>
                                <x-button
                                    color="secondary"
                                    flat
                                    sm
                                    icon="chevron-right"
                                    x-bind:disabled="
                                        (data.current_page ?? 1) >=
                                        (data.last_page ?? 1)
                                    "
                                    x-on:click="
                                        $wire.gotoPage(
                                            (data.current_page ?? 1) + 1,
                                        )
                                    "
                                />
                            </nav>
                        </div>
                    </div>
                </div>
            </x-tab>
        </div>
    </div>
</div>
