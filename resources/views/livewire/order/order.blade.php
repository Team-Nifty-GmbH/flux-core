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
        <x-modal name="replicate-order">
            <x-card>
                <section x-data="{
                    updateContactId(id) {
                        Alpine.$data(
                            document.getElementById('invoice-address-id').querySelector('[x-data]')
                        ).asyncData.params.where[0][2] = id;
                        Alpine.$data(
                            document.getElementById('delivery-address-id').querySelector('[x-data]')
                        ).asyncData.params.where[0][2] = id;
                        $wire.fetchContactData(true);
                    }
                }
                ">
                    <div class="space-y-2.5 divide-y divide-secondary-200">
                        <x-select
                            :options="$orderTypes"
                            option-label="name"
                            option-value="id"
                            :label="__('Order type')"
                            wire:model="replicateOrder.order_type_id"
                        />
                        <div class="pt-4">
                            <x-select
                                :label="__('Contact')"
                                class="pb-4"
                                wire:model="replicateOrder.contact_id"
                                option-value="contact_id"
                                option-label="label"
                                option-description="description"
                                :clearable="false"
                                x-on:selected="updateContactId($event.detail.contact_id)"
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
                            <div id="invoice-address-id">
                                <x-select
                                    class="pb-4"
                                    :label="__('Invoice Address')"
                                    wire:model="replicateOrder.address_invoice_id"
                                    option-value="id"
                                    option-label="label"
                                    option-description="description"
                                    :clearable="false"
                                    :async-data="[
                                        'api' => route('search', \FluxErp\Models\Address::class),
                                        'method' => 'POST',
                                        'params' => [
                                            'with' => 'contact.media',
                                            'where' => [
                                                ['contact_id', '=', $order->contact_id],
                                            ],
                                        ]
                                    ]"
                                />
                            </div>
                            <div id="delivery-address-id">
                                <x-select
                                    :label="__('Delivery Address')"
                                    class="pb-4"
                                    wire:model="replicateOrder.address_delivery_id"
                                    option-value="id"
                                    option-label="label"
                                    option-description="description"
                                    :clearable="false"
                                    :async-data="[
                                        'api' => route('search', \FluxErp\Models\Address::class),
                                        'method' => 'POST',
                                        'params' => [
                                            'with' => 'contact.media',
                                            'where' => [
                                                ['contact_id', '=', $order->contact_id],
                                            ],
                                        ]
                                    ]"
                                />
                            </div>
                        </div>
                        <div class="space-y-3 pt-4">
                            <x-select
                                :label="__('Client')"
                                :options="$clients"
                                option-value="id"
                                option-label="name"
                                :clearable="false"
                                autocomplete="off"
                                wire:model="replicateOrder.client_id"
                            />
                            <x-select
                                :label="__('Price list')"
                                :options="$priceLists"
                                option-value="id"
                                option-label="name"
                                :clearable="false"
                                autocomplete="off"
                                wire:model="replicateOrder.price_list_id"
                            />
                            <x-select
                                :label="__('Payment method')"
                                :options="$paymentTypes"
                                option-value="id"
                                option-label="name"
                                :clearable="false"
                                autocomplete="off"
                                wire:model="replicateOrder.payment_type_id"
                            />
                            <x-select
                                :label="__('Language')"
                                :options="$languages"
                                option-value="id"
                                option-label="name"
                                :clearable="false"
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
                            <x-button flat :label="__('Cancel')" x-on:click="close" />
                            <x-button spinner="saveReplicate" primary :label="__('Save')" wire:click="saveReplicate()" />
                        </div>
                    </div>
                </x-slot:footer>
            </x-card>
        </x-modal>
        <x-modal name="create-child-order" max-width="7xl">
            <x-card>
                <div class="grid grid-cols-2 gap-1.5">
                    <div class="flex flex-col gap-1.5">
                        <x-select
                            :label="__('Order Type')"
                            wire:model="replicateOrder.order_type_id"
                            :options="$replicateOrderTypes"
                            option-value="id"
                            option-label="name"
                            :clearable="false"
                        />
                    </div>
                    <div class="overflow-auto">
                        <template x-for="(position, index) in $wire.replicateOrder.order_positions">
                            <x-list-item :item="[]">
                                <x-slot:value>
                                    <span x-text="position.name"></span>
                                </x-slot:value>
                                <x-slot:sub-value>
                                    <div class="flex flex-col">
                                        <span x-html="position.description"></span>
                                    </div>
                                </x-slot:sub-value>
                                <x-slot:actions>
                                    <x-inputs.number x-model.number="position.amount" min="0" />
                                    <x-button
                                        negative
                                        icon="trash"
                                        x-on:click="$wire.replicateOrder.order_positions.splice(index, 1); $wire.recalculateReplicateOrderPositions();"
                                    />
                                </x-slot:actions>
                            </x-list-item>
                        </template>
                    </div>
                </div>
                <div class="pt-4">
                    <livewire:order.replicate-order-position-list :id="$order->id" />
                </div>
                <x-slot:footer>
                    <div class="flex justify-end gap-1.5">
                        <x-button :label="__('Cancel')" x-on:click="close"/>
                        <x-button
                            x-cloak
                            x-show="$wire.replicateOrder.order_positions?.length"
                            primary
                            :label="__('Save')"
                            wire:click="saveReplicate()"
                        />
                    </div>
                </x-slot:footer>
            </x-card>
        </x-modal>
    @show
    <div
        class="mx-auto md:flex md:items-center md:justify-between md:space-x-5">
        <div class="flex items-center gap-5">
            <x-avatar xl :src="$order->contact['avatar_url'] ?? ''"></x-avatar>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    <div class="flex gap-1.5">
                        <div @canAction(\FluxErp\Actions\Order\ToggleLock::class) wire:click="toggleLock()" class="cursor-pointer" wire:flux-confirm.icon.warning="{{  __('Unlock Order') }}|{{ __('Unlocking orders can have unexpected side effects.<br><br>Are you Sure?') }}|{{ __('Cancel') }}|{{ __('Continue') }}" @endCanAction>
                            <x-heroicons x-cloak x-show="$wire.order.is_locked" variant="solid" name="lock-closed" />
                            <x-heroicons x-cloak x-show="! $wire.order.is_locked" variant="solid" name="lock-open" />
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
                                        :label="__('Reminder Level :level', ['level' => $order->payment_reminder_current_level])"
                                        warning
                                        rounded
                                    />
                                    @break
                                @case(2)
                                    <x-badge
                                        :label="__('Reminder Level :level', ['level' => $order->payment_reminder_current_level])"
                                        orange
                                        rounded
                                    />
                                    @break
                                @default
                                    <x-badge
                                        :label="__('Reminder Level :level', ['level' => $order->payment_reminder_current_level])"
                                        negative
                                        rounded
                                    />
                            @endswitch
                        @endif
                    </div>
                </h1>
                <a wire:navigate class="flex gap-1.5 font-semibold opacity-40 dark:text-gray-200" x-bind:href="$wire.order.parent?.url" x-cloak x-show="$wire.order.parent?.url">
                    <x-heroicons name="link" class="w-4 h-4" />
                    <span x-text="$wire.order.parent?.label"></span>
                </a>
            </div>
        </div>
        <div class="justify-stretch mt-6 flex flex-col-reverse space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3">
            @if(resolve_static(\FluxErp\Actions\Order\ReplicateOrder::class, 'canPerformAction', [false]))
                <x-button
                    spinner="replicate"
                    class="w-full"
                    wire:click="replicate()"
                    :label="__('Replicate')"
                />
            @endif
            @if(resolve_static(\FluxErp\Actions\Order\DeleteOrder::class, 'canPerformAction', [false]) && ! $order->is_locked)
                <x-button
                    wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Order')]) }}"
                    negative
                    label="{{ __('Delete') }}"
                    wire:click="delete"
                />
            @endif
            @if(resolve_static(\FluxErp\Actions\Order\UpdateOrder::class, 'canPerformAction', [false]) && ! $order->is_locked)
                <x-button
                    primary
                    spinner="save"
                    class="w-full"
                    x-on:click="$wire.save(orderPositions)"
                    :label="__('Save')"
                />
            @endif
        </div>
    </div>
    <x-flux::tabs wire:loading="tab" wire:model="tab" :$tabs class="w-full lg:col-start-1 xl:col-span-2 xl:flex gap-4">
        <x-slot:prepend>
            <section class="relative basis-2/12" wire:ignore>
                <div class="sticky top-6 flex flex-col gap-4">
                    @section('contact-address-card')
                        <x-card :title="__('Contact')">
                            <x-slot:action>
                                <x-button outline icon="eye" href="{{ route('contacts.id?', $order->contact_id ?? '') }}">
                                </x-button>
                            </x-slot:action>
                            <div x-data="{
                                    updateContactId(id) {
                                        Alpine.$data(
                                            document.getElementById('order-invoice-address-id').querySelector('[x-data]')
                                        ).asyncData.params.where[0][2] = id;
                                        Alpine.$data(
                                            document.getElementById('order-delivery-address-id').querySelector('[x-data]')
                                        ).asyncData.params.where[0][2] = id;
                                        $wire.fetchContactData();
                                    }
                                }"
                            >
                                <x-select
                                    class="pb-4"
                                    :label="__('Contact')"
                                    :disabled="$order->is_locked"
                                    wire:model="order.contact_id"
                                    option-value="contact_id"
                                    option-label="label"
                                    option-description="description"
                                    :clearable="false"
                                    x-on:selected="updateContactId($event.detail.contact_id)"
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
                            </div>
                        </x-card>
                    @show
                    @section('invoice-address-card')
                        <x-card :title="__('Invoice Address')">
                            <x-slot:action>
                                <x-button outline icon="eye" href="{{ route('contacts.id?', $order->address_invoice['contact_id'] ?? '') }}">
                                </x-button>
                            </x-slot:action>
                            <div id="order-invoice-address-id">
                                <x-select
                                    :disabled="$order->is_locked"
                                    class="pb-4"
                                    wire:model.live="order.address_invoice_id"
                                    option-value="id"
                                    option-label="label"
                                    option-description="description"
                                    :clearable="false"
                                    :async-data="[
                                        'api' => route('search', \FluxErp\Models\Address::class),
                                        'params' => [
                                            'with' => 'contact.media',
                                            'where' => [
                                                ['contact_id', '=', $order->contact_id],
                                            ],
                                        ]
                                    ]"
                                />
                            </div>
                            <div class="text-sm">
                                <div x-text="$wire.order.address_invoice.company">
                                </div>
                                <div x-text="($wire.order.address_invoice.firstname + ' ' + $wire.order.address_invoice.lastname).trim()">
                                </div>
                                <div x-text="$wire.order.address_invoice.street">
                                </div>
                                <div x-text="($wire.order.address_invoice.zip + ' ' + $wire.order.address_invoice.city).trim()">
                                </div>
                            </div>
                        </x-card>
                    @show
                    @section('delivery-address-card')
                        <x-card :title="__('Delivery Address')">
                            <x-slot:action>
                                <x-button outline icon="eye" href="{{ route('contacts.id?', $order->address_delivery['contact_id'] ?? '') }}">
                                </x-button>
                            </x-slot:action>
                            <div id="order-delivery-address-id">
                                <x-select
                                    :disabled="$order->is_locked"
                                    class="pb-4"
                                    wire:model.live="order.address_delivery_id"
                                    option-value="id"
                                    option-label="label"
                                    option-description="description"
                                    :clearable="false"
                                    :async-data="[
                                        'api' => route('search', \FluxErp\Models\Address::class),
                                        'params' => [
                                            'with' => 'contact.media',
                                            'where' => [
                                                ['contact_id', '=', $order->contact_id],
                                            ],
                                        ]
                                    ]"
                                />
                            </div>
                            <div class="text-sm" x-bind:class="$wire.order.address_delivery_id === $wire.order.address_invoice_id && 'hidden'">
                                <div x-text="$wire.order.address_delivery?.company">
                                </div>
                                <div x-text="(($wire.order.address_delivery?.firstname ?? '') + ' ' + ($wire.order.address_delivery?.lastname ?? '')).trim()">
                                </div>
                                <div x-text="$wire.order.address_delivery?.street">
                                </div>
                                <div x-text="(($wire.order.address_delivery?.zip ?? '') + ' ' + ($wire.order.address_delivery?.city ?? '')).trim()">
                                </div>
                            </div>
                        </x-card>
                    @show
                    @section('general-card')
                        <x-card :title="__('Additional Addresses')" class="!px-0 !py-0">
                            <x-slot:action>
                                <x-button.circle
                                    class="transition-transform"
                                    x-bind:class="showAdditionalAddresses && '-rotate-90'"
                                    icon="chevron-left"
                                    x-on:click="showAdditionalAddresses = !showAdditionalAddresses"
                                />
                            </x-slot:action>
                            <div class="space-y-3 px-2 py-5" x-collapse x-cloak x-show="showAdditionalAddresses">
                                <livewire:order.additional-addresses :order-id="$order->id"/>
                            </div>
                        </x-card>
                        <x-card :title="__('Order Informations')" class="!px-0 !py-0">
                            <x-slot:action>
                                <x-button.circle
                                    class="transition-transform"
                                    x-bind:class="showOrderInformations && '-rotate-90'"
                                    icon="chevron-left"
                                    x-on:click="showOrderInformations = !showOrderInformations"
                                />
                            </x-slot:action>
                            <div class="space-y-3 px-2 py-5" x-collapse x-cloak x-show="showOrderInformations">
                                @if(count($clients) > 1)
                                    <x-select
                                        disabled
                                        :label="__('Client')"
                                        :options="$clients"
                                        option-value="id"
                                        option-label="name"
                                        :clearable="false"
                                        autocomplete="off"
                                        wire:model.live="order.client_id"
                                    />
                                @endif
                                <x-select
                                    :label="__('Commission Agent')"
                                    option-value="id"
                                    option-label="label"
                                    :disabled="$order->is_locked"
                                    autocomplete="off"
                                    wire:model="order.agent_id"
                                    :template="[
                                        'name' => 'user-option',
                                    ]"
                                    :async-data="[
                                        'api' => route('search', \FluxErp\Models\User::class),
                                        'method' => 'POST',
                                        'params' => [
                                            'with' => 'media',
                                        ]
                                    ]"
                                />
                                <x-select
                                    :label="__('Responsible User')"
                                    :disabled="$order->is_locked"
                                    autocomplete="off"
                                    option-value="id"
                                    option-label="label"
                                    wire:model="order.responsible_user_id"
                                    :template="[
                                        'name' => 'user-option',
                                    ]"
                                    :async-data="[
                                        'api' => route('search', \FluxErp\Models\User::class),
                                        'method' => 'POST',
                                        'params' => [
                                            'with' => 'media',
                                        ]
                                    ]"
                                />
                                <x-select
                                    :label="__('Assigned')"
                                    :disabled="$order->is_locked"
                                    autocomplete="off"
                                    :multiselect="true"
                                    option-value="id"
                                    option-label="label"
                                    wire:model="order.users"
                                    :template="[
                                        'name' => 'user-option',
                                    ]"
                                    :async-data="[
                                        'api' => route('search', \FluxErp\Models\User::class),
                                        'method' => 'POST',
                                        'params' => [
                                            'with' => 'media',
                                        ]
                                    ]"
                                />
                                <x-select
                                    :label="__('Price list')"
                                    :options="$priceLists"
                                    option-value="id"
                                    option-label="name"
                                    :clearable="false"
                                    autocomplete="off"
                                    wire:model.live="order.price_list_id"
                                    x-bind:disabled="$wire.order.is_locked"
                                    :disabled="$order->is_locked"
                                />
                                <x-select
                                    :label="__('Payment method')"
                                    :options="$paymentTypes"
                                    option-value="id"
                                    option-label="name"
                                    :clearable="false"
                                    autocomplete="off"
                                    wire:model.live="order.payment_type_id"
                                    x-bind:disabled="$wire.order.is_locked"
                                    :disabled="$order->is_locked"
                                />
                                @if($contactBankConnections)
                                    <x-select
                                        wire:model="order.contact_bank_connection_id"
                                        :label="__('Bank connection')"
                                        :disabled="$order->is_locked"
                                        :options="$contactBankConnections"
                                        option-key-value
                                    />
                                @endif
                                @if(count($languages) > 1)
                                    <x-select
                                        :label="__('Language')"
                                        :options="$languages"
                                        option-value="id"
                                        option-label="name"
                                        :clearable="false"
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
                                <x-state
                                    class="w-full"
                                    align="left"
                                    :label="__('Order state')"
                                    wire:model="order.state"
                                    formatters="formatter.state"
                                    available="availableStates.state"
                                />
                                <x-state
                                    align="left"
                                    :label="__('Payment state')"
                                    wire:model="order.payment_state"
                                    formatters="formatter.payment_state"
                                    available="availableStates.payment_state"
                                />
                                <x-state
                                    align="left"
                                    :label="__('Delivery state')"
                                    wire:model="order.delivery_state"
                                    formatters="formatter.delivery_state"
                                    available="availableStates.delivery_state"
                                />
                                @if($order->is_locked)
                                    <x-button
                                        primary
                                        class="w-full"
                                        icon="document-text"
                                        wire:click="saveStates()"
                                        :label="__('Save')"
                                    />
                                @endif
                            </div>
                        </x-card>
                    @show
                </div>
            </section>
        </x-slot:prepend>
        @includeWhen($tab === 'order.order-positions', 'flux::livewire.order.order-positions')
        <x-slot:append>
            <section class="relative basis-2/12" wire:ignore>
                <div class="sticky top-6 space-y-6">
                    @section('content.right')
                        <x-card>
                            <div class="space-y-4">
                                @section('actions')
                                    @if($printLayouts)
                                        <x-button
                                            primary
                                            class="w-full"
                                            icon="document-text"
                                            wire:click="openCreateDocumentsModal()"
                                            :label="__('Create Documents')"
                                        />
                                        <div class="dropdown-full-w">
                                            <x-dropdown width="w-full">
                                                <x-slot name="trigger">
                                                    <x-button class="w-full" icon="search">
                                                        {{ __('Preview') }}
                                                    </x-button>
                                                </x-slot>
                                                <template x-for="printLayout in $wire.printLayouts">
                                                    <x-dropdown.item
                                                        x-on:click="$wire.openPreview(printLayout.layout, '{{ morph_alias(\FluxErp\Models\Order::class) }}', $wire.order.id)">
                                                        <span x-text="printLayout.label"></span>
                                                    </x-dropdown.item>
                                                </template>
                                            </x-dropdown>
                                        </div>
                                        <livewire:features.signature-link-generator :model-type="\FluxErp\Models\Order::class" wire:model="order.id"/>
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
                                    @section('content.right.summary.profit')
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
                                        <div class="flex justify-between p-2.5">
                                            <div>
                                                {{ __('Discount') }}
                                            </div>
                                            <div>
                                                <span x-html="formatters.coloredMoney(($wire.order.total_net_price - $wire.order.total_base_net_price) ?? 0)">
                                                </span>
                                            </div>
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
                                    <div class="dark:bg-secondary-700 flex justify-between bg-gray-50 p-2.5">
                                        <div>
                                            {{ __('Balance') }}
                                        </div>
                                        <div>
                                            <span x-html="formatters.coloredMoney($wire.order.balance ?? 0)">
                                            </span>
                                        </div>
                                    </div>
                                @show
                            </div>
                        </x-card>
                        <x-card>
                            <div class="space-y-3">
                                @section('content.right.order_dates')
                                    <x-datetime-picker wire:model="order.invoice_date" :without-time="true" :disabled="true" :label="__('Invoice Date')" />
                                    <x-datetime-picker wire:model="order.system_delivery_date" :without-time="true" :disabled="$order->is_locked" :label="__('Performance/Delivery date')" />
                                    <x-datetime-picker wire:model="order.system_delivery_date_end" :without-time="true" :disabled="$order->is_locked" :label="__('Performance/Delivery date end')" />
                                    <x-datetime-picker wire:model="order.order_date" :without-time="true" :disabled="$order->is_locked" :label="__('Order Date')" />
                                    <x-input wire:model="order.commission" :disabled="$order->is_locked" :label="__('Commission')" />
                                    <x-datetime-picker
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
