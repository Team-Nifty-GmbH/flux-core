<div>
    <x-modal
        id="edit-purchase-invoice-modal"
        size="full"
        scope="fullscreen"
        scrollable
        persistent
    >
        <div
            class="grid h-full min-h-screen content-stretch gap-4 sm:grid-cols-2"
        >
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
                <iframe
                    width="100%"
                    height="100%"
                    lazy
                    class="h-full w-full"
                    x-bind:src="$wire.purchaseInvoiceForm.mediaUrl"
                    type="application/pdf"
                ></iframe>
            </div>
            @show
            @show
            <div class="flex flex-col gap-1.5 overflow-auto px-2">
                @section('client')
                @if (count($clients ?? []) > 1)
                    <div
                        x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                    >
                        <x-select.styled
                            x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                            wire:model="purchaseInvoiceForm.client_id"
                            :label="__('Client')"
                            select="label:name|value:id"
                            :options="$clients"
                        />
                    </div>
                @endif

                @show
                @section('supplier-data')
                <div
                    x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                >
                    <x-select.styled
                        x-on:select="$wire.fillFromSelectedContact($event.detail.select?.contact?.id)"
                        x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                        :label="__('Supplier')"
                        wire:model="purchaseInvoiceForm.contact_id"
                        select="label:label|value:contact_id"
                        unfiltered
                        :request="[
                            'url' => route('search', \FluxErp\Models\Address::class),
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
                                    ],
                                ],
                                'with' => [
                                    'contact.media',
                                ],
                            ],
                        ]"
                    />
                </div>
                <div class="flex w-full gap-1.5">
                    @if (count($currencies ?? []) > 1)
                        <div
                            class="flex-1"
                            x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                        >
                            <x-select.styled
                                x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                wire:model="purchaseInvoiceForm.currency_id"
                                :label="__('Currency')"
                                select="label:name|value:id"
                                :options="$currencies"
                            />
                        </div>
                    @endif

                    <div
                        class="flex-1"
                        x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                    >
                        <x-select.styled
                            x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                            wire:model="purchaseInvoiceForm.order_type_id"
                            :label="__('Order Type')"
                            select="label:name|value:id"
                            :options="$orderTypes"
                        />
                    </div>
                    <div
                        class="flex-1"
                        x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                    >
                        <x-select.styled
                            :label="__('Approval User')"
                            wire:model="purchaseInvoiceForm.approval_user_id"
                            select="label:label|value:id"
                            unfiltered
                            :request="[
                                'url' => route('search', \FluxErp\Models\User::class),
                                'method' => 'POST',
                                'params' => [
                                    'with' => 'media',
                                ],
                            ]"
                        />
                    </div>
                    <div
                        class="flex-1"
                        x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                    >
                        <x-select.styled
                            x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                            wire:model="purchaseInvoiceForm.payment_type_id"
                            :label="__('Payment Type')"
                            select="label:name|value:id"
                            :options="$paymentTypes"
                        />
                    </div>
                </div>
                @show
                @section('payment-terms')
                <div class="flex w-full gap-1.5">
                    <div
                        class="flex-1"
                        x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                    >
                        <x-date
                            x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                            without-time
                            wire:model="purchaseInvoiceForm.payment_target_date"
                            :label="__('Payment Target Date')"
                        />
                    </div>
                    <div
                        class="flex-1"
                        x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                    >
                        <x-date
                            x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                            without-time
                            wire:model="purchaseInvoiceForm.payment_discount_target_date"
                            :label="__('Payment Discount Target Date')"
                        />
                    </div>
                    <div
                        class="flex-1"
                        x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                    >
                        <x-number
                            x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                            wire:model="purchaseInvoiceForm.payment_discount_percent"
                            step="0.01"
                            min="0"
                            max="100"
                            :label="__('Payment Discount Percent')"
                        />
                    </div>
                </div>
                @show
                @section('invoice-data')
                <div class="flex w-full gap-1.5">
                    <x-input
                        class="flex-1"
                        x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                        wire:model="purchaseInvoiceForm.invoice_number"
                        :label="__('Invoice Number')"
                    />
                    <div
                        class="flex-1"
                        x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                    >
                        <x-date
                            x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                            without-time
                            wire:model="purchaseInvoiceForm.invoice_date"
                            :label="__('Invoice Date')"
                        />
                    </div>
                </div>
                <div class="flex w-full gap-1.5">
                    <div
                        class="flex-1"
                        x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                    >
                        <x-date
                            x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                            without-time
                            wire:model="purchaseInvoiceForm.system_delivery_date"
                            :label="__('Performance/Delivery date')"
                        />
                    </div>
                    <div
                        class="flex-1"
                        x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                    >
                        <x-date
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
                        <x-select.styled
                            :label="__('Lay out user')"
                            autocomplete="off"
                            x-on:select="$wire.purchaseInvoiceForm.iban = $event.detail.select?.iban; $wire.purchaseInvoiceForm.bic = $event.detail.select?.bic; $wire.purchaseInvoiceForm.bank_name = $event.detail.select?.bank_name; $wire.purchaseInvoiceForm.account_holder = $event.detail.select?.account_holder"
                            wire:model="purchaseInvoiceForm.lay_out_user_id"
                            select="label:label|value:id"
                            unfiltered
                            :request="[
                                'url' => route('search', \FluxErp\Models\User::class),
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
                                    ],
                                ],
                            ]"
                        />
                    </div>
                    <x-input
                        wire:model="purchaseInvoiceForm.account_holder"
                        :label="__('Account Holder')"
                    />
                    <x-input
                        wire:model="purchaseInvoiceForm.iban"
                        :label="__('IBAN')"
                    />
                    <x-input
                        wire:model="purchaseInvoiceForm.bic"
                        :label="__('BIC')"
                    />
                    <x-input
                        wire:model="purchaseInvoiceForm.bank_name"
                        :label="__('Bank Name')"
                    />
                </div>
                @show
                @section('purchase-invoice-positions')
                <x-toggle
                    x-bind:disabled="$wire.purchaseInvoiceForm.order_id"
                    wire:model="purchaseInvoiceForm.is_net"
                    :label="__('Is Net')"
                />
                <x-number
                    x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                    wire:model="purchaseInvoiceForm.total_gross_price"
                    step="0.01"
                    min="0"
                    :label="__('Total Gross Price')"
                />
                <div
                    x-data="{
                        recalculatePrices(position, $event) {
                            const attribute = $event.target.getAttribute('x-model.number')

                            if (attribute === 'position.total_price') {
                                position.unit_price = position.total_price / position.amount
                            } else if (attribute === 'position.unit_price') {
                                position.total_price = this.position.amount * position.unit_price
                            } else if (attribute === 'position.amount') {
                                position.total_price = position.amount * position.unit_price
                            }
                        },
                    }"
                >
                    <template
                        x-for="(position, index) in $wire.purchaseInvoiceForm.purchase_invoice_positions"
                    >
                        <x-card>
                            <div class="flex flex-col gap-4">
                                <div
                                    x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                                >
                                    <x-select.styled
                                        :label="__('Product')"
                                        x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                        x-model.number="position.product_id"
                                        x-on:select="position.name = $event.detail.select?.label"
                                        select="label:label|value:id|description:product_number"
                                        unfiltered
                                        :request="[
                                            'url' => route('search', \FluxErp\Models\Product::class),
                                            'method' => 'POST',
                                            'params' => [
                                                'whereDoesntHave' => 'children',
                                                'fields' => [
                                                    'id',
                                                    'name',
                                                    'product_number',
                                                ],
                                                'with' => 'media',
                                            ],
                                        ]"
                                    />
                                </div>
                                <x-input
                                    x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                    x-model="position.name"
                                    :label="__('Name')"
                                />
                                <div class="flex flex-col gap-1.5 xl:flex-row">
                                    <x-number
                                        step="0.01"
                                        x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                        x-on:keyup="recalculatePrices(position, $event)"
                                        x-model.number="position.amount"
                                        :label="__('Amount')"
                                    />
                                    <div
                                        x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                                    >
                                        <x-select.styled
                                            x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                            :label="__('Vat Rate')"
                                            x-model.number="position.vat_rate_id"
                                            x-init="$el.value = position.vat_rate_id; fillSelectedFromInputValue();"
                                            select="label:name|value:id"
                                            :options="$vatRates"
                                        />
                                    </div>
                                    <x-number
                                        step="0.01"
                                        x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                        x-on:keyup="recalculatePrices(position, $event)"
                                        x-model.number="position.unit_price"
                                        :label="__('Unit Price')"
                                    />
                                    <x-number
                                        step="0.01"
                                        x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                        x-on:keyup="recalculatePrices(position, $event)"
                                        x-model.number="position.total_price"
                                        :label="__('Total Price')"
                                    />
                                </div>
                                <div
                                    x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                                    class="w-full"
                                >
                                    <x-select.styled
                                        x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                        :label="__('Ledger Account')"
                                        x-init="$el.value = position.ledger_account_id; init();"
                                        x-model.number="position.ledger_account_id"
                                        select="label:name|value:id|description:number"
                                        unfiltered
                                        :request="[
                                            'url' => route('search', \FluxErp\Models\LedgerAccount::class),
                                            'method' => 'POST',
                                            'params' => [
                                                'where' => [
                                                    [
                                                        'ledger_account_type_enum',
                                                        '=',
                                                        \FluxErp\Enums\LedgerAccountTypeEnum::Expense,
                                                    ],
                                                ],
                                            ],
                                        ]"
                                    />
                                </div>
                            </div>
                            <x-slot:footer>
                                <div class="flex justify-end">
                                    @canAction(\FluxErp\Actions\PurchaseInvoicePosition\DeletePurchaseInvoicePosition::class)
                                        <x-button
                                            x-cloak
                                            x-show="! $wire.purchaseInvoiceForm.order_id"
                                            color="red"
                                            :text="__('Delete')"
                                            x-on:click="$wire.purchaseInvoiceForm.purchase_invoice_positions.splice(index, 1)"
                                        />
                                    @endcanAction
                                </div>
                            </x-slot>
                        </x-card>
                    </template>
                    <div class="flex justify-center pt-4">
                        @canAction(\FluxErp\Actions\PurchaseInvoicePosition\CreatePurchaseInvoicePosition::class)
                            <x-button
                                x-cloak
                                x-show="! $wire.purchaseInvoiceForm.order_id"
                                color="emerald"
                                :text="__('Add Position')"
                                x-on:click="$wire.purchaseInvoiceForm.purchase_invoice_positions.push({ ledger_account_id: $wire.purchaseInvoiceForm.lastLedgerAccountId, vat_rate_id: null, product_id: null, name: null, amount: 1, unit_price: 0, total_price: 0 })"
                            />
                        @endcanAction
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
                    @canAction(\FluxErp\Actions\PurchaseInvoice\ForceDeletePurchaseInvoice::class)
                        <x-button
                            color="red"
                            x-cloak
                            x-show="$wire.purchaseInvoiceForm.id && ! $wire.purchaseInvoiceForm.order_id"
                            :text="__('Delete')"
                            loading="delete"
                            wire:click="delete().then((success) => { if (success) $modalClose('edit-purchase-invoice-modal'); })"
                            wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Purchase Invoice')]) }}"
                        />
                    @endcanAction

                    @show
                </div>
                <div class="flex gap-2">
                    @section('footer-buttons.right')
                    <x-button
                        color="secondary"
                        light
                        :text="__('Cancel')"
                        x-on:click="$modalClose('edit-purchase-invoice-modal')"
                    />
                    <x-button
                        color="indigo"
                        x-cloak
                        x-show="! $wire.purchaseInvoiceForm.order_id"
                        :text="__('Save')"
                        loading="save"
                        wire:click="save().then((success) => { if (success) $modalClose('edit-purchase-invoice-modal'); })"
                    />
                    @canAction(\FluxErp\Actions\PurchaseInvoice\CreateOrderFromPurchaseInvoice::class)
                        <x-button
                            color="indigo"
                            x-cloak
                            x-show="$wire.purchaseInvoiceForm.id && ! $wire.purchaseInvoiceForm.order_id"
                            :text="__('Finish')"
                            loading="finish"
                            wire:click="finish().then((success) => { if (success) $modalClose('edit-purchase-invoice-modal'); })"
                        />
                    @endcanAction

                    @show
                </div>
            </div>
            @show
        </x-slot>
    </x-modal>

    <x-modal
        id="bulk-pdf-upload-modal"
        lg
        scrollable
        :title="__('Bulk PDF Upload')"
    >
        <div class="space-y-4">
            <div class="text-sm text-gray-600">
                {{ __('Select multiple PDF files to upload as purchase invoices. Each PDF will create a separate purchase invoice.') }}
            </div>

            <div
                wire:ignore
                x-data="{
                    ...filePond(
                        $wire,
                        $refs.upload,
                        '{{ Auth::user()?->language?->language_code }}',
                        {
                            title: '{{ __('File will be replaced') }}',
                            description: '{{ __('Do you want to proceed?') }}',
                            labelAccept: '{{ __('Accept') }}',
                            labelReject: '{{ __('Undo') }}',
                        },
                        {
                            uploadDisabled: '{{ __('Upload not allowed - Read Only') }}',
                        },
                    ),
                }"
            >
                <div x-ref="upload">
                    @canAction(\FluxErp\Actions\Media\UploadMedia::class)
                        <div class="flex flex-col items-end">
                            <div class="mb-4 w-full">
                                <input
                                    x-init="loadFilePond(() => 0)"
                                    id="filepond-drop"
                                    type="file"
                                    multiple
                                    accept="application/pdf"
                                />
                            </div>
                            <x-button
                                :text="__('Upload PDFs')"
                                x-cloak
                                x-show="tempFilesId.length !== 0 && isLoadingFiles.length === 0"
                                x-bind:disabled="isLoadingFiles.length > 0"
                                loading="processBulkUpload"
                                wire:click="processBulkUpload(tempFilesId).then((success) => { if(success) { clearPond(); $modalClose('bulk-pdf-upload-modal'); } })"
                            />
                        </div>
                    @endcanAction
                </div>
            </div>
        </div>

        <x-slot:footer>
            <x-button
                color="secondary"
                light
                :text="__('Cancel')"
                x-on:click="$modalClose('bulk-pdf-upload-modal');"
            />
        </x-slot>
    </x-modal>
</div>
