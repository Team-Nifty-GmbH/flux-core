<div
    x-data="{
        init() {
            $wire.getProductCrossSellings();
        },
        pushProduct: function (product, index) {
            $wire.productCrossSellings[index].products.push(product);
        }
    }"
    class="space-y-5"
>
    <div style="display: none;">
        <div id="select">
            <x-select.styled
                :label="__('Assign product')"
                x-on:select="pushProduct($event.detail.select, $el.closest('[data-index]').getAttribute('data-index')); clear()"
                class="pb-4"
                select="label:name|value:id|description:product_number"
                :request="[
                    'url' => route('search', \FluxErp\Models\Product::class),
                    'params' => [
                        'fields' => [
                            'id',
                            'product_number',
                            'name',
                        ],
                    ],
                ]"
            />
        </div>
    </div>
    <template x-for="(productCrossSelling, index) in $wire.productCrossSellings ?? []">
        <x-card>
            <x-slot:title>
                <div class="flex justify-between w-full">
                    <span x-text="productCrossSelling.name"></span>
                </div>
            </x-slot:title>
            <div class="flex gap-4 flex-col">
                <x-input x-bind:readonly="!edit" x-model="productCrossSelling.name" :label="__('Name')" />
                <x-toggle x-bind:readonly="!edit" x-model="productCrossSelling.is_active" :label="__('Active')" />
            </div>
            <x-slot:footer>
                <div x-bind:data-index="index" class="flex flex-col gap-4 pb-4" x-cloak x-show="edit" x-transition>
                    <x-button color="indigo" :text="__('Add product')" x-on:click="$el.parentNode.appendChild(document.getElementById('select'))" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <template x-for="(product, productIndex) in productCrossSelling.products">
                        <div class="grid grid-cols-3 text-sm font-medium text-gray-700 dark:text-gray-400">
                            <div class="flex items-center gap-1.5">
                                <div class="shrink-0 inline-flex items-center justify-center overflow-hidden rounded-full w-8 h-8 text-sm border border-gray-200 dark:border-secondary-500">
                                    <img class="shrink-0 object-cover object-center" x-bind:src="product.avatar_url ?? product.src" />
                                </div>
                                <span x-text="product.product_number"></span>
                            </div>
                            <span x-text="product.name"></span>
                            <div x-show="productCrossSelling.products.length > 1 && edit" x-transition>
                                <x-button.circle icon="trash" color="red" x-on:click="productCrossSelling.products.splice(productIndex, 1)" />
                            </div>
                        </div>
                    </template>
                </div>
            </x-slot:footer>
            <x-slot:header>
                <div x-show="edit" x-cloak x-transition>
                    <x-button color="red" x-on:click="$wire.productCrossSellings.splice(index, 1)">
                        {{ __('Delete') }}
                    </x-button>
                </div>
            </x-slot:header>
        </x-card>
    </template>
    <div class="w-full flex justify-center">
        <x-button color="indigo" x-on:click="edit = true; $wire.productCrossSellings.push({'name': '{{ __('New Cross Selling') }}', 'is_active': true, 'is_new': true, 'products': []})">
            {{ __('Add product cross selling') }}
        </x-button>
    </div>
</div>

