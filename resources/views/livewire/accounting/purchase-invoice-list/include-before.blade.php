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
            x-data="{
                showPayment: false,
                showBank: false,
            }"
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

            <div class="flex flex-col gap-4 overflow-auto px-2">
                @section('tenant')
                @if (count($tenants ?? []) > 1)
                    <div
                        x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                    >
                        <x-select.styled
                            x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                            wire:model="purchaseInvoiceForm.tenant_id"
                            :label="__('Tenant')"
                            select="label:name|value:id"
                            :options="$tenants"
                        />
                    </div>
                @endif

                @show

                @section('basic-info')
                <div
                    class="flex items-end gap-2"
                    x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                >
                    <div class="flex-1">
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
                                    'fields' => ['name', 'contact_id', 'firstname', 'lastname', 'company', 'contact'],
                                    'where' => [['is_main_address', '=', true]],
                                    'with' => ['contact.media'],
                                ],
                            ]"
                        />
                    </div>
                    @canAction(\FluxErp\Actions\Contact\CreateContact::class)
                        <x-button
                            x-cloak
                            x-show="! $wire.purchaseInvoiceForm.order_id"
                            color="emerald"
                            icon="plus"
                            wire:click="showCreateContactModal"
                        />
                    @endcanAction
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div
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
                        x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                    >
                        <x-select.styled
                            x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                            wire:model="purchaseInvoiceForm.payment_type_id"
                            :label="__('Payment Type')"
                            select="label:name|value:id"
                            :options="$paymentTypes"
                            x-on:select="if ($event.detail.select?.requires_manual_transfer && ! $wire.purchaseInvoiceForm.iban) showBank = true"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <x-input
                        x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                        wire:model="purchaseInvoiceForm.invoice_number"
                        :label="__('Invoice Number')"
                    />
                    <div
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

                <div class="grid grid-cols-2 gap-4">
                    <div
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

                @section('payment-settings')
                <div class="border-t border-gray-200 pt-2 dark:border-gray-700">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300"
                        x-on:click="showPayment = !showPayment"
                    >
                        <span>{{ __('Payment Details') }}</span>
                        <x-icon
                            name="chevron-down"
                            class="h-4 w-4 transition-transform"
                            x-bind:class="showPayment && 'rotate-180'"
                        />
                    </button>
                    <div x-cloak x-show="showPayment" x-collapse>
                        <div class="grid grid-cols-2 gap-4 pb-2 pt-2">
                            @if (count($currencies ?? []) > 1)
                                <div
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
                                        'params' => ['with' => 'media'],
                                    ]"
                                />
                            </div>
                            <div
                                x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                            >
                                <x-date
                                    x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                    wire:model="purchaseInvoiceForm.payment_target_date"
                                    :label="__('Payment Target Date')"
                                />
                            </div>
                            <div
                                x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                            >
                                <x-date
                                    x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                    wire:model="purchaseInvoiceForm.payment_discount_target_date"
                                    :label="__('Discount Target')"
                                />
                            </div>
                            <div
                                x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
                            >
                                <x-number
                                    x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                    wire:model="purchaseInvoiceForm.payment_discount_percent"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    :label="__('Discount %')"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                @show

                @section('bank-data')
                <div class="border-t border-gray-200 dark:border-gray-700">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300"
                        x-on:click="showBank = !showBank"
                    >
                        <span>{{ __('Bank Data') }}</span>
                        <x-icon
                            name="chevron-down"
                            class="h-4 w-4 transition-transform"
                            x-bind:class="showBank && 'rotate-180'"
                        />
                    </button>
                    <div x-cloak x-show="showBank" x-collapse>
                        <div class="grid grid-cols-2 gap-4 pb-2 pt-2">
                            <div class="col-span-2">
                                <x-select.styled
                                    :label="__('Lay out user')"
                                    autocomplete="off"
                                    x-on:select="
                                        $wire.purchaseInvoiceForm.iban = $event.detail.select?.iban;
                                        $wire.purchaseInvoiceForm.bic = $event.detail.select?.bic;
                                        $wire.purchaseInvoiceForm.bank_name = $event.detail.select?.bank_name;
                                        $wire.purchaseInvoiceForm.account_holder = $event.detail.select?.account_holder
                                    "
                                    wire:model="purchaseInvoiceForm.lay_out_user_id"
                                    select="label:label|value:id"
                                    unfiltered
                                    :request="[
                                        'url' => route('search', \FluxErp\Models\User::class),
                                        'method' => 'POST',
                                        'params' => [
                                            'with' => 'media',
                                            'fields' => ['id', 'name', 'email', 'iban', 'bic', 'bank_name', 'account_holder'],
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
                    </div>
                </div>
                @show

                @section('purchase-invoice-positions')
                <div
                    class="border-t border-gray-200 pt-4 dark:border-gray-700"
                    x-data="{
                        vatRates: @js($vatRates),
                        currencies: @js($currencies),
                        get positionsSum() {
                            const positions =
                                $wire.purchaseInvoiceForm.purchase_invoice_positions || []
                            const isNet = $wire.purchaseInvoiceForm.is_net

                            return positions.reduce((sum, pos) => {
                                let total = parseFloat(pos.total_price) || 0

                                if (isNet) {
                                    const vatRate = this.vatRates.find(
                                        (v) => v.id === pos.vat_rate_id,
                                    )
                                    const rate = vatRate ? parseFloat(vatRate.rate_percentage) : 0
                                    total = total * (1 + rate)
                                }

                                return sum + total
                            }, 0)
                        },
                        get currencyIso() {
                            const currency = this.currencies.find(
                                (c) => c.id === $wire.purchaseInvoiceForm.currency_id,
                            )
                            return currency ? currency.iso : 'EUR'
                        },
                        recalculatePrices(position, $event) {
                            const attribute = $event.target.getAttribute('x-model.number')
                            if (attribute === 'position.total_price') {
                                position.unit_price = position.total_price / position.amount
                            } else if (attribute === 'position.unit_price') {
                                position.total_price = position.amount * position.unit_price
                            } else if (attribute === 'position.amount') {
                                position.total_price = position.amount * position.unit_price
                            }
                        },
                    }"
                >
                    <div class="mb-4 flex flex-col gap-4">
                        <x-number
                            x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                            wire:model="purchaseInvoiceForm.total_gross_price"
                            step="0.01"
                            min="0"
                            :label="__('Total Gross Price')"
                        />
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                {{ __('Positions Sum') }}
                            </label>
                            <div
                                class="mt-1 text-lg font-medium"
                                x-bind:class="
                                    Math.abs(positionsSum - ($wire.purchaseInvoiceForm.total_gross_price || 0)) >
                                    0.01
                                        ? 'text-red-500'
                                        : 'text-emerald-500'
                                "
                                x-text="
                                    positionsSum.toLocaleString('de-DE', {
                                        style: 'currency',
                                        currency: currencyIso,
                                    })
                                "
                            ></div>
                        </div>
                        <x-toggle
                            x-bind:disabled="$wire.purchaseInvoiceForm.order_id"
                            wire:model="purchaseInvoiceForm.is_net"
                            :label="__('Position input net')"
                        />
                    </div>

                    <div class="space-y-3">
                        <template
                            x-for="(position, index) in $wire.purchaseInvoiceForm.purchase_invoice_positions"
                        >
                            <div
                                class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800"
                            >
                                <div
                                    class="mb-3 flex items-center justify-between border-b border-gray-200 pb-2 dark:border-gray-600"
                                >
                                    <span
                                        class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        {{ __('Position') }}
                                        <span x-text="index + 1"></span>
                                    </span>
                                    @canAction(\FluxErp\Actions\PurchaseInvoicePosition\DeletePurchaseInvoicePosition::class)
                                        <x-button
                                            x-cloak
                                            x-show="! $wire.purchaseInvoiceForm.order_id"
                                            color="red"
                                            icon="trash"
                                            :text="__('Delete')"
                                            flat
                                            sm
                                            x-on:click="$wire.purchaseInvoiceForm.purchase_invoice_positions.splice(index, 1)"
                                        />
                                    @endcanAction
                                </div>

                                <div
                                    class="mb-3 grid grid-cols-1 gap-4 lg:grid-cols-2"
                                >
                                    <x-input
                                        x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                        x-model="position.name"
                                        :label="__('Name')"
                                    />
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
                                                    'fields' => ['id', 'name', 'product_number'],
                                                    'with' => 'media',
                                                ],
                                            ]"
                                        />
                                    </div>
                                </div>

                                <div
                                    class="mb-3 grid grid-cols-1 gap-4 lg:grid-cols-4"
                                >
                                    <x-number
                                        step="0.01"
                                        x-bind:readonly="$wire.purchaseInvoiceForm.order_id"
                                        x-on:keyup="recalculatePrices(position, $event)"
                                        x-model.number="position.amount"
                                        :label="__('Amount')"
                                    />
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
                                        :label="__('Total')"
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
                                </div>

                                <div
                                    x-bind:class="$wire.purchaseInvoiceForm.order_id && 'pointer-events-none'"
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
                                                        \FluxErp\Enums\LedgerAccountTypeEnum::Expense
                                                    ],
                                                ],
                                            ],
                                        ]"
                                    />
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="flex justify-center pt-4">
                        @canAction(\FluxErp\Actions\PurchaseInvoicePosition\CreatePurchaseInvoicePosition::class)
                            <x-button
                                x-cloak
                                x-show="! $wire.purchaseInvoiceForm.order_id"
                                color="emerald"
                                icon="plus"
                                :text="__('Add Position')"
                                x-on:click="$wire.purchaseInvoiceForm.purchase_invoice_positions.push({
                                    ledger_account_id: $wire.purchaseInvoiceForm.lastLedgerAccountId,
                                    vat_rate_id: null,
                                    product_id: null,
                                    name: null,
                                    amount: 1,
                                    unit_price: 0,
                                    total_price: 0
                                })"
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

    {!! $createContactForm->autoRender($__data) !!}
</div>
