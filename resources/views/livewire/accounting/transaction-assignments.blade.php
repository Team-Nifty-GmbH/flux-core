<div
    x-on:refresh-transactions.window="init()"
    x-data="{
        data: [],
        async assignOrders(dataTableId) {
            const dataTable = Livewire.find(dataTableId)
            await $wire.assignOrders(dataTable.selected)
            dataTable.selected = []
        },
        async init() {
            this.data = await $wire.loadTransactions()
        },
    }"
>
    <x-flux::spinner />
    @teleport('body')
        <x-modal id="transaction-assign-orders-modal" size="7xl">
            <x-slot:title>
                <div class="flex w-full flex-col">
                    <div class="flex flex-row gap-2">
                        <span
                            x-text="$wire.transactionForm.counterpart_name"
                        ></span>
                        <span
                            class="text-red-600"
                            x-html="formatters.coloredMoney($wire.transactionForm.amount)"
                        ></span>
                    </div>
                    <div
                        class="text-xs"
                        x-text="formatters.date($wire.transactionForm.booking_date)"
                    ></div>
                    <div
                        class="mt-2 flex w-full flex-row justify-between border-t border-slate-200 pt-2"
                    >
                        <div x-text="$wire.transactionForm.purpose"></div>
                    </div>
                </div>
            </x-slot>
            <livewire:accounting.order-list lazy />
            <x-slot:footer>
                <x-button
                    color="secondary"
                    :text="__('Cancel')"
                    x-on:click="$modalClose('transaction-assign-orders-modal')"
                />
                <x-button
                    :text="__('Assign')"
                    x-on:click="assignOrders($root.querySelector('[tall-datatable]').parentNode.getAttribute('wire:id'))"
                />
            </x-slot>
        </x-modal>
    @endteleport

    @teleport('body')
        <x-modal id="transaction-comments-modal">
            <x-slot:title>
                <div class="flex w-full flex-col">
                    <div class="flex flex-row gap-2">
                        <span
                            x-text="$wire.transactionForm.counterpart_name"
                        ></span>
                        <span
                            class="text-red-600"
                            x-html="formatters.coloredMoney($wire.transactionForm.amount)"
                        ></span>
                    </div>
                    <div
                        class="text-xs"
                        x-text="formatters.date($wire.transactionForm.booking_date)"
                    ></div>
                    <div
                        class="mt-2 flex w-full flex-row justify-between border-t border-slate-200 pt-2"
                    >
                        <div x-text="$wire.transactionForm.purpose"></div>
                    </div>
                </div>
            </x-slot>
            <livewire:features.comments.comments
                :model-type="\FluxErp\Models\Transaction::class"
                wire:model="transactionForm.id"
                lazy
                :is-public="false"
            />
            <x-slot:footer>
                <x-button
                    color="secondary"
                    :text="__('Cancel')"
                    x-on:click="$modalClose('transaction-comments-modal')"
                />
            </x-slot>
        </x-modal>
    @endteleport

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
                    x-on:click="$wire.saveOrderTransaction()"
                />
            </x-slot>
        </x-modal>
    @endteleport

    <div class="flex flex-col gap-4 text-sm">
        <div class="flex flex-col">
            <x-tab wire:model.live="tab">
                <x-tab.items :tab="__('All')" />
                <x-tab.items :tab="__('Assignment suggestions')">
                    <x-slot:right>
                        <x-badge round="full">
                            <x-slot:text>
                                <span
                                    x-text="$wire.suggestionCount ?? 0"
                                ></span>
                            </x-slot>
                        </x-badge>
                    </x-slot>
                </x-tab.items>
                <x-tab.items :tab="__('Open transactions')">
                    <x-slot:right>
                        <x-badge round="full">
                            <x-slot:text>
                                <span
                                    x-text="$wire.unassignedCount ?? 0"
                                ></span>
                            </x-slot>
                        </x-badge>
                    </x-slot>
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
                                multiple
                                select="label:name|value:id|description:iban"
                                :placeholder="__('Choose bank account')"
                                :options="$bankConnections"
                                wire:model.live="bankAccounts"
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
                            </x-slot>
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
                                        multiple
                                        select="label:name|value:id|description:iban"
                                        :placeholder="__('Choose bank account')"
                                        :options="$bankConnections"
                                        wire:model.live="bankAccounts"
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
                                class="flex w-full flex-col border-b-2 border-slate-200 p-4 lg:w-1/2 lg:border-b-0 lg:border-r-2"
                            >
                                <div class="flex justify-between gap-4">
                                    <div class="flex gap-4">
                                        <x-avatar
                                            borderless
                                            image
                                            class="ring-4 ring-offset-2"
                                            x-bind:class="parseFloat(transaction.balance) === 0 ? 'ring-emerald-500' : (transaction.order_transactions_count > 0 ? 'ring-amber-500' : 'ring-red-500')"
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
                                                x-text="transaction.counterpart_iban"
                                            ></div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <div
                                            class="flex w-full justify-end text-lg font-semibold"
                                            x-html="formatters.coloredMoney(transaction.amount)"
                                        ></div>
                                        <div
                                            class="flex w-full flex-row items-center justify-end gap-2 font-semibold"
                                        >
                                            <span
                                                x-text="formatters.date(transaction.booking_date)"
                                            ></span>
                                            <x-dropdown icon="banknotes">
                                                <div class="p-2">
                                                    <b
                                                        x-text="transaction.bank_connection.bank_name"
                                                    ></b>
                                                    <br />
                                                    <span
                                                        x-text="transaction.bank_connection.iban"
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
                                <template
                                    x-for="order in transaction.orders"
                                >
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
                                                x-show="! order.pivot.is_accepted"
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
                                                class="absolute left-0 top-0 opacity-0 transition-opacity duration-200 group-hover/button:opacity-100"
                                            />
                                        </div>
                                        <div
                                            class="flex w-full justify-between rounded bg-slate-100 p-2 font-semibold"
                                        >
                                            <div class="flex gap-2">
                                                <div
                                                    class="hidden overflow-hidden transition-all duration-200 group-hover:block"
                                                >
                                                    <x-button
                                                        class="h-full"
                                                        color="secondary"
                                                        sm
                                                        icon="eye"
                                                        x-on:click="$openDetailModal('{{ route('orders.id', ['id' => '__key__']) }}'.replace('__key__', order.id))"
                                                    />
                                                </div>
                                                <div>
                                                    <div
                                                        x-text="order.address_invoice?.name"
                                                    ></div>
                                                    <div
                                                        x-text="order.invoice_number"
                                                    ></div>
                                                </div>
                                            </div>
                                            <div class="flex gap-2">
                                                <div>
                                                    <div
                                                        class="flex w-full justify-end font-semibold"
                                                        x-html="formatters.coloredMoney(order.pivot.amount)"
                                                    ></div>
                                                    <div
                                                        class="flex w-full flex-row items-center justify-end gap-2 font-semibold"
                                                    >
                                                        <span
                                                            x-html="formatters.date(order.invoice_date)"
                                                        ></span>
                                                    </div>
                                                </div>
                                                <div
                                                    class="hidden overflow-hidden transition-all duration-200 group-hover:block"
                                                >
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
                                <div class="px-2 pt-2">
                                    <div class="flex flex-col">
                                        <div
                                            class="flex flex-row items-center justify-between border-t border-slate-200 pt-2"
                                        >
                                            <div class="flex flex-row gap-2">
                                                <x-button
                                                    sm
                                                    x-cloak
                                                    x-show="transaction.suggestions > 0"
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
                                                                x-show="transaction.comments_count"
                                                                x-text="'(' + transaction.comments_count + ')'"
                                                            ></span>
                                                        </div>
                                                    </x-slot>
                                                </x-button>
                                                <x-button
                                                    sm
                                                    light
                                                    color="gray"
                                                    wire:click="assignOrdersModal(transaction.id)"
                                                    x-cloak
                                                    x-show="parseFloat(transaction.balance) !== 0"
                                                    :text="__('Assign order')"
                                                />
                                                <x-button
                                                    sm
                                                    color="red"
                                                    wire:click="ignoreTransaction(transaction.id)"
                                                    x-cloak
                                                    x-show="transaction.order_transactions_count === 0 && ! transaction.is_ignored"
                                                    :text="__('Ignore transaction')"
                                                />
                                                <x-button
                                                    sm
                                                    color="emerald"
                                                    wire:click="ignoreTransaction(transaction.id)"
                                                    x-cloak
                                                    x-show="transaction.is_ignored"
                                                    :text="__('Dont ignore transaction')"
                                                />
                                            </div>
                                            <div
                                                class="flex flex-row gap-2 pr-2"
                                            >
                                                <span class="font-semibold">
                                                    {{ __('Open') }}:
                                                </span>
                                                <span
                                                    x-html="formatters.coloredMoney(transaction.balance)"
                                                ></span>
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
                            :text="__('Accept all')"
                            x-cloak
                            x-show="$wire.tab === '{{ __('Assignment suggestions') }}' && data.data.length > 0"
                        />
                    </div>
                    <x-tall-datatables::pagination />
                </div>
            </x-tab>
        </div>
    </div>
</div>
