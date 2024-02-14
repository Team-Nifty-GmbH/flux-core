<x-modal name="edit-purchase-invoice" max-width="" spacing="">
    <x-card style="height: calc(100vh - 62.5px - 36px);" :title="__('Edit Purchase Invoice')" rounded="">
        <div class="h-full grid sm:grid-cols-2 gap-4 content-stretch">
            <div x-cloak x-show="! $wire.purchaseInvoiceForm.id">
                <x-flux::features.media.upload-form-object
                    :label="__('Invoice')"
                    :multiple="false"
                    accept="application/pdf;image/*"
                    wire:model="mediaForm"
                />
            </div>
            <div x-cloak x-show="$wire.purchaseInvoiceForm.id">
                <embed height="100%" lazy class="w-full h-full" x-bind:src="$wire.purchaseInvoiceForm.mediaUrl" type="application/pdf">
            </div>
            <div class="flex flex-col gap-4 overflow-auto">
                <x-select
                    wire:model="purchaseInvoiceForm.client_id"
                    option-key-value
                    :options="$clients"
                    :label="__('Client')"
                />
                <x-select
                    :label="__('Supplier')"
                    wire:model="purchaseInvoiceForm.contact_id"
                    option-value="contact_id"
                    option-label="label"
                    option-description="description"
                    template="user-option"
                    :async-data="[
                        'api' => route('search', \FluxErp\Models\Address::class),
                        'method' => 'POST',
                        'params' => [
                            'option-value' => 'contact_id',
                            'fields' => [
                                'name',
                                'contact_id',
                                'firstname',
                                'lastname',
                                'company',
                            ],
                            'where' => [
                                [
                                    'is_main_address',
                                    '=',
                                    true,
                                ]
                            ],
                            'with' => 'contact.media',
                        ]
                    ]"
                />
                <x-select
                    wire:model="purchaseInvoiceForm.currency_id"
                    option-key-value
                    :options="$currencies"
                    :label="__('Currency')"
                />
                <x-select
                    wire:model="purchaseInvoiceForm.order_type_id"
                    option-key-value
                    :options="$orderTypes"
                    :label="__('Order Type')"
                />
                <x-select
                    wire:model="purchaseInvoiceForm.payment_type_id"
                    option-key-value
                    :options="$paymentTypes"
                    :label="__('Payment Type')"
                />
                <x-input
                    wire:model="purchaseInvoiceForm.invoice_number"
                    :label="__('Invoice Number')"
                />
                <x-datetime-picker
                    without-time
                    wire:model="purchaseInvoiceForm.invoice_date"
                    :label="__('Invoice Date')"
                />
                <x-toggle wire:model="purchaseInvoiceForm.is_net" :label="__('Is Net')" />
                <div x-data="{
                    recalculatePrices(position, $event) {
                        const attribute = $event.target.getAttribute('x-model.number');

                        if (attribute === 'position.total_price') {
                            position.unit_price = position.total_price / position.amount;
                        } else if (attribute === 'position.unit_price') {
                            position.total_price = this.position.amount * position.unit_price;
                        } else if (attribute === 'position.amount') {
                            position.total_price = position.amount * position.unit_price;
                        }
                    }
                }">
                    <template x-for="(position, index) in $wire.purchaseInvoiceForm.purchase_invoice_positions">
                        <x-card>
                            <div class="flex flex-col gap-4">
                                <x-select
                                    :label="__('Product')"
                                    x-on:selected="position.name = $event.detail.label; position.product_id = $event.detail.id"
                                    option-value="id"
                                    option-label="label"
                                    option-description="product_number"
                                    :clearable="false"
                                    :template="[
                                        'name'   => 'user-option',
                                    ]"
                                    :async-data="[
                                        'api' => route('search', \FluxErp\Models\Product::class),
                                        'params' => [
                                            'whereDoesntHave' => 'children',
                                            'fields' => ['id', 'name', 'product_number'],
                                            'with' => 'media',
                                        ]
                                    ]"
                                />
                                <x-input
                                    x-model="position.name"
                                    :label="__('Name')"
                                />
                                <div class="flex gap-1.5">
                                    <x-input
                                        x-on:keyup="recalculatePrices(position, $event)"
                                        x-model.number="position.amount"
                                        :label="__('Amount')"
                                    />
                                    <x-select
                                        :options="$vatRates"
                                        option-key-value
                                        :label="__('Vat Rate')"
                                        x-on:selected="position.vat_rate_id = $event.detail.value"
                                    />
                                    <x-input
                                        x-on:keyup="recalculatePrices(position, $event)"
                                        x-model.number="position.unit_price"
                                        :label="__('Unit Price')"
                                    />
                                    <x-input
                                        x-on:keyup="recalculatePrices(position, $event)"
                                        x-model.number="position.total_price"
                                        :label="__('Total Price')"
                                    />
                                    <x-select
                                        :label="__('Ledger Account')"
                                        option-value="id"
                                        option-label="name"
                                        option-description="description"
                                        x-model.number="position.ledger_account_id"
                                        :async-data="[
                                            'api' => route('search', \FluxErp\Models\LedgerAccount::class),
                                            'params' => [
                                                'where' => [
                                                    [
                                                        'ledger_account_type_enum',
                                                        '=',
                                                        \FluxErp\Enums\LedgerAccountTypeEnum::Expense,
                                                    ],
                                                ]
                                            ]
                                        ]"
                                    />
                                </div>
                            </div>
                            <x-slot:footer>
                                <div class="flex justify-end">
                                    <x-button
                                        negative
                                        :label="__('Delete')"
                                        x-on:click="$wire.purchaseInvoiceForm.purchase_invoice_positions.splice(index, 1)"
                                    />
                                </div>
                            </x-slot:footer>
                        </x-card>
                    </template>
                    <div class="flex justify-center pt-4">
                        <x-button
                            positive
                            :label="__('Add Position')"
                            x-on:click="$wire.purchaseInvoiceForm.purchase_invoice_positions.push({ vat_rate_id: null, product_id: null, name: null, amount: 1, unit_price: 0, total_price: 0 })"
                        />
                    </div>
                </div>
            </div>
        </div>
        <x-slot:footer>
            <div class="flex justify-between">
                <div>
                    <x-button
                        negative
                        x-cloak
                        x-show="$wire.purchaseInvoiceForm.id"
                        :label="__('Delete')"
                        wire:click="delete().then((success) => { if (success) close(); })"
                        wire:confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Purchase Invoice')]) }}"
                    />
                </div>
                <div class="flex gap-1.5">
                    <x-button
                        :label="__('Cancel')"
                        x-on:click="close()"
                    />
                    <x-button
                        primary
                        :label="__('Save')"
                        wire:click="save().then((success) => { if (success) close(); })"
                    />
                    <x-button
                        primary
                        x-cloak
                        x-show="$wire.purchaseInvoiceForm.id"
                        :label="__('Finish')"
                        wire:click="finish().then((success) => { if (success) close(); })"
                    />
                </div>
            </div>
        </x-slot:footer>
    </x-card>
</x-modal>
