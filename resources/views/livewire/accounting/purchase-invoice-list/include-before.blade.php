<x-modal name="edit-purchase-invoice" max-width="" spacing="">
    <x-card style="height: calc(100vh - 62.5px - 36px);" :title="__('Edit Purchase Invoice')" rounded="">
        <div class="h-full grid sm:grid-cols-2 gap-4 content-stretch">
            @section('invoice-file')
                @section('invoice-upload')
                    <div x-cloak x-show="! $wire.purchaseInvoiceForm.id">
                        <x-flux::features.media.upload-form-object
                            :label="__('Invoice')"
                            :multiple="false"
                            accept="application/pdf;image/*"
                            wire:model="mediaForm"
                        />
                    </div>
                @show
                @section('invoice-preview')
                    <div x-cloak x-show="$wire.purchaseInvoiceForm.id">
                        <iframe width="100%" height="100%" lazy class="w-full h-full" x-bind:src="$wire.purchaseInvoiceForm.mediaUrl" type="application/pdf">
                        </iframe>
                    </div>
                @show
            @show
            <div class="flex flex-col gap-1.5 overflow-auto">
                @section('client')
                    @if(count($clients ?? []) > 1)
                        <div x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'">
                            <x-select
                                x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                wire:model="purchaseInvoiceForm.client_id"
                                option-key-value
                                :options="$clients"
                                :label="__('Client')"
                            />
                        </div>
                    @endif
                @show
                @section('supplier-data')
                    <div x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'">
                        <x-select
                            x-on:selected="$wire.fillFromSelectedContact($event.detail?.contact?.id)"
                            x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
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
                                        'contact',
                                    ],
                                    'where' => [
                                        [
                                            'is_main_address',
                                            '=',
                                            true,
                                        ]
                                    ],
                                    'with' => [
                                        'contact.media',
                                    ],
                                ]
                            ]"
                        />
                    </div>
                    <div class="flex w-full gap-1.5">
                        @if(count($currencies ?? []) > 1)
                            <div class="flex-1" x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'">
                                <x-select
                                    x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                    wire:model="purchaseInvoiceForm.currency_id"
                                    option-key-value
                                    :options="$currencies"
                                    :label="__('Currency')"
                                />
                            </div>
                        @endif
                        <div class="flex-1" x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'">
                            <x-select
                                x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                wire:model="purchaseInvoiceForm.order_type_id"
                                option-key-value
                                :options="$orderTypes"
                                :label="__('Order Type')"
                            />
                        </div>
                        <div class="flex-1" x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'">
                            <x-select
                                :label="__('Approval User')"
                                wire:model="purchaseInvoiceForm.approval_user_id"
                                option-value="id"
                                option-label="label"
                                :template="[
                                    'name'   => 'user-option',
                                ]"
                                :async-data="[
                                    'api' => route('search', \FluxErp\Models\User::class),
                                    'method' => 'POST',
                                    'params' => [
                                        'with' => 'media',
                                    ]
                                ]"
                            />
                        </div>
                        <div class="flex-1" x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'">
                            <x-select
                                x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                wire:model="purchaseInvoiceForm.payment_type_id"
                                option-key-value
                                :options="$paymentTypes"
                                :label="__('Payment Type')"
                            />
                        </div>
                    </div>
                @show
                @section('invoice-data')
                    <div class="flex gap-1.5 w-full">
                        <x-input class="flex-1"
                            x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                            wire:model="purchaseInvoiceForm.invoice_number"
                            :label="__('Invoice Number')"
                        />
                        <div class="flex-1" x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'">
                            <x-datetime-picker
                                x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                without-time
                                wire:model="purchaseInvoiceForm.invoice_date"
                                :label="__('Invoice Date')"
                            />
                        </div>
                    </div>
                    <div class="flex gap-1.5 w-full">
                        <div class="flex-1" x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'">
                            <x-datetime-picker
                                x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                without-time
                                wire:model="purchaseInvoiceForm.system_delivery_date"
                                :label="__('Performance/Delivery date')"
                            />
                        </div>
                        <div class="flex-1" x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'">
                            <x-datetime-picker
                                x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                without-time
                                wire:model="purchaseInvoiceForm.system_delivery_date_end"
                                :label="__('Performance/Delivery date end')"
                            />
                        </div>
                    </div>
                @show
                @section('bank-data')
                    <div class="grid grid-cols-2 gap-1.5">
                        <div class="col-span-2">
                            <x-select
                                :label="__('Lay out user')"
                                option-value="id"
                                option-label="label"
                                autocomplete="off"
                                x-on:selected="$wire.purchaseInvoiceForm.iban = $event.detail?.iban; $wire.purchaseInvoiceForm.bic = $event.detail?.bic; $wire.purchaseInvoiceForm.bank_name = $event.detail?.bank_name; $wire.purchaseInvoiceForm.account_holder = $event.detail?.account_holder"
                                wire:model="purchaseInvoiceForm.lay_out_user_id"
                                :template="[
                                    'name'   => 'user-option',
                                ]"
                                :async-data="[
                                    'api' => route('search', \FluxErp\Models\User::class),
                                    'method' => 'POST',
                                    'params' => [
                                        'with' => 'media',
                                        'fields' => [
                                            'id',
                                            'name',
                                            'email',
                                            'iban',
                                            'bic',
                                            'bank_name',
                                            'account_holder',
                                        ]
                                    ]
                                ]"
                            />
                        </div>
                        <x-input wire:model="purchaseInvoiceForm.account_holder" :label="__('Account Holder')"/>
                        <x-input wire:model="purchaseInvoiceForm.iban" :label="__('IBAN')"/>
                        <x-input wire:model="purchaseInvoiceForm.bic" :label="__('BIC')"/>
                        <x-input wire:model="purchaseInvoiceForm.bank_name" :label="__('Bank Name')"/>
                    </div>
                @show
                @section('purchase-invoice-positions')
                    <x-toggle
                        x-bind:disabled="$wire.purchaseInvoiceForm.order_id"
                        wire:model="purchaseInvoiceForm.is_net"
                        :label="__('Is Net')"
                    />
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
                                    <div x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'">
                                        <x-select
                                            :label="__('Product')"
                                            x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                            x-on:selected="position.name = $event.detail?.label; position.product_id = $event.detail?.id"
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
                                    </div>
                                    <x-input
                                        x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                        x-model="position.name"
                                        :label="__('Name')"
                                    />
                                    <div class="flex flex-col md:flex-row gap-1.5">
                                        <x-inputs.number
                                            step="0.01"
                                            x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                            x-on:keyup="recalculatePrices(position, $event)"
                                            x-model.number="position.amount"
                                            :label="__('Amount')"
                                        />
                                        <div x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'" class="w-full">
                                            <x-select
                                                x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                                :options="$vatRates"
                                                option-key-value
                                                :label="__('Vat Rate')"
                                                x-model.number="position.vat_rate_id"
                                                x-on:selected="position.vat_rate_id = $event.detail?.value"
                                                x-init="$el.value = position.vat_rate_id; fillSelectedFromInputValue();"
                                            />
                                        </div>
                                        <x-inputs.number
                                            step="0.01"
                                            x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                            x-on:keyup="recalculatePrices(position, $event)"
                                            x-model.number="position.unit_price"
                                            :label="__('Unit Price')"
                                        />
                                        <x-inputs.number
                                            step="0.01"
                                            x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                            x-on:keyup="recalculatePrices(position, $event)"
                                            x-model.number="position.total_price"
                                            :label="__('Total Price')"
                                        />
                                    </div>
                                    <div x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'" class="w-full">
                                        <x-select
                                            x-on:selected="position.ledger_account_id = $event.detail?.value"
                                            x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                            :label="__('Ledger Account')"
                                            option-value="id"
                                            option-label="name"
                                            option-description="number"
                                            x-init="$el.value = position.ledger_account_id; init();"
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
                                            x-cloak
                                            x-show="! $wire.purchaseInvoiceForm.order_id"
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
                                x-cloak
                                x-show="! $wire.purchaseInvoiceForm.order_id"
                                positive
                                :label="__('Add Position')"
                                x-on:click="$wire.purchaseInvoiceForm.purchase_invoice_positions.push({ ledger_account_id: $wire.purchaseInvoiceForm.lastLedgerAccountId, vat_rate_id: null, product_id: null, name: null, amount: 1, unit_price: 0, total_price: 0 })"
                            />
                        </div>
                    </div>
                @show
            </div>
        </div>
        <x-slot:footer>
            @section('footer-buttons')
                <div class="flex justify-between">
                    <div>
                        @section('footer-buttons.left')
                            <x-button
                                negative
                                x-cloak
                                x-show="$wire.purchaseInvoiceForm.id && ! $wire.purchaseInvoiceForm.order_id"
                                :label="__('Delete')"
                                wire:click="delete().then((success) => { if (success) close(); })"
                                wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Purchase Invoice')]) }}"
                            />
                        @show
                    </div>
                    <div class="flex gap-1.5">
                        @section('footer-buttons.right')
                            <x-button
                                :label="__('Cancel')"
                                x-on:click="close()"
                            />
                            <x-button
                                primary
                                x-cloak
                                x-show="! $wire.purchaseInvoiceForm.order_id"
                                :label="__('Save')"
                                wire:click="save().then((success) => { if (success) close(); })"
                            />
                            <x-button
                                primary
                                x-cloak
                                x-show="$wire.purchaseInvoiceForm.id && ! $wire.purchaseInvoiceForm.order_id"
                                :label="__('Finish')"
                                wire:click="finish().then((success) => { if (success) close(); })"
                            />
                        @show
                    </div>
                </div>
            @show
        </x-slot:footer>
    </x-card>
</x-modal>
