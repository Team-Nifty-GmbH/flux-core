<div
    x-data="{
        init() {
            document.body.dataset.currencyCode = $wire.order.currency.iso;
        },
        showOrderInformations: false,
        showAdditionalAddresses: false,
        orderPositions: [],
        formatter: @js(resolve_static(\FluxErp\Models\Order::class, 'typeScriptAttributes')),
    }"
>
    @section('modals')
        {{ $this->renderCreateDocumentsModal() }}
        @canAction(\FluxErp\Actions\Task\CreateTask::class)
            <x-modal id="create-tasks">
                <livewire:order.order-project lazy :order="$order->id" />
            </x-modal>
        @endCanAction
        <x-modal id="replicate-order">
            <section x-data="{
                updateContactId(id) {
                    $tallstackuiSelect('invoice-address-id')
                        .mergeRequestParams({
                            where: [
                                ['contact_id', '=', id]
                            ]}
                        );
                    $tallstackuiSelect('delivery-address-id')
                        .mergeRequestParams({
                            where: [
                                ['contact_id', '=', id]
                            ]}
                        );
                    $wire.fetchContactData(true);
                }
            }
            ">
                <div class="space-y-2.5 divide-y divide-secondary-200">
                    <x-select.styled
                        select="label:name|value:id"
                        :label="__('Order type')"
                        wire:model="replicateOrder.order_type_id"
                        required
                        :request="[
                            'url' => route('search', \FluxErp\Models\OrderType::class),
                            'method' => 'POST',
                            'params' => [
                                'searchFields' => [
                                    'name',
                                ],
                                'select' => [
                                    'name',
                                    'id',
                                ],
                                'where' => [
                                    [
                                        'is_active',
                                        '=',
                                        true,
                                    ],
                                    [
                                        'is_hidden',
                                        '=',
                                        false,
                                    ],
                                ],
                            ],
                        ]"
                    />
                    <div class="pt-4">
                        <x-select.styled
                            :label="__('Contact')"
                            class="pb-4"
                            wire:model="replicateOrder.contact_id"
                            select="label:label|value:contact_id"
                            option-description="description"
                            required
                            x-on:select="updateContactId($event.detail.select.contact_id)"
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
                                    ],
                                    'where' => [
                                        [
                                            'is_main_address',
                                            '=',
                                            true,
                                        ]
                                    ],
                                    'with' => 'contact.media',
                                ],
                            ]"
                        />
                        <div id="invoice-address-id">
                            <x-select.styled
                                class="pb-4"
                                :label="__('Invoice Address')"
                                wire:model="replicateOrder.address_invoice_id"
                                option-description="description"
                                required
                                :request="[
                                    'url' => route('search', \FluxErp\Models\Address::class),
                                    'method' => 'POST',
                                    'params' => [
                                        'with' => 'contact.media',
                                        'where' => [
                                            [
                                                'contact_id',
                                                '=',
                                                $order->contact_id,
                                            ],
                                        ],
                                    ],
                                ]"
                            />
                        </div>
                        <div id="delivery-address-id">
                            <x-select.styled
                                :label="__('Delivery Address')"
                                class="pb-4"
                                wire:model="replicateOrder.address_delivery_id"
                                option-description="description"
                                required
                                :request="[
                                    'url' => route('search', \FluxErp\Models\Address::class),
                                    'method' => 'POST',
                                    'params' => [
                                        'with' => 'contact.media',
                                        'where' => [
                                            [
                                                'contact_id',
                                                '=',
                                                $order->contact_id,
                                            ],
                                        ],
                                    ],
                                ]"
                            />
                        </div>
                    </div>
                    <div class="space-y-3 pt-4">
                        <x-select.styled
                            :label="__('Client')"
                            :options="$clients"
                            select="label:name|value:id"
                            required
                            autocomplete="off"
                            wire:model="replicateOrder.client_id"
                        />
                        <x-select.styled
                            :label="__('Price list')"
                            :options="$priceLists"
                            select="label:name|value:id"
                            required
                            autocomplete="off"
                            wire:model="replicateOrder.price_list_id"
                        />
                        <x-select.styled
                            :label="__('Payment method')"
                            :options="$paymentTypes"
                            select="label:name|value:id"
                            required
                            autocomplete="off"
                            wire:model="replicateOrder.payment_type_id"
                        />
                        <x-select.styled
                            :label="__('Language')"
                            :options="$languages"
                            select="label:name|value:id"
                            required
                            autocomplete="off"
                            wire:model="replicateOrder.language_id"
                        />
                    </div>
                </div>
            </section>
            <x-errors />
            <x-slot:footer>
                <div class="flex justify-end gap-x-4">
                    <div class="flex">
                        <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('replicate-order')" />
                        <x-button loading="saveReplicate" color="indigo" :text="__('Save')" wire:click="saveReplicate()" />
                    </div>
                </div>
            </x-slot:footer>
        </x-modal>
        <x-modal id="create-child-order" size="7xl">
            <div class="grid grid-cols-2 gap-1.5">
                <div id="replicate-order-order-type">
                    <x-select.styled
                        :label="__('Order Type')"
                        wire:model="replicateOrder.order_type_id"
                        select="label:name|value:id"
                        required
                        :request="[
                            'url' => route('search', \FluxErp\Models\OrderType::class),
                            'method' => 'POST',
                            'params' => [
                                'searchFields' => [
                                    'name',
                                ],
                                'select' => [
                                    'name',
                                    'id',
                                ],
                                'where' => [
                                    [
                                        'is_active',
                                        '=',
                                        true,
                                    ],
                                ],
                                'whereIn' => [
                                    [
                                        'id',
                                        '',
                                    ],
                                ],
                            ],
                        ]"
                    />
                </div>
                <div class="overflow-auto">
                    <template x-for="(position, index) in $wire.replicateOrder.order_positions">
                        <x-flux::list-item :item="[]">
                            <x-slot:value>
                                <span x-text="position.name"></span>
                            </x-slot:value>
                            <x-slot:sub-value>
                                <div class="flex flex-col">
                                    <span x-html="position.description"></span>
                                </div>
                            </x-slot:sub-value>
                            <x-slot:actions>
                                <x-number x-model.number="position.amount" min="0" />
                                <x-button
                                    color="red"
                                    icon="trash"
                                    x-on:click="$wire.replicateOrder.order_positions.splice(index, 1); $wire.recalculateReplicateOrderPositions();"
                                />
                            </x-slot:actions>
                        </x-flux::list-item>
                    </template>
                </div>
            </div>
            <div class="pt-4">
                <livewire:order.replicate-order-position-list :order-id="$order->id" lazy />
            </div>
            <x-slot:footer>
                <div class="flex justify-end gap-1.5">
                    <x-button color="secondary" light :text="__('Cancel')" x-on:click="$modalClose('create-child-order')"/>
                    <x-button
                        x-cloak
                        x-show="$wire.replicateOrder.order_positions?.length"
                        color="indigo"
                        :text="__('Save')"
                        wire:click="saveReplicate()"
                    />
                </div>
            </x-slot:footer>
        </x-modal>
        <x-modal id="edit-discount" x-on:open="$focus.first()" x-trap="show" x-on:keyup.enter="$wire.saveDiscount().then((success) => {if(success) $modalClose('edit-discount');})">
            <div class="flex flex-col gap-4">
                <x-input wire:model="discount.name" :text="__('Name')" />
                <div x-cloak x-show="$wire.discount.is_percentage">
                    <x-input
                        prefix="%"
                        type="number"
                        x-on:focus=""
                        :label="__('Discount')"
                        wire:model="discount.discount"
                        x-on:change="$el.value = parseNumber($el.value)"
                    />
                </div>
                <div x-cloak x-show="! $wire.discount.is_percentage">
                    <x-input
                        :prefix="data_get($order, 'currency.symbol')"
                        type="number"
                        :label="__('Discount')"
                        wire:model="discount.discount"
                        x-on:change="$el.value = parseNumber($el.value)"
                    />
                </div>
                <x-toggle wire:model="discount.is_percentage" :label="__('Is Percentage')" />
            </div>
            <x-slot:footer>
                <div class="flex justify-end gap-1.5">
                    <x-button color="secondary" light :text="__('Cancel')" x-on:click="$modalClose('edit-discount')"/>
                    <x-button
                        color="indigo"
                        :text="__('Save')"
                        wire:click="saveDiscount().then((success) => {if(success) $modalClose('edit-discount');})"
                    />
                </div>
            </x-slot:footer>
        </x-modal>
    @show
    <div
        class="mx-auto md:flex md:items-center md:justify-between md:space-x-5">
        <div class="flex items-center gap-5">
            <x-avatar xl :image="data_get($order, 'avatarUrl', '')" />
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    <div class="flex gap-1.5">
                        <div @canAction(\FluxErp\Actions\Order\ToggleLock::class) wire:click="toggleLock()" class="cursor-pointer" wire:flux-confirm.icon.warning="{{  __('Change order lock state') }}|{{ __('Manually locking or unlocking orders can have unexpected side effects.<br><br>Are you Sure?') }}|{{ __('Cancel') }}|{{ __('Continue') }}" @endCanAction>
                            <x-icon x-cloak x-show="$wire.order.is_locked" variant="solid" name="lock-closed" />
                            <x-icon x-cloak x-show="! $wire.order.is_locked" variant="solid" name="lock-open" />
                        </div>
                        <div>
                            <div>
                                <span class="opacity-40 transition-opacity hover:opacity-100" x-text="$wire.order.order_type.name">
                                </span>
                                <span class="opacity-40 transition-opacity hover:opacity-100" x-text="$wire.order.invoice_number ? $wire.order.invoice_number : ($wire.order.order_number || $wire.order.id)">
                                </span>
                            </div>
                        </div>
                        @if($order->payment_reminder_current_level)
                            @switch($order->payment_reminder_current_level)
                                @case(1)
                                    <x-badge
                                        :text="__('Reminder Level :level', ['level' => $order->payment_reminder_current_level])"
                                        warning
                                        rounded
                                    />
                                    @break
                                @case(2)
                                    <x-badge
                                        :text="__('Reminder Level :level', ['level' => $order->payment_reminder_current_level])"
                                        orange
                                        rounded
                                    />
                                    @break
                                @default
                                    <x-badge
                                        :text="__('Reminder Level :level', ['level' => $order->payment_reminder_current_level])"
                                        negative
                                        rounded
                                    />
                            @endswitch
                        @endif
                        @if($order->hasContactDeliveryLock)
                            <x-badge
                                :text="__('Has Delivery Lock')"
                                color="red"
                                rounded
                            />
                        @endif
                    </div>
                </h1>
                <a wire:navigate
                   class="flex gap-1.5 font-semibold opacity-40 dark:text-gray-200"
                   x-bind:href="($wire.order.parent?.url ?? $wire.order.created_from?.url) || ''"
                   x-show="$wire.order.parent?.url || $wire.order.created_from?.url"
                >
                    <i class="size-4 ph ph-copy"
                       x-bind:class="$wire.order.parent?.url ? 'ph-link' : 'ph-copy'">
                    </i>
                    <span x-text="$wire.order.parent?.label ?? $wire.order.created_from?.label"></span>
                </a>
            </div>
        </div>
        <div class="justify-stretch mt-6 flex flex-col-reverse space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3">
            @if(resolve_static(\FluxErp\Actions\Order\ReplicateOrder::class, 'canPerformAction', [false]))
                <x-button color="secondary" light
                    loading="replicate"
                    class="w-full"
                    wire:click="replicate()"
                    :text="__('Replicate')"
                />
            @endif
            @if(resolve_static(\FluxErp\Actions\Order\DeleteOrder::class, 'canPerformAction', [false]) && ! $order->is_locked)
                <x-button color="secondary" light
                    wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Order')]) }}"
                    color="red"
                    :text="__('Delete')"
                    wire:click="delete"
                />
            @endif
            @if((resolve_static(\FluxErp\Actions\Order\UpdateOrder::class, 'canPerformAction', [false]) && ! $order->is_locked) || resolve_static(\FluxErp\Actions\Order\UpdateLockedOrder::class, 'canPerformAction', [false]))
                <x-button
                    color="indigo"
                    loading="save"
                    class="w-full"
                    x-on:click="$wire.save(orderPositions)"
                    :text="__('Save')"
                />
            @endif
        </div>
    </div>
    <x-flux::tabs wire:loading="tab" wire:model="tab" :tabs="$tabs" class="w-full lg:col-start-1 xl:col-span-2 xl:flex gap-4">
        <x-slot:prepend>
            <section class="relative basis-2/12" wire:ignore>
                <div class="sticky top-6 flex flex-col gap-4">
                    @section('contact-address-card')
                        <x-card :title="__('Contact')">
                            <x-slot:header>
                                <x-button color="secondary"
                                    light
                                    wire:navigate
                                    icon="eye"
                                    :href="route('contacts.id?', data_get($order, 'contact_id', ''))"
                                />
                            </x-slot:header>
                            <div x-data="{
                                    updateContactId(id) {
                                        $tallstackuiSelect('invoice-address-id')
                                            .mergeRequestParams({
                                                where: [
                                                    ['contact_id', '=', id]
                                                ]}
                                            );
                                        $tallstackuiSelect('delivery-address-id')
                                            .mergeRequestParams({
                                                where: [
                                                    ['contact_id', '=', id]
                                                ]}
                                            );

                                        $wire.fetchContactData();
                                    }
                                }"
                            >
                                <x-select.styled
                                    class="pb-4"
                                    :label="__('Contact')"
                                    :disabled="$order->is_locked"
                                    wire:model="order.contact_id"
                                    select="label:label|value:contact_id"
                                    option-description="description"
                                    required
                                    x-on:selected="updateContactId($event.detail.select.value)"
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
                                            ],
                                            'where' => [
                                                [
                                                    'is_main_address',
                                                    '=',
                                                    true,
                                                ]
                                            ],
                                            'with' => 'contact.media',
                                        ],
                                    ]"
                                />
                            </div>
                        </x-card>
                    @show
                    @section('invoice-address-card')
                        <x-card :title="__('Invoice Address')">
                            <x-slot:header>
                                <x-button color="secondary" light
                                    wire:navigate
                                    outline
                                    icon="eye"
                                    :href="route('address.id', data_get($order, 'address_invoice_id', ''))"
                                >
                                </x-button>
                            </x-slot:header>
                            <div id="order-invoice-address-id">
                                <x-select.styled
                                    :disabled="$order->is_locked"
                                    class="pb-4"
                                    wire:model.live="order.address_invoice_id"
                                    option-description="description"
                                    required
                                    :request="[
                                        'url' => route('search', \FluxErp\Models\Address::class),
                                        'method' => 'POST',
                                        'params' => [
                                            'with' => 'contact.media',
                                            'where' => [
                                                [
                                                    'contact_id',
                                                    '=',
                                                    $order->contact_id,
                                                ],
                                            ],
                                        ],
                                    ]"
                                />
                            </div>
                            <div class="text-sm">
                                <div x-text="$wire.order.address_invoice.company">
                                </div>
                                <div x-text="$wire.order.address_invoice.addition">
                                </div>
                                <div x-text="(($wire.order.address_invoice?.firstname || '').trim() + ' ' + ($wire.order.address_invoice?.lastname || '').trim()).trim()">
                                </div>
                                <div x-text="$wire.order.address_invoice.street">
                                </div>
                                <div x-text="(($wire.order.address_invoice?.zip || '').trim() + ' ' + ($wire.order.address_invoice?.city || '').trim()).trim()">
                                </div>
                            </div>
                        </x-card>
                    @show
                    @section('delivery-address-card')
                        <x-card :title="__('Delivery Address')">
                            <x-slot:header>
                                <x-button color="secondary" light
                                    outline
                                    icon="eye"
                                    :href="route('address.id', data_get($order, 'address_delivery_id', ''))"
                                />
                            </x-slot:header>
                            <div id="order-delivery-address-id">
                                <x-select.styled
                                    :disabled="$order->is_locked"
                                    class="pb-4"
                                    wire:model.live="order.address_delivery_id"
                                    option-description="description"
                                    required
                                    :request="[
                                        'url' => route('search', \FluxErp\Models\Address::class),
                                        'method' => 'POST',
                                        'params' => [
                                            'with' => 'contact.media',
                                            'where' => [
                                                [
                                                    'contact_id',
                                                    '=',
                                                    $order->contact_id,
                                                ],
                                            ],
                                        ],
                                    ]"
                                />
                            </div>
                            <div class="text-sm" x-bind:class="$wire.order.address_delivery_id === $wire.order.address_invoice_id && 'hidden'">
                                <div x-text="$wire.order.address_delivery?.company">
                                </div>
                                <div x-text="$wire.order.address_delivery?.addition">
                                </div>
                                <div x-text="(($wire.order.address_delivery?.firstname || '').trim() + ' ' + ($wire.order.address_delivery?.lastname || '').trim()).trim()">
                                </div>
                                <div x-text="$wire.order.address_delivery?.street">
                                </div>
                                <div x-text="(($wire.order.address_invoice?.zip || '').trim() + ' ' + ($wire.order.address_invoice?.city || '').trim()).trim()">
                                </div>
                            </div>
                        </x-card>
                    @show
                    @section('general-card')
                        <x-card :title="__('Additional Addresses')" class="!px-0 !py-0">
                            <x-slot:header>
                                <x-button.circle color="secondary"
                                    light
                                    class="transition-transform"
                                    x-bind:class="showAdditionalAddresses && '-rotate-90'"
                                    icon="chevron-left"
                                    x-on:click="showAdditionalAddresses = !showAdditionalAddresses"
                                />
                            </x-slot:header>
                            <div class="space-y-3 px-2 py-5" x-collapse x-cloak x-show="showAdditionalAddresses">
                                <livewire:order.additional-addresses lazy :order-id="$order->id" :client-id="$order->client_id"/>
                            </div>
                        </x-card>
                        <x-card :title="__('Order Informations')" class="!px-0 !py-0">
                            <x-slot:header>
                                <x-button.circle
                                    color="secondary"
                                    light
                                    class="transition-transform"
                                    x-bind:class="showOrderInformations && '-rotate-90'"
                                    icon="chevron-left"
                                    x-on:click="showOrderInformations = !showOrderInformations"
                                />
                            </x-slot:header>
                            <div class="space-y-3 px-2 py-5" x-collapse x-cloak x-show="showOrderInformations">
                                @if(count($clients) > 1)
                                    <x-select.styled
                                        disabled
                                        :label="__('Client')"
                                        :options="$clients"
                                        select="label:name|value:id"
                                        required
                                        autocomplete="off"
                                        wire:model.live="order.client_id"
                                    />
                                @endif
                                <x-select.styled
                                    :label="__('Commission Agent')"
                                    :disabled="$order->is_locked"
                                    autocomplete="off"
                                    wire:model="order.agent_id"
                                    :request="[
                                        'url' => route('search', \FluxErp\Models\User::class),
                                        'method' => 'POST',
                                        'params' => [
                                            'with' => 'media',
                                        ],
                                    ]"
                                />
                                <x-select.styled
                                    :label="__('Responsible User')"
                                    autocomplete="off"
                                    wire:model="order.responsible_user_id"
                                    :request="[
                                        'url' => route('search', \FluxErp\Models\User::class),
                                        'method' => 'POST',
                                        'params' => [
                                            'with' => 'media',
                                        ],
                                    ]"
                                />
                                <x-select.styled
                                    :label="__('Assigned')"
                                    autocomplete="off"
                                    multiple
                                    wire:model="order.users"
                                    :request="[
                                        'url' => route('search', \FluxErp\Models\User::class),
                                        'method' => 'POST',
                                        'params' => [
                                            'with' => 'media',
                                        ],
                                    ]"
                                />
                                <x-select.styled
                                    :label="__('Price list')"
                                    :options="$priceLists"
                                    select="label:name|value:id"
                                    required
                                    autocomplete="off"
                                    wire:model.live="order.price_list_id"
                                    x-bind:disabled="$wire.order.is_locked"
                                    :disabled="$order->is_locked"
                                />
                                <x-select.styled
                                    :label="__('Tax Exemption')"
                                    :options="$vatRates"
                                    select="label:name|value:id"
                                    autocomplete="off"
                                    wire:model="order.vat_rate_id"
                                    x-bind:disabled="$wire.order.is_locked"
                                    :disabled="$order->is_locked"
                                />
                                <x-select.styled
                                    :label="__('Payment method')"
                                    :options="$paymentTypes"
                                    select="label:name|value:id"
                                    required
                                    autocomplete="off"
                                    wire:model="order.payment_type_id"
                                />
                                @if($contactBankConnections)
                                    <x-select.styled
                                        wire:model="order.contact_bank_connection_id"
                                        :label="__('Bank connection')"
                                        :options="$contactBankConnections"
                                        option-key-value
                                    />
                                @endif
                                @if(count($languages) > 1)
                                    <x-select.styled
                                        :label="__('Language')"
                                        :options="$languages"
                                        select="label:name|value:id"
                                        required
                                        autocomplete="off"
                                        wire:model="order.language_id"
                                        x-bind:disabled="$wire.order.is_locked"
                                        :disabled="$order->is_locked"
                                    />
                                @endif
                            </div>
                        </x-card>
                    @show
                    @section('state-card')
                        <x-card>
                            <div class="flex flex-col gap-3">
                                <x-flux::state
                                    class="w-full"
                                    align="bottom-start"
                                    :label="__('Order state')"
                                    wire:model="order.state"
                                    formatters="formatter.state"
                                    available="availableStates.state"
                                />
                                <x-flux::state
                                    align="bottom-start"
                                    :label="__('Payment state')"
                                    wire:model="order.payment_state"
                                    formatters="formatter.payment_state"
                                    available="availableStates.payment_state"
                                />
                                <x-flux::state
                                    align="bottom-start"
                                    :label="__('Delivery state')"
                                    wire:model="order.delivery_state"
                                    formatters="formatter.delivery_state"
                                    available="availableStates.delivery_state"
                                />
                            </div>
                        </x-card>
                    @show
                </div>
            </section>
        </x-slot:prepend>
        <x-slot:append>
            <section class="relative basis-2/12" wire:ignore>
                <div class="sticky top-6 space-y-6">
                    @section('content.right')
                        <x-card>
                            <div class="space-y-4">
                                @section('actions')
                                    @if($printLayouts)
                                        <x-button
                                            color="indigo"
                                            class="w-full"
                                            icon="document-text"
                                            wire:click="openCreateDocumentsModal()"
                                            :text="__('Create Documents')"
                                        />
                                        <div class="dropdown-full-w">
                                            <x-dropdown>
                                                <x-slot:action>
                                                    <x-button color="secondary" light class="w-full" icon="magnifying-glass" x-on:click="show = !show" :text="__('Preview')"/>
                                                </x-slot:action>
                                                <template x-for="printLayout in $wire.printLayouts">
                                                    <x-dropdown.items
                                                        x-on:click="$wire.openPreview(printLayout.layout, '{{ morph_alias(\FluxErp\Models\Order::class) }}', $wire.order.id).then(() => show = false)">
                                                        <span x-text="printLayout.label"></span>
                                                    </x-dropdown.items>
                                                </template>
                                            </x-dropdown>
                                        </div>
                                        <livewire:features.signature-link-generator lazy :model-type="\FluxErp\Models\Order::class" wire:model="order.id"/>
                                    @endif
                                    @foreach($additionalModelActions as $modelAction)
                                        {{$modelAction}}
                                    @endforeach
                                @show
                            </div>
                        </x-card>
                        <x-card>
                            <div class="text-sm">
                                @section('content.right.summary')
                                    <div x-cloak x-show="$wire.order.total_net_price !== ($wire.order.total_base_net_price ?? '0.0000000000')">
                                        <div class="flex justify-between p-2.5">
                                            <div>
                                                {{ __('Sum net without discount') }}
                                            </div>
                                            <div>
                                                <span x-html="formatters.coloredMoney($wire.order.total_base_net_price ?? 0)">
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex justify-between p-2.5" x-cloak x-show="$wire.order.total_position_discount_percentage != 0">
                                            <div>
                                                <span>
                                                    {{ __('Position discounts') }}
                                                </span>
                                                <span x-html="formatters.percentage($wire.order.total_position_discount_percentage ?? 0)">
                                                </span>
                                            </div>
                                            <div>
                                                <span x-html="formatters.coloredMoney(($wire.order.total_position_discount_flat ?? 0) * -1)">
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex justify-between p-2.5" x-cloak x-show="$wire.order.total_position_discount_percentage != 0">
                                            <div>
                                                <span>
                                                    {{ __('Sum net discounted') }}
                                                </span>
                                            </div>
                                            <div>
                                                <span x-html="formatters.coloredMoney($wire.order.total_base_discounted_net_price ?? 0)">
                                                </span>
                                            </div>
                                        </div>
                                        <div x-sort="$wire.reOrderDiscount($item, $position)">
                                            <template x-for="discount in $wire.order.discounts" :key="`${discount.id}-${discount.order_column}`">
                                                <div class="flex justify-between p-2.5 items-center cursor-ns-resize" x-sort:item="discount.id">
                                                    <div class="flex gap-1.5 items-center">
                                                        @if (! $order->is_locked || ! resolve_static(\FluxErp\Actions\Discount\DeleteDiscount::class, 'canPerformAction', [false]))
                                                            <div>
                                                                <x-button.circle
                                                                    color="red"
                                                                    icon="x-mark"
                                                                    2xs
                                                                    wire:click="deleteDiscount(discount.id)"
                                                                    wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => 'Discount']) }}"
                                                                />
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <span x-html="discount.name"></span>
                                                            <span x-html="formatters.percentage(discount.discount_percentage ?? 0)"></span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <span x-html="formatters.coloredMoney((discount.discount_flat ?? 0) * -1)">
                                                        </span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    @if (! $order->is_locked || ! resolve_static(\FluxErp\Actions\Discount\CreateDiscount::class, 'canPerformAction', [false]))
                                        <div class="w-full">
                                            <x-button color="secondary" light class="w-full" wire:click="editDiscount()" :text="__('Add discount')" />
                                        </div>
                                    @endif
                                    <div class="flex justify-between p-2.5 opacity-50" x-cloak x-show="($wire.order.total_discount_percentage ?? 0) != 0">
                                        <div>
                                            <span>{{ __('Total discount') }}</span>
                                            <span x-html="formatters.percentage($wire.order.total_discount_percentage ?? 0)">
                                        </div>
                                        <div>
                                            <span x-html="formatters.coloredMoney(($wire.order.total_discount_flat ?? 0) * -1)">
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between p-2.5">
                                        <div>
                                            {{ __('Sum net') }}
                                        </div>
                                        <div>
                                            <span x-html="formatters.coloredMoney($wire.order.total_net_price ?? 0)">
                                            </span>
                                        </div>
                                    </div>
                                    <hr />
                                    <template x-for="vat in $wire.order.total_vats">
                                        <div class="flex justify-between p-2.5">
                                            <div>
                                                <span>{{ __('Plus ') }}</span>
                                                <span x-html="formatters.percentage(vat.vat_rate_percentage ?? 0)">
                                                </span>
                                            </div>
                                            <div>
                                                <span x-html="formatters.coloredMoney(vat.total_vat_price ?? 0)">
                                                </span>
                                            </div>
                                        </div>
                                    </template>
                                    <div class="dark:bg-secondary-700 flex justify-between bg-gray-50 p-2.5">
                                        <div>
                                            {{ __('Total Gross') }}
                                        </div>
                                        <div>
                                            <span x-html="formatters.coloredMoney($wire.order.total_gross_price ?? 0)">
                                            </span>
                                        </div>
                                    </div>
                                    <div class="dark:bg-secondary-700 flex justify-between bg-gray-50 p-2.5 opacity-50">
                                        <div>
                                            {{ __('Balance') }}
                                        </div>
                                        <div>
                                            <span x-html="formatters.coloredMoney($wire.order.balance ?? 0)">
                                            </span>
                                        </div>
                                    </div>
                                    @section('content.right.summary.profit')
                                        <hr />
                                        <div class="flex justify-between p-2.5">
                                            <div>
                                                {{ __('Margin') }}
                                            </div>
                                            <div>
                                                <span x-html="formatters.coloredMoney($wire.order.margin ?? 0)">
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex justify-between p-2.5">
                                            <div>
                                                {{ __('Gross Profit') }}
                                            </div>
                                            <div>
                                                <span x-html="formatters.coloredMoney($wire.order.gross_profit ?? 0)">
                                                </span>
                                            </div>
                                        </div>
                                    @show
                                @show
                            </div>
                        </x-card>
                        <x-card>
                            <div class="space-y-3">
                                @section('content.right.order_dates')
                                    <x-date wire:model="order.invoice_date" :without-time="true" :disabled="true" :text="__('Invoice Date')" />
                                    <x-date wire:model="order.system_delivery_date" :without-time="true" :disabled="$order->is_locked" :label="__('Performance/Delivery date')" />
                                    <x-date wire:model="order.system_delivery_date_end" :without-time="true" :disabled="$order->is_locked" :label="__('Performance/Delivery date end')" />
                                    <x-date wire:model="order.order_date" :without-time="true" :disabled="$order->is_locked" :label="__('Order Date')" />
                                    <x-input wire:model="order.commission" :label="__('Commission')" />
                                    <x-date
                                        wire:model="order.payment_reminder_next_date"
                                        :without-time="true"
                                        :label="__('Payment Reminder Next Date')"
                                    />
                                @show
                            </div>
                        </x-card>
                    @show
                    <x-card>
                        <div class="text-sm whitespace-nowrap overflow-hidden text-ellipsis">
                            <div class="flex gap-0.5">
                                <div class="">{{ __('Created At') }}:</div>
                                <div x-text="window.formatters.datetime($wire.order.created_at)"></div>
                                <div x-text="$wire.order.created_by || '{{ __('Unknown') }}'"></div>
                            </div>
                            <div class="flex gap-0.5">
                                <div class="">{{ __('Updated At') }}:</div>
                                <div x-text="window.formatters.datetime($wire.order.updated_at)"></div>
                                <div x-text="$wire.order.updated_by || '{{ __('Unknown') }}'"></div>
                            </div>
                        </div>
                    </x-card>
                </div>
            </section>
        </x-slot:append>
    </x-flux::tabs>
</div>
