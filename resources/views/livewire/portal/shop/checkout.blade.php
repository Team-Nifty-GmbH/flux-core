<div class="flex flex-col gap-4 text-gray-900 dark:text-gray-50">
    <x-modal width="7xl" name="terms-and-conditions">
        <x-card class="w-full">
            <div id="terms-and-conditions">
            </div>
        </x-card>
    </x-modal>
    <x-modal name="edit-delivery-address">
        <x-card :title="__('Edit delivery address')">
            <x-flux::address.address :only-postal="true" />
            <div class="flex flex-wrap gap-4 pt-4">
                @foreach(auth()->user()->contact->addresses as $address)
                    <div
                        class="cursor-pointer rounded-md bg-secondary-100 p-2"
                        x-on:click="$wire.loadAddress({{ $address->id }})"
                        x-bind:class="$wire.address.id === {{ $address->id }} && 'ring-2 ring-offset-2 ring-primary-500'"
                    >
                        {!! implode('<br />', $address->postal_address)  !!}
                    </div>
                @endforeach
                <div
                    class="cursor-pointer rounded-md bg-secondary-100 p-2 flex justify-center items-center flex-col"
                    x-on:click="$wire.loadAddress()"
                    x-bind:class="$wire.address.id === null && 'ring-2 ring-offset-2 ring-primary-500'"
                >
                    <x-icon class="w-5 h-5" name="plus" />
                    <div>
                        {{  __('Add new address') }}
                    </div>
                </div>
            </div>
            <x-slot:footer>
                <div class="flex justify-end gap-1">
                    <x-button x-on:click="close()">{{ __('Cancel') }}</x-button>
                    <x-button wire:click="saveDeliveryAddress().then((success) => success ? close() : null)" primary>{{ __('Save') }}</x-button>
                </div>
            </x-slot>
        </x-card>
    </x-modal>
    <x-card :title="__('Terms And Conditions')">
        <div class="flex items-center gap-1.5">
            <x-checkbox wire:model.boolean="termsAndConditions" />
            <div>
                {{ __('I accept the terms and conditions') }}
            </div>
            <div wire:click="loadTermsAndConditions().then((text) => {document.getElementById('terms-and-conditions').innerHTML = text; $openModal('terms-and-conditions')})" class="cursor-pointer text-primary-500 underline">
                {{ __('Read terms and conditions') }}
            </div>
        </div>
    </x-card>
    <div class="flex flex-col sm:flex-row gap-4 justify-between">
        <x-card :title="__('Invoice Address')">
            <p>
                {!! implode('</p><p>', auth()->user()->contact->invoiceAddress?->postal_address ?? [])  !!}
            </p>
        </x-card>
        <x-card :title="__('Delivery Address')">
            <x-slot:action>
                <x-button xs x-on:click="$openModal('edit-delivery-address')" :label="__('Edit delivery address')" />
            </x-slot:action>
            <p>
                {!! implode('</p><p>', $this->deliveryAddress->postalAddress() ?? [])  !!}
            </p>
        </x-card>
    </div>
    <x-card>
        <div class="flex flex-col gap-4">
            @if(auth()->user()?->contact?->priceList?->is_net)
                <x-input :label="__('Desired delivery date')" wire:model="delivery_date" />
                <x-input :label="__('Commission')" wire:model="commission" />
            @endif
            <x-textarea :label="__('Comment')" wire:model="comment" />
        </div>
    </x-card>
    <x-card :title="__('Positions')">
        <div class="flex flex-col gap-1.5">
            @foreach($this->cart?->cartItems ?? [] as $key => $cartItem)
                <x-flux::shop.cart-item :cartItem="$cartItem" :key="$cartItem->id"/>
                <hr />
            @endforeach
        </div>
    </x-card>
    <x-card :title="__('Summary')">
        <div class="flex flex-col gap-1.5">
            <div class="flex justify-between gap-2">
                <div>{{ __('Total Net') }}</div>
                <div>{{ Number::currency(number: $this->cart->cart_items_sum_total_net ?? 0, locale: app()->getLocale()) }}</div>
            </div>
            @foreach($this->cart->vatRates() as $vatRate)
                <div class="flex justify-between gap-2">
                    <div>{{ __('Plus :percentage% VAT', ['percentage' => bcmul($vatRate['vat_rate_percentage'], 100, 2)]) }}</div>
                    <div>{{ Number::currency(number: $vatRate['vat_sum'], locale: app()->getLocale()) }}</div>
                </div>
            @endforeach
            <div class="flex justify-between gap-2 font-semibold">
                <div>{{ __('Total Gross') }}</div>
                <div>{{ Number::currency(number: $this->cart->cart_items_sum_total_gross ?? 0, locale: app()->getLocale()) }}</div>
            </div>
        </div>
        <x-slot:footer>
            <x-button class="w-full" wire:click="buy()" primary>{{ __('Buy now') }}</x-button>
        </x-slot:footer>
    </x-card>
    @if(auth()->user()->priceList?->is_net)
        * {{ __('All prices net plus VAT') }}
    @else
        * {{ __('All prices gross including VAT') }}
    @endif
</div>
