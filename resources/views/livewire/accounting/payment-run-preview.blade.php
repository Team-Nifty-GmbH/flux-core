<div
    x-data="{
        groupTotal(group) {
            return (group.orders || []).reduce(
                (sum, row) => sum + parseFloat(row.amount || 0),
                0,
            )
        },
        get grandTotal() {
            return ($wire.groups || []).reduce(
                (sum, group) => sum + this.groupTotal(group),
                0,
            )
        },
        transfersLabel(count) {
            const template = count === 1
                ? '{{ trans_choice('{1} :count Transfer|[2,*] :count Transfers', 1) }}'
                : '{{ trans_choice('{1} :count Transfer|[2,*] :count Transfers', 2) }}'

            return template.replace(':count', count)
        },
    }"
    class="flex flex-col gap-4"
>
    <template x-for="(group, groupIndex) in $wire.groups" :key="group.key">
        <x-card>
            <x-slot:header>
                <div>
                    <div class="font-medium" x-text="group.contact_name"></div>
                    <div class="text-xs text-gray-500" x-text="group.iban"></div>
                </div>
            </x-slot:header>
            <div class="flex flex-col gap-2">
                <template x-for="row in group.orders" :key="row.id">
                    <div class="flex flex-col gap-1 border-b py-2 last:border-0">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex flex-col gap-0.5">
                                <div class="flex items-center gap-2">
                                    <span x-text="row.invoice_number"></span>
                                    <x-badge
                                        x-cloak
                                        x-show="row.is_credit_note"
                                        color="amber"
                                        :text="__('Credit note')"
                                    />
                                    <x-badge
                                        x-cloak
                                        x-show="row.is_suggested"
                                        color="sky"
                                        :text="__('Automatically suggested')"
                                    />
                                </div>
                                <div class="flex items-center gap-3 text-xs text-gray-500">
                                    <span>
                                        {{ __('Total Gross Price') }}:
                                        <span x-html="$nuxbe.format.money(row.total_gross_price)"></span>
                                    </span>
                                    <span>
                                        {{ __('Balance') }}:
                                        <span x-html="$nuxbe.format.money(row.balance)"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-number
                                    x-model="row.amount"
                                    x-on:input.debounce.400ms="$wire.applyAmount(group.key, row.id, row.amount)"
                                    step="0.01"
                                    class="w-28"
                                />
                                <x-button
                                    x-cloak
                                    x-show="
                                        row.balance_due_discount &&
                                        row.payment_discount_percent
                                    "
                                    xs
                                    color="primary"
                                    :text="__('Apply Discount Amount')"
                                    x-on:click="$wire.applyDiscount(group.key, row.id)"
                                />
                                <x-button
                                    xs
                                    color="secondary"
                                    :text="__('Apply Balance Amount')"
                                    x-on:click="$wire.applyBalance(group.key, row.id)"
                                />
                                <x-button
                                    xs
                                    color="red"
                                    icon="trash"
                                    :text="__('Remove')"
                                    x-on:click="$wire.removeOrder(row.id)"
                                />
                            </div>
                        </div>
                        <div
                            x-cloak
                            x-show="row.capped_from"
                            class="text-xs text-amber-600"
                            x-text="
                                '{{ __(':amount of :full offset, the rest stays open') }}'
                                    .replace(':amount', $nuxbe.format.money(row.amount))
                                    .replace(':full', $nuxbe.format.money(row.capped_from))
                            "
                        ></div>
                    </div>
                </template>
                <div class="flex justify-end border-t pt-2">
                    <div
                        class="w-28 text-right font-bold"
                        x-html="$nuxbe.format.money(groupTotal(group), { colored: true })"
                    ></div>
                </div>
            </div>
            <x-slot:footer>
                <div class="text-xs text-gray-500">
                    {{ __('Purpose') }}:
                    <span x-text="group.purpose"></span>
                </div>
            </x-slot:footer>
        </x-card>
    </template>
    <x-card>
        <div class="flex items-center justify-between">
            <div
                x-text="transfersLabel(($wire.groups || []).length)"
            ></div>
            <div
                class="text-lg font-bold"
                x-html="$nuxbe.format.money(grandTotal, { colored: true })"
            ></div>
        </div>
        <x-slot:footer>
            <div class="flex justify-end gap-4">
                <x-button
                    color="secondary"
                    :text="__('Cancel')"
                    wire:click="cancel()"
                />
                <x-button
                    color="primary"
                    loading="createPaymentRun"
                    :text="__('Create Payment Run')"
                    wire:click="createPaymentRun()"
                    wire:flux-confirm="{{ __('Create Payment Run|Do you really want to create the Payment Run?|Cancel|Yes') }}"
                />
            </div>
        </x-slot:footer>
    </x-card>
</div>
