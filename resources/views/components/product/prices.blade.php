<div
    x-data="{
        recalculate(priceList, isNet) {
            const vatRate = Number(product.vat_rate.rate_percentage);
            if (isNet) {
                priceList.price_gross = priceList.price_net * (1 + vatRate);
            } else {
                priceList.price_net = priceList.price_gross / (1 + vatRate);
            }
        }
    }"
    x-init="$wire.getPriceLists().then(() => priceLists.forEach(priceList => {priceList.price_net = parseNumber(priceList.price_net); priceList.price_gross = parseNumber(priceList.price_gross);}))"
    wire:key="{{ uniqid() }}"
    class="space-y-5"
>
    <x-card :title="__('Calculation')">
        <x-native-select x-model="product.vat_rate_id" label="{{ __('VAT rate') }}" x-bind:readonly="!edit">
            <template x-for="vatRate in vatRates">
                <option x-bind:valu="vatRate.id">
                    <span x-text="vatRate.name"></span>
                    <span x-text="formatters.percentage(vatRate.rate_percentage)" class="text-gray-500"></span>
                </option>
            </template>
        </x-native-select>
    </x-card>
    <template x-for="priceList in priceLists" :key="priceList.id">
        <x-card class="space-y-2.5">
            <x-slot:title>
                <div class="flex gap-1.5">
                    <span x-text="priceList.name"></span>
                    <x-badge x-show="priceList.is_default" primary label="{{ __('Default') }}" />
                </div>
            </x-slot:title>
            <x-input :prefix="$this->currency['symbol']" class="net-price" type="number" x-on:input="recalculate(priceList, true);" x-bind:readonly="!edit" label="{{ __('Price net') }}" x-model="priceList.price_net" />
            <x-input :prefix="$this->currency['symbol']" class="gross-price" type="number" x-on:input="recalculate(priceList, false);" x-bind:readonly="!edit" label="{{ __('Price gross') }}" x-model="priceList.price_gross" />
        </x-card>
    </template>
</div>

