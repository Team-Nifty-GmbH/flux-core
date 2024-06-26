<div class="flex flex-col gap-8 h-full" x-init="$watch('$wire.productForm.product_cross_sellings', () => {showCrossSelling = $wire.productForm.product_cross_sellings[0]?.id})" x-data="{showMeta: null, showCrossSelling: null}">
    <x-spinner />
    <div class="relative grid sm:flex gap-8 h-full">
        <div class="w-full sm:w-1/2 flex justify-items-center gap-4">
            <div class="w-full">
                <div class="w-full">
                    <img src="{{ route('icons', ['name' => 'photo']) }}" x-bind:src="$wire.productForm.cover_url" alt="{{ $productForm->name }}" class="w-full object-contain max-h-96 rounded-lg" />
                </div>
                <div class="flex gap-2 justify-start">
                    @foreach($productForm->media as $media)
                        <div class="rounded-md flex-none" x-bind:class="$wire.productForm.cover_url === '{{ $media }}' && 'ring-2 ring-offset-2 ring-primary-500'" x-on:click="$wire.productForm.cover_url = '{{ $media }}'">
                            <x-avatar xl squared :src="$media" class="w-full" />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="w-full sm:w-1/2 flex flex-col gap-8">
            <div>
                <h1 class="text-2xl font-semibold">
                    {{ $productForm->name }}
                </h1>
                <h2 class="font-semibold">
                    {{ __('Product Number') . ': ' . $productForm->product_number }}
                </h2>
            </div>
            <p>
                {!! $productForm->description !!}
            </p>
            @if($productForm->productOptionGroups)
                <div class="flex flex-col gap-4">
                    @foreach($productForm->productOptionGroups as $group)
                        <div class="flex flex-col gap-1.5">
                            <h3 class="font-semibold">
                                {{ $group['name'] }}
                            </h3>
                            <div class="flex gap-1.5 flex-wrap">
                                @foreach($group['product_options'] as $option)
                                    <x-button
                                        class="whitespace-nowrap"
                                        x-bind:class="Object.values($wire.groups).includes({{ $option['id'] }}) ? 'bg-primary-500 text-white' : ''"
                                        wire:click="selectOption({{ $option['id'] }})"
                                        :label="$option['name']"
                                    />
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            @if(! is_null($productForm->parent_id) || ! $productForm->children_count)
                @section('price')
                    <div class="flex flex-col gap-1.5 text-center">
                        <div class="flex gap-4 content-center justify-center w-full">
                            <div class="text-sm font-semibold text-gray-900">{{ Number::currency($productForm->buy_price, $defaultCurrency->iso, app()->getLocale()) }} *</div>
                            @if(bccomp(data_get($productForm, 'root_discount_percentage'), 0) === 1)
                                <x-badge negative xs :label="__('%')" />
                            @endif
                        </div>
                        @if(bccomp(data_get($productForm, 'root_discount_percentage'), 0) === 1)
                            <div class="text-gray-900">
                                <span class="line-through">
                                    {{ Number::currency($productForm->root_price_flat ?? 0, $defaultCurrency->iso, app()->getLocale()) }} *
                                </span>
                                <span>
                                    {{ __('Total discount of :percentage %', ['percentage' => bcmul($productForm->root_discount_percentage, 100, 2)]) }}
                                </span>
                            </div>
                        @endif
                    </div>
                @show
                <div class="text-2xs text-secondary-400">
                    @if(auth()->user()->contact->priceList->is_net)
                        * {{ __('All prices net plus VAT') }}
                    @else
                        * {{ __('All prices gross including VAT') }}
                    @endif
                </div>
                <div
                    class="grid grid-cols-2 w-full gap-4"
                    x-data="{amount: 1}"
                >
                    <x-inputs.number step="1" x-model="amount"/>
                    <x-button x-on:click="$wire.dispatch('cart:add', {product: {id: $wire.productForm.id, name: $wire.productForm.name, price: $wire.productForm.price, amount: amount}})" primary class="w-full" :label="__('Add to cart')" />
                </div>
            @endif
            <div class="flex flex-col gap-4">
                @foreach($productForm->meta as $name => $value)
                    <x-card class="!px-0 !py-0">
                        <x-slot:title>
                            <div class="w-full font-semiboldl" x-on:click="showMeta = showMeta === '{{ $name }}' ? null : '{{ $name }}'">
                                {{ $name }}
                            </div>
                        </x-slot:title>
                        <x-slot:action>
                            <x-button icon="chevron-down" x-on:click="showMeta = showMeta === '{{ $name }}' ? null : '{{ $name }}'" />
                        </x-slot:action>
                        <div class="px-2 py-5 md:px-4" x-cloak x-show="showMeta === '{{ $name }}'" x-collapse>
                            {!! $value !!}
                        </div>
                    </x-card>
                @endforeach
                <div id="folder-tree" class="pt-3">
                    <livewire:folder-tree lazy :model-id="$productForm->id" :model-type="\FluxErp\Models\Product::class" />
                </div>
            </div>
        </div>
    </div>
    <div class="flex flex-col gap-4">
        <div class="flex gap-8">
            @foreach($productForm->product_cross_sellings ?? [] as $crossSelling)
                <div>
                    <h2 x-bind:class="showCrossSelling === {{ $crossSelling['id'] }} && 'underline'" class="text-xl font-semibold" x-on:click="showCrossSelling = {{ $crossSelling['id'] }}">
                        {{ $crossSelling['name'] }}
                    </h2>
                </div>
            @endforeach
        </div>
        @foreach($productForm->product_cross_sellings ?? [] as $crossSelling)
            <div
                x-cloak
                x-show="showCrossSelling === {{ $crossSelling['id'] }}"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4 pt-4"
            >
                @foreach($crossSelling['products'] as $product)
                    <livewire:portal.shop.product-list-card :product="$product" :key="$crossSelling['id'] . '-' .$product['id']" />
                @endforeach
            </div>
        @endforeach
    </div>
</div>
