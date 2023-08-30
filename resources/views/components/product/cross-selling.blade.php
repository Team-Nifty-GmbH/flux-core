<div
    x-data="{
        productCrossSellings: $wire.entangle('productCrossSellings').defer,
        pushProduct: function (product, index) {
            this.productCrossSellings[index].products.push(product);
        }
    }"
    x-init="$wire.getProductCrossSellings()"
    wire:key="{{ uniqid() }}"
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
                    option-description="description"
                    :async-data="[
                                        'api' => route('search', \FluxErp\Models\Product::class),
                                        'params' => [
                                            'fields' => ['id', 'product_number', 'name'],
                                        ]
                                    ]"
            />
        </div>
    </div>
    <template x-for="(productCrossSelling, index) in productCrossSellings">
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
                <div x-bind:data-index="index" class="flex flex-col gap-4 pb-4" x-show="edit" x-transition>
                    <x-button primary :label="__('Add product')" x-on:click="$el.parentNode.appendChild(document.getElementById('select'))" />
                </div>
                <template x-for="(product, productIndex) in productCrossSelling.products">
                    <div class="grid grid-cols-3">
                        <span x-text="product.product_number">
                        </span>
                        <span x-text="product.name">
                        </span>
                        <div x-show="productCrossSelling.products.length > 1 && edit" x-transition>
                            <x-button.circle icon="trash" negative x-on:click="productCrossSelling.products.splice(productIndex, 1)" />
                        </div>
                    </div>
                </template>
            </x-slot:footer>
            <x-slot:action>
                <div x-show="edit" x-cloak x-transition>
                    <x-button negative x-on:click="productCrossSellings.splice(index, 1)">
                        {{ __('Delete') }}
                    </x-button>
                </div>
            </x-slot:action>
        </x-card>
    </template>
    <div class="w-full flex justify-center" x-show="edit" x-transition x-cloak>
        <x-button primary x-on:click="productCrossSellings.push({'name': '{{ __('New Cross Selling') }}', 'is_active': true, 'is_new': true, 'products': []})">
            {{ __('Add product cross selling') }}
        </x-button>
    </div>
</div>

