<div
    x-data="{
        order: $wire.entangle('order'),
        childOrders: $wire.entangle('childOrders'),
        detail: $wire.entangle('positionDetails'),
        positionsSummary: $wire.entangle('positionsSummary'),
        detailRoute: '{{ route('portal.orders.id', ['id' => ':id']) }}',
        selected: null,
    }"
    x-init="
        () => {
            var meta = document.createElement('meta')
            meta.name = 'currency-code'
            meta.content = order.currency.iso
            document.getElementsByTagName('head')[0].appendChild(meta)
            $watch('detail', (value) => {
                Alpine.$data(
                    document.getElementById('folder-tree').querySelector('[x-data]'),
                ).loadModel(@js(\Illuminate\Support\Facades\App::getAlias(\FluxErp\Models\Product::class)), value.product_id)
            })
        }
    "
    class="dark:text-white"
    x-on:data-table-record-selected="
        selected = Alpine.$data(
            document
                .getElementById('order-position-table')
                .querySelector('[tall-datatable]'),
        ).selected
    "
>
    <x-modal id="detail-modal" wire="detailModal">
        @section('product-modal.content')
        <div class="grid grid-cols-3 gap-5">
            <div class="col-span-1">
                @section('product-modal.content.image')
                <div class="bg-portal-light w-full rounded-md">
                    <div x-html="detail.image"></div>
                    <div
                        x-show="! detail.image"
                        class="flex min-h-[12rem] w-full items-center justify-center rounded bg-gray-300 dark:bg-gray-700"
                    >
                        <svg
                            class="h-12 w-12 text-gray-200"
                            xmlns="http://www.w3.org/2000/svg"
                            aria-hidden="true"
                            fill="currentColor"
                            viewBox="0 0 640 512"
                        >
                            <path
                                d="M480 80C480 35.82 515.8 0 560 0C604.2 0 640 35.82 640 80C640 124.2 604.2 160 560 160C515.8 160 480 124.2 480 80zM0 456.1C0 445.6 2.964 435.3 8.551 426.4L225.3 81.01C231.9 70.42 243.5 64 256 64C268.5 64 280.1 70.42 286.8 81.01L412.7 281.7L460.9 202.7C464.1 196.1 472.2 192 480 192C487.8 192 495 196.1 499.1 202.7L631.1 419.1C636.9 428.6 640 439.7 640 450.9C640 484.6 612.6 512 578.9 512H55.91C25.03 512 .0006 486.1 .0006 456.1L0 456.1z"
                            />
                        </svg>
                    </div>
                </div>
                @show
            </div>
            <div class="col-span-2">
                @section('product-modal.content.detail')
                <div
                    class="text-lg"
                    x-text="detail.product?.product_number"
                ></div>
                <div class="text-lg" x-text="detail.name"></div>
                <div
                    class="pt-5 text-sm"
                    x-show="detail.serial_number?.length"
                >
                    <span>{{ __('Serial numbers:') }}</span>
                    <ul>
                        <template
                            x-for="serialNumber in detail.serial_number"
                        >
                            <li x-text="serialNumber.serial_number"></li>
                        </template>
                    </ul>
                </div>
                <div class="pt-5" x-html="detail.product?.description"></div>
                @show
            </div>
        </div>
        <div id="folder-tree" class="pt-3">
            @section('product-modal.content.media')
            <livewire:portal.order.product-media
                :model-type="\FluxErp\Models\Product::class"
                wire:key="product-media-{{ data_get($positionDetails, 'product_id') }}"
                wire:model="positionDetails.product_id"
            />
            @show
        </div>
        @show
    </x-modal>
    <div id="new-ticket-modal-wrapper">
        <x-modal
            id="new-ticket-modal"
            :title="__('New Ticket')"
            x-on:open="$focusOn('ticket-title')"
        >
            <livewire:portal.ticket.ticket-create
                :model-type="\FluxErp\Models\Order::class"
                :model-id="$order['id']"
            />
            <x-slot:footer>
                <x-button
                    color="secondary"
                    light
                    flat
                    :text="__('Cancel')"
                    x-on:click="$modalClose('new-ticket-modal')"
                />
                <x-button
                    color="indigo"
                    :text="__('Save')"
                    x-on:click="Alpine.$data(document.getElementById('new-ticket-modal-wrapper').querySelector('[x-data]').querySelector('[x-data]')).save();"
                />
            </x-slot>
        </x-modal>
    </div>
    <h2 class="text-base font-bold uppercase">
        {{ __('Order details') }}
    </h2>
    <h1 class="py-5 text-5xl font-semibold">
        {{
            __(':order_type :order_number dated :order_date', [
                'order_type' => data_get($order, 'order_type.name'),
                'order_number' => data_get($order, 'order_number'),
                'order_date' => \Illuminate\Support\Carbon::parse(data_get($order, 'order_date'))->isoFormat('L'),
            ])
        }}
    </h1>
    <div class="flex justify-end gap-1.5 pb-5">
        @section('actions')
        @if ($order['invoice_number'])
            <x-button
                color="indigo"
                :text="__('Download invoice')"
                wire:click="downloadInvoice()"
                loading="downloadInvoice"
            />
        @endif

        @if ($order['parent_id'])
            <x-button
                color="indigo"
                :href="route('portal.orders.id', data_get($order, 'parent_id'))"
                :text="__('Show parent')"
            />
        @endif

        <x-button
            color="indigo"
            :text="__('New Ticket')"
            x-on:click="$modalOpen('new-ticket-modal')"
            loading="downloadInvoice"
        />
        @show
    </div>
    <div class="space-y-5">
        <div class="flex gap-8">
            <x-card :header="__('Invoice Address')">
                <div>
                    {{ data_get($order, 'address_invoice.company', '') }}
                </div>
                <div>
                    {{ Str::squish(data_get($order, 'address_invoice.firstname', '') . ' ' . data_get($order, 'address_invoice.lastname', '')) }}
                </div>
                <div>
                    {{ data_get($order, 'address_invoice.street', '') }}
                </div>
                <div>
                    {{ trim(($order['address_invoice']['zip'] ?? '') . ' ' . ($order['address_invoice']['city'] ?? '')) }}
                </div>
            </x-card>
            <template x-for="address in order.addresses">
                <x-card>
                    <div x-text="address.company?.trim()" />
                    <div
                        x-text="(address.firstname + ' ' + address.lastname)?.trim()"
                    />
                    <div x-text="address.addition?.trim()" />
                    <div x-text="address.street?.trim()" />
                    <div
                        x-text="(address.zip + ' ' + address.city)?.trim()"
                    />
                </x-card>
            </template>
        </div>
        @section('attachments')
        @if ($attachments)
            <x-card :header="__('Attachments')">
                @foreach ($attachments as $attachment)
                    <div class="flex justify-between">
                        <div class="flex items-center justify-center gap-1">
                            <div target="_blank">
                                <span class="font-semibold">
                                    {{ __(\Illuminate\Support\Str::headline($attachment['collection_name'])) }}
                                </span>
                                {{ $attachment['file_name'] }}
                            </div>
                            <x-button
                                color="indigo"
                                xs
                                flat
                                :text="__('Download')"
                                wire:click="downloadMedia({{ $attachment['id'] }})"
                            />
                        </div>
                    </div>
                @endforeach
            </x-card>
        @endif

        @show
        @section('attributes')
        <x-card>
            <div class="grid grid-cols-1 lg:grid-cols-2">
                <div class="grid grid-cols-2 gap-5">
                    @section('attributes.left')
                    <div class="text-right">{{ __('Commission') }}:</div>
                    <div>
                        {{ data_get($order, 'commission') }}
                    </div>
                    <div class="text-right">{{ __('Customer no.') }}:</div>
                    <div>
                        {{ data_get($order, 'address_invoice.contact.customer_number') }}
                    </div>
                    <div class="text-right">{{ __('Logistics note') }}:</div>
                    <div>
                        {{ data_get($order, 'logistic_note') }}
                    </div>
                    @show
                </div>
                <div class="grid grid-cols-2 gap-5">
                    @section('attributes.right')
                    <div class="text-right">{{ __('Clerk') }}:</div>
                    <div>
                        {{ data_get($order, 'created_by') }}
                    </div>
                    <div class="text-right">
                        {{ __('Responsible representative') }}:
                    </div>
                    <div>
                        {{ data_get($order, 'agent.name') }}
                    </div>
                    <div class="text-right">
                        {{ __('Performance/Delivery date') }}:
                    </div>
                    <div>
                        {{ data_get($order, 'system_delivery_date') }}
                    </div>
                    @show
                </div>
            </div>
        </x-card>
        @show
        @if ($order['header'])
            <x-card>
                {!! $order['header'] !!}
            </x-card>
        @endif

        <div class="space-y-8">
            <div
                id="order-position-table"
                x-on:data-table-row-clicked="$wire.selectPosition($event.detail.id)"
            >
                <livewire:portal.data-tables.order-position-list
                    :order-id="$order['id']"
                    :filters="[['column' => 'order_id', 'operator' => '=', 'value' => $order['id']]]"
                />
            </div>
            <div x-show="positionsSummary.length > 0" x-cloak>
                <x-flux::table>
                    <x-slot name="title">
                        <h2 class="text-base font-bold uppercase">
                            {{ __('Summary') }}
                        </h2>
                    </x-slot>
                    <template x-for="item in positionsSummary">
                        <x-flux::table.row>
                            <x-flux::table.cell x-html="item.pos" />
                            <x-flux::table.cell
                                class="col-span-3"
                                x-html="item.name"
                            />
                            <x-flux::table.cell
                                x-html="item.{{ auth()->user()->priceList?->is_net ? 'total_net_price' : 'total_gross_price' }}"
                            />
                        </x-flux::table.row>
                    </template>
                </x-flux::table>
            </div>
            <x-card>
                <div class="text-sm">
                    <div class="flex justify-between py-2.5">
                        <div>
                            {{ __('Sum net') }}
                        </div>
                        <div>
                            <span
                                x-text="formatters.money(order.total_net_price)"
                            ></span>
                        </div>
                    </div>
                    <template x-for="vat in order.total_vats">
                        <div class="flex justify-between py-2.5">
                            <div
                                x-text="'{{ __('Plus ') }}' + ' ' + formatters.percentage(vat.vat_rate_percentage)"
                            ></div>
                            <div>
                                <span
                                    x-text="formatters.money(vat.total_vat_price)"
                                ></span>
                            </div>
                        </div>
                    </template>
                    <div class="flex justify-between py-2.5">
                        <div>
                            {{ __('Total Gross') }}
                        </div>
                        <div>
                            <span
                                x-text="formatters.money(order.total_gross_price)"
                            ></span>
                        </div>
                    </div>
                </div>
            </x-card>
            <div>
                <h2 class="text-base font-bold uppercase">
                    {{ __('Payment information') }}
                </h2>
                <x-card>
                    {!! data_get($order, 'footer') !!}
                    <br />
                    {!! is_array(data_get($order, 'payment_texts')) ? nl2br(implode('<br />', data_get($order, 'payment_texts'))) : data_get($order, 'payment_texts') !!}
                </x-card>
            </div>
        </div>
    </div>
    @if ($childOrders)
        <div class="pt-6">
            <h2 class="text-base font-bold uppercase">
                {{ __('Related orders') }}
            </h2>
            <div class="mt-3">
                <livewire:portal.data-tables.order-list
                    :is-searchable="false"
                    :is-filterable="false"
                    :is-sortable="false"
                    :show-filter-inputs="false"
                    :filters="[
                        [
                            'parent_id',
                            '=',
                            $order['id']
                        ]
                    ]"
                />
            </div>
        </div>
    @endif
</div>
