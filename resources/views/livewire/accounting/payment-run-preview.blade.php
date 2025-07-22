<div
    x-data="{
        get totalAmount() {
            return Object.values($wire.orders || {}).reduce(
                (sum, order) => sum + parseFloat(order.amount || 0),
                0,
            )
        },
    }"
>
    <x-card
        :header="__('Payment Run Preview') . ' - ' . __($this->paymentRunTypeEnum)"
    >
        <x-flux::table>
            <x-slot:header>
                <x-flux::table.head-cell>
                    {{ __('Invoice Number') }}
                </x-flux::table.head-cell>
                <x-flux::table.head-cell>
                    {{ __('Contact') }}
                </x-flux::table.head-cell>
                <x-flux::table.head-cell>
                    {{ __('Total Gross Price') }}
                </x-flux::table.head-cell>
                <x-flux::table.head-cell>
                    {{ __('Balance') }}
                </x-flux::table.head-cell>
                <x-flux::table.head-cell>
                    {{ __('Payment Amount') }}
                </x-flux::table.head-cell>
                <x-flux::table.head-cell>
                    {{ __('Actions') }}
                </x-flux::table.head-cell>
            </x-slot>

            <template x-for="order in $wire.orders" :key="order.id">
                <x-flux::table.row>
                    <x-flux::table.cell
                        x-text="order.invoice_number"
                    ></x-flux::table.cell>
                    <x-flux::table.cell
                        x-text="order.contact_name || order.address_name || '-'"
                    ></x-flux::table.cell>
                    <x-flux::table.cell
                        x-html="window.formatters.coloredMoney(order.total_gross_price)"
                    ></x-flux::table.cell>
                    <x-flux::table.cell
                        x-html="window.formatters.coloredMoney(order.balance)"
                    ></x-flux::table.cell>
                    <x-flux::table.cell>
                        <x-number
                            x-model="order.amount"
                            step="0.01"
                            class="w-24"
                        />
                    </x-flux::table.cell>
                    <x-flux::table.cell>
                        <x-button
                            wire:click="removeOrder(order.id)"
                            color="red"
                            size="sm"
                            icon="trash"
                            :text="__('Remove')"
                        />
                    </x-flux::table.cell>
                </x-flux::table.row>
            </template>

            <x-slot:footer>
                <x-flux::table.head-cell
                    colspan="4"
                    class="text-right font-medium"
                >
                    {{ __('Total') }}:
                </x-flux::table.head-cell>
                <x-flux::table.head-cell
                    class="font-bold"
                    x-html="window.formatters.coloredMoney(Math.abs(totalAmount))"
                ></x-flux::table.head-cell>
                <x-flux::table.head-cell></x-flux::table.head-cell>
            </x-slot>
        </x-flux::table>

        <x-slot:footer>
            <div class="flex justify-end gap-4">
                <x-button
                    color="secondary"
                    :text="__('Cancel')"
                    wire:click="cancel"
                />

                <x-button
                    color="primary"
                    loading="createPaymentRun"
                    :text="__('Create Payment Run')"
                    wire:click="createPaymentRun"
                    wire:flux-confirm="{{ __('Create Payment Run|Do you really want to create the Payment Run?|Cancel|Yes') }}"
                />
            </div>
        </x-slot>
    </x-card>
</div>
