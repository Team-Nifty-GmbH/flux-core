<div x-data="{
        order: $wire.entangle('order').defer,
        childOrders: $wire.entangle('childOrders').defer,
        detail: $wire.entangle('positionDetails').defer,
        positionsSummary: $wire.entangle('positionsSummary').defer,
        detailRoute: '{{ route('portal.orders.id', ['id' => ':id']) }}',
        selected: null,
    }"
     x-init="() => {
        var meta = document.createElement('meta');
        meta.name = 'currency-code';
        meta.content = order.currency.iso;
        document.getElementsByTagName('head')[0].appendChild(meta);
        $watch('detail', (value) => {
            Alpine.$data(document.getElementById('folder-tree').querySelector('[x-data]')).loadModel(@js(\FluxErp\Models\Product::class), value.product_id);
        });
     }"
     class="dark:text-white"
     x-on:data-table-record-selected="selected = Alpine.$data(document.getElementById('order-position-table').querySelector('[tall-datatable]')).selected"
>
    <x-modal.card id="detail-modal" wire:model.defer="detailModal">
        @section('product-modal.content')
            <div class="grid grid-cols-3 gap-5">
                <div class="col-span-1">
                    @section('product-modal.content.image')
                        <div class="bg-portal-light w-full rounded-md">
                            <div x-html="detail.image">
                            </div>
                            <div x-show="! detail.image"
                                 class="flex min-h-[12rem] w-full items-center justify-center rounded bg-gray-300 dark:bg-gray-700">
                                <svg class="h-12 w-12 text-gray-200" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                                     fill="currentColor" viewBox="0 0 640 512">
                                    <path
                                        d="M480 80C480 35.82 515.8 0 560 0C604.2 0 640 35.82 640 80C640 124.2 604.2 160 560 160C515.8 160 480 124.2 480 80zM0 456.1C0 445.6 2.964 435.3 8.551 426.4L225.3 81.01C231.9 70.42 243.5 64 256 64C268.5 64 280.1 70.42 286.8 81.01L412.7 281.7L460.9 202.7C464.1 196.1 472.2 192 480 192C487.8 192 495 196.1 499.1 202.7L631.1 419.1C636.9 428.6 640 439.7 640 450.9C640 484.6 612.6 512 578.9 512H55.91C25.03 512 .0006 486.1 .0006 456.1L0 456.1z"/>
                                </svg>
                            </div>
                        </div>
                    @show
                </div>
                <div class="col-span-2">
                    @section('product-modal.content.detail')
                        <div class="text-lg" x-text="detail.product?.product_number"></div>
                        <div class="text-lg" x-text="detail.name"></div>
                        <div class="pt-5 text-sm" x-show="detail.serial_number?.length">
                            <span>{{ __('Serial numbers:') }}</span>
                            <ul>
                                <template x-for="serialNumber in detail.serial_number">
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
                    <livewire:folder-tree :model-type="\FluxErp\Models\Product::class" />
                @show
            </div>
        @show
    </x-modal.card>
    <div id="new-ticket-modal">
        <x-modal.card :title="__('New Ticket')">
            <livewire:portal.ticket.ticket-create :model-type="\FluxErp\Models\Order::class" :model-id="$order['id']"/>
            <x-slot name="footer">
                <div class="w-full">
                    <div class="flex justify-between gap-x-4">
                        <div class="flex">
                            <x-button flat :label="__('Cancel')" x-on:click="close"/>
                            <x-button primary :label="__('Save')" x-on:click="Alpine.$data(document.getElementById('new-ticket-modal').querySelector('[x-data]').querySelector('[x-data]')).save();"/>
                        </div>
                    </div>
                </div>
            </x-slot>
        </x-modal.card>
    </div>
    <h2 class="text-base font-bold uppercase">
        {{ __('Order details') }}
    </h2>
    <h1 class="py-5 text-5xl font-semibold">
        {{ trans(':order_type :order_number dated :order_date', [
            'order_type' => $order['order_type']['name'],
            'order_number' => $order['order_number'],
            'order_date' => \Illuminate\Support\Carbon::parse($order['order_date'])->isoFormat('L')
        ]) }}
    </h1>
    <div class="flex justify-end pb-5 gap-1.5">
        @if($order['invoice_number'])
            <x-button primary :label="__('Download invoice')" wire:click="downloadInvoice" spinner="downloadInvoice"/>
        @endif

        @if($order['parent_id'])
            <x-button primary :href="route('portal.orders.id', $order['parent_id'])">{{ __('Show parent') }}</x-button>
        @endif
        <x-button primary :label="__('New Ticket')" x-on:click="Alpine.$data(document.getElementById('new-ticket-modal').querySelector('[wireui-modal]')).open();" spinner="downloadInvoice"/>
    </div>
    <div class="space-y-5">
        <div class="flex gap-8">
            <x-card :title="__('Invoice Address')">
                <div>
                    {{ $order['address_invoice']['company'] ?? '' }}
                </div>
                <div>
                    {{ trim(($order['address_invoice']['firstname'] ?? '') . ' ' . ($order['address_invoice']['lastname'] ?? '')) }}
                </div>
                <div>
                    {{ $order['address_invoice']['street'] ?? '' }}
                </div>
                <div>
                    {{ trim(($order['address_invoice']['zip'] ?? '') . ' ' . ($order['address_invoice']['city'] ?? '')) }}
                </div>
            </x-card>
            <template x-for="address in order.addresses">
                <x-card>
                    <div x-text="(address.company)?.trim()"/>
                    <div x-text="(address.firstname + ' ' + address.lastname)?.trim()"/>
                    <div x-text="(address.addition)?.trim()"/>
                    <div x-text="(address.street)?.trim()"/>
                    <div x-text="(address.zip + ' ' + address.city)?.trim()"/>
                </x-card>
            </template>
        </div>
        @if($attachments)
            <x-card :title="__('Attachments')">
                @foreach($attachments as $attachment)
                    <div class="flex justify-between">
                        <div class="flex justify-center items-center gap-1">
                            <div target="_blank">
                                <span class="font-semibold">{{ __(\Illuminate\Support\Str::headline($attachment['collection_name'])) }}</span> {{ $attachment['file_name'] }}
                            </div>
                            <x-button primary xs flat :label="__('Download')" wire:click="downloadMedia({{ $attachment['id'] }})" />
                        </div>
                    </div>
                @endforeach
            </x-card>
        @endif
        <x-card>
            <div class="grid grid-cols-1 lg:grid-cols-2">
                <div class="grid grid-cols-2 gap-5">
                    <div class="text-right">
                        {{ __('Commission') }}:
                    </div>
                    <div>
                        {{ $order['commission'] }}
                    </div>
                    <div class="text-right">
                        {{ __('Customer no.') }}:
                    </div>
                    <div>
                        {{ $order['address_invoice']['contact']['customer_number'] }}
                    </div>
                    <div class="text-right">
                        {{ __('Logistics note') }}:
                    </div>
                    <div>
                        {{ $order['logistic_note'] }}
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-5">
                    <div class="text-right">
                        {{ __('Clerk') }}:
                    </div>
                    <div>
                        {{ $order['user_created']['name'] ?? '' }}
                    </div>
                    <div class="text-right">
                        {{ __('Responsible representative') }}:
                    </div>
                    <div>
                        {{ $order['commission'] }}
                    </div>
                    <div class="text-right">
                        {{ __('Performance/Delivery date') }}:
                    </div>
                    <div>
                        {{ $order['system_delivery_date'] }}
                    </div>
                </div>
            </div>
        </x-card>
        @if($order['header'])
            <x-card>
                {!! $order['header'] !!}
            </x-card>
        @endif
        <div class="space-y-8">
            <div id="order-position-table" x-on:data-table-row-clicked="$wire.selectPosition($event.detail.id)">
                <livewire:data-tables.portal.order-position-list
                    :order-id="$order['id']"
                    :filters="[['column' => 'order_id', 'operator' => '=', 'value' => $order['id']]]"
                />
            </div>
            @section('actions')
            @show
            <div x-show="selected?.length > 0" class="pt-4">
                <livewire:features.custom-events :model="\FluxErp\Models\Order::class" :id="$order['id']" :additional-data="['selected', 'order']"/>
            </div>
            <div x-show="positionsSummary.length > 0" x-cloak>
                <x-table>
                    <x-slot name="title">
                        <h2 class="text-base font-bold uppercase">
                            {{ __('Summary') }}
                        </h2>
                    </x-slot>
                    <template x-for="item in positionsSummary">
                        <x-table.row>
                            <x-table.cell x-html="item.pos"/>
                            <x-table.cell class="col-span-3" x-html="item.name"/>
                            <x-table.cell
                                x-html="item.{{ auth()->user()->contact?->priceList?->is_net ? 'total_net_price' : 'total_gross_price' }}"/>
                        </x-table.row>
                    </template>
                </x-table>
            </div>
            <x-card>
                <div class="text-sm">
                    <div class="flex justify-between py-2.5">
                        <div>
                            {{ __('Sum net') }}
                        </div>
                        <div>
                            <span x-text="formatters.money(order.total_net_price)"></span>
                        </div>
                    </div>
                    <template x-for="vat in order.total_vats">
                        <div class="flex justify-between py-2.5">
                            <div x-text="'{{ __('Plus ') }}' + ' ' + formatters.percentage(vat.vat_rate_percentage)">
                            </div>
                            <div>
                                <span x-text="formatters.money(vat.total_vat_price)"></span>
                            </div>
                        </div>
                    </template>
                    <div class="flex justify-between py-2.5">
                        <div>
                            {{ __('Total gross') }}
                        </div>
                        <div>
                            <span x-text="formatters.money(order.total_gross_price)"></span>
                        </div>
                    </div>
                </div>
            </x-card>
            <div>
                <h2 class="text-base font-bold uppercase">
                    {{ __('Payment information') }}
                </h2>
                <x-card>
                    {!! $order['footer'] !!}<br/>
                    {!! is_array($order['payment_texts']) ? nl2br(implode('<br />', $order['payment_texts'])) : $order['payment_texts'] !!}
                </x-card>
            </div>
        </div>
    </div>
    @if($childOrders)
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
                            'contact_id',
                            '=',
                            auth()->user()->contact_id
                        ],
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
