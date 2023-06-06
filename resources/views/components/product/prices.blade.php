<div
    x-data="{
        priceLists: $wire.entangle('priceLists').defer,
        recalculate(priceList, isNet) {
            const vatRate = Number(product.vat_rate.rate_percentage);
            if (isNet) {
                priceList.price_gross = priceList.price_net * (1 + vatRate);
            } else {
                priceList.price_net = priceList.price_gross / (1 + vatRate);
            }
        },
    }"
    x-init="$wire.getPriceLists();"
    class="space-y-5"
>
    <template x-for="priceList in priceLists">
        <x-card class="space-y-2.5">
            <x-slot:title>
                <span x-text="priceList.name"></span>
                <x-badge x-show="priceList.is_default" class="ml-2" variant="primary" label="{{ __('Default') }}" />
            </x-slot:title>
            <x-input  class="net-price" type="number" x-on:input="recalculate(priceList, true);" x-bind:readonly="!edit" label="{{ __('Price net') }}" x-model="priceList.price_net" />
            <x-input class="gross-price" type="number" x-on:input="recalculate(priceList, false);" x-bind:readonly="!edit" label="{{ __('Price gross') }}" x-model="priceList.price_gross" />
        </x-card>
    </template>
</div>
