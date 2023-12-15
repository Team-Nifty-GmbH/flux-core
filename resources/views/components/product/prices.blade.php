<div
    x-data="{
        init() {
            $wire.getPriceLists()
                .then(() => $wire.priceLists.forEach(
                    priceList => {
                        priceList.price_net = parseNumber(priceList.price_net);
                        priceList.price_gross = parseNumber(priceList.price_gross);
                    }
                ))
        },
        recalculate(priceList, isNet) {
            const vatRate = Number($wire.product.vat_rate.rate_percentage);
            if (isNet) {
                priceList.price_gross = parseNumber(priceList.price_net * (1 + vatRate));
            } else {
                priceList.price_net = parseNumber(priceList.price_gross / (1 + vatRate));
            }
        }
    }"
    class="space-y-5"
>
    <x-card :title="__('Calculation')">
        <x-select :options="$this->vatRates" label="{{ __('VAT rate') }}" wire:model="product.vat_rate_id" option-label="name" option-value="id"/>
    </x-card>
    <template x-for="priceList in $wire.priceLists">
        <x-card class="space-y-2.5">
            <x-slot:title>
                <div class="flex gap-1.5">
                    <span x-text="priceList.name"></span>
                    <x-badge x-show="priceList.is_default" primary label="{{ __('Default') }}" />
                    <x-badge x-show="priceList.parent" warning x-text="'{{ __('Inherited from :parent_name') }}'.replace(':parent_name', priceList.parent?.name)" />
                </div>
            </x-slot:title>
            <x-input :prefix="$defaultCurrency->symbol" class="net-price" type="number" x-on:input="recalculate(priceList, true);" x-bind:readonly="!edit || !priceList.is_editable" label="{{ __('Price net') }}" x-model="priceList.price_net" />
            <x-input :prefix="$defaultCurrency->symbol" class="gross-price" type="number" x-on:input="recalculate(priceList, false);" x-bind:readonly="!edit || !priceList.is_editable" label="{{ __('Price gross') }}" x-model="priceList.price_gross" />
        </x-card>
    </template>
</div>

