<div
    x-data="{
        calculatedPrices: {},
        units: @js(resolve_static(\FluxErp\Models\Unit::class, 'query')->pluck('name', 'id')),
        init() {
            $wire.getPriceLists().then(() =>
                $wire.priceLists.forEach((priceList) => {
                    priceList.price_net = $nuxbe.parseNumber(
                        priceList.price_net,
                    );
                    priceList.price_gross = $nuxbe.parseNumber(
                        priceList.price_gross,
                    );
                    this.calculatedPrices[priceList.id] = {
                        price_net: priceList.price_net,
                        price_gross: priceList.price_gross,
                    };
                }),
            );
        },
        pricePerBasicUnit(priceList) {
            const sellingUnit = Number($wire.product.selling_unit);
            const basicUnit = Number($wire.product.basic_unit);

            const priceNet = Number(priceList.price_net);

            // a net price of zero is a price, only null or an empty field is not
            if (
                !sellingUnit ||
                !basicUnit ||
                sellingUnit === basicUnit ||
                priceList.price_net == null ||
                priceList.price_net === '' ||
                Number.isNaN(priceNet)
            ) {
                return null;
            }

            return $nuxbe.parseNumber(
                Math.round(((priceNet * basicUnit) / sellingUnit) * 100) / 100,
            );
        },
        resetPrice(priceList) {
            if (priceList.is_editable) {
                return;
            }

            const calculated = this.calculatedPrices[priceList.id];
            priceList.price_net = calculated?.price_net;
            priceList.price_gross = calculated?.price_gross;
        },
        recalculate(priceList, isNet) {
            const vatRate = Number($wire.product.vat_rate?.rate_percentage);

            if (!vatRate) {
                if (isNet) {
                    priceList.price_gross = $nuxbe.parseNumber(
                        priceList.price_net,
                    );
                } else {
                    priceList.price_net = $nuxbe.parseNumber(
                        priceList.price_gross,
                    );
                }

                return;
            }

            if (isNet) {
                priceList.price_gross = $nuxbe.parseNumber(
                    priceList.price_net * (1 + vatRate),
                );
            } else {
                priceList.price_net = $nuxbe.parseNumber(
                    priceList.price_gross / (1 + vatRate),
                );
            }
        },
    }"
    class="space-y-5"
>
    <x-card :header="__('Calculation')">
        <x-select.styled
            x-on:select="$wire.product.vat_rate = $event.detail.select"
            :label="__('VAT rate')"
            wire:model="product.vat_rate_id"
            :options="$this->vatRates"
            select="label:name|value:id"
        />
    </x-card>
    <template x-for="priceList in $wire.priceLists">
        <x-card class="space-y-2.5">
            <x-slot:header>
                <div class="flex gap-1.5">
                    <span x-text="priceList.name"></span>
                    <x-badge
                        x-show="priceList.is_default"
                        color="indigo"
                        :text="__('Default')"
                    />
                    <x-badge
                        x-show="priceList.is_purchase"
                        color="red"
                        :text="__('Purchase Price')"
                    />
                    <x-badge
                        x-show="priceList.parent && !priceList.price_id"
                        color="amber"
                        x-text="'{{ __('Inherited from :parent_name') }}'.replace(':parent_name', priceList.parent?.name)"
                    />
                    <div x-show="priceList.parent">
                        <x-toggle
                            x-model.boolean="priceList.is_editable"
                            x-on:change="resetPrice(priceList)"
                            x-bind:disabled="!isEditing"
                            label="{{ __('Override calculated price') }}"
                        />
                    </div>
                </div>
            </x-slot:header>
            <x-input
                :prefix="resolve_static(\FluxErp\Models\Currency::class, 'default')?->symbol"
                class="net-price"
                type="number"
                x-on:input="recalculate(priceList, true)"
                x-bind:readonly="!isEditing || !priceList.is_editable"
                label="{{ __('Price net') }}"
                x-model="priceList.price_net"
            />
            <x-input
                :prefix="resolve_static(\FluxErp\Models\Currency::class, 'default')?->symbol"
                class="gross-price"
                type="number"
                x-on:input="recalculate(priceList, false)"
                x-bind:readonly="!isEditing || !priceList.is_editable"
                label="{{ __('Price gross') }}"
                x-model="priceList.price_gross"
            />
            <div
                x-cloak
                x-show="pricePerBasicUnit(priceList)"
                class="text-sm text-gray-500 dark:text-gray-400"
            >
                <span>{{ __('Price per Basic Unit (net)') }}:</span>
                <span
                    x-text="
                        '{{ resolve_static(\FluxErp\Models\Currency::class, 'default')?->symbol }} ' +
                        pricePerBasicUnit(priceList) +
                        (units[$wire.product.reference_unit_id]
                            ? ' / ' + units[$wire.product.reference_unit_id]
                            : '')
                    "
                ></span>
            </div>
        </x-card>
    </template>
</div>
