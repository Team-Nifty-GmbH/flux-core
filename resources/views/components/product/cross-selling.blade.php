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
            <x-select
                :label="__('Assign product')"
                x-on:selected="pushProduct($event.detail, $el.closest('[data-index]').getAttribute('data-index')); clear()"
                class="pb-4"
                option-value="id"
                option-label="label"
                option-description="product_number"
                template="user-option"
                :async-data="[
                    'api' => route('search', \FluxErp\Models\Product::class),
                    'params' => [
                        'fields' => ['id', 'product_number', 'name'],
                    ]
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
                    <x-button primary :label="__('Add product')" x-on:click="$el.parentNode.appendChild(document.getElementById('select'))" />
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
                                <x-mini-button icon="trash" negative x-on:click="productCrossSelling.products.splice(productIndex, 1)" />
                            </div>
                        </div>
                    </template>
                </div>
            </x-slot:footer>
            <x-slot:action>
                <div x-show="edit" x-cloak x-transition>
                    <x-button negative x-on:click="$wire.productCrossSellings.splice(index, 1)">
                        {{ __('Delete') }}
                    </x-button>
                </div>
            </x-slot:action>
        </x-card>
    </template>
    <div class="w-full flex justify-center">
        <x-button primary x-on:click="edit = true; $wire.productCrossSellings.push({'name': '{{ __('New Cross Selling') }}', 'is_active': true, 'is_new': true, 'products': []})">
            {{ __('Add product cross selling') }}
        </x-button>
    </div>
</div>

