<div
    class="flex h-full flex-col gap-8 text-gray-900 dark:text-gray-50"
    x-data="{
        showMeta: null,
        showCrossSelling:
            {{ $productForm->product_cross_sellings[0]['id'] ?? 'null' }},
        showMedia: null,
    }"
>
    <x-spinner />
    <div class="relative grid h-full gap-8 sm:flex">
        <div class="flex w-full justify-items-center gap-4 sm:w-1/2">
            <div class="w-full">
                <div class="w-full">
                    <img
                        src="{{ route('icons', ['name' => 'photo']) }}"
                        x-bind:src="$wire.productForm.cover_url"
                        alt="{{ $productForm->name }}"
                        class="max-h-96 w-full rounded-lg object-contain"
                    />
                </div>
                <div class="flex justify-start gap-2">
                    @foreach ($productForm->media as $media)
                        <div
                            class="flex-none rounded-md"
                            x-bind:class="
                                $wire.productForm.cover_url === '{{ $media }}' &&
                                    'ring-2 ring-offset-2 ring-primary-500'
                            "
                            x-on:click="$wire.productForm.cover_url = '{{ $media }}'"
                        >
                            <x-avatar
                                xl
                                squared
                                :image="$media"
                                class="w-full"
                            />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="flex w-full flex-col gap-8 sm:w-1/2">
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
            @if ($productForm->productOptionGroups)
                <div class="flex flex-col gap-4">
                    @foreach ($productForm->productOptionGroups as $group)
                        <div class="flex flex-col gap-1.5">
                            <h3 class="font-semibold">
                                {{ $group['name'] }}
                            </h3>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($group['product_options'] as $option)
                                    <x-button
                                        color="secondary"
                                        light
                                        class="whitespace-nowrap"
                                        x-bind:class="Object.values($wire.groups).includes({{ $option['id'] }}) ? 'bg-indigo-500 text-white' : ''"
                                        wire:click="selectOption({{ $option['id'] }})"
                                        :text="$option['name']"
                                    />
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if (! is_null($productForm->parent_id) || ! $productForm->children_count)
                @can(route_to_permission('portal.checkout'))
                    @section('price')
                    <div class="flex flex-col gap-1.5 text-center">
                        <div
                            class="flex w-full content-center justify-center gap-4"
                        >
                            <div class="text-sm font-semibold">
                                {{ Number::currency(number: $productForm->buy_price ?? 0, locale: app()->getLocale()) }}
                                *
                            </div>
                            @if (bccomp(data_get($productForm, 'root_discount_percentage'), 0) === 1)
                                <x-badge color="red" xs :text="__('%')" />
                            @endif
                        </div>
                        @if (bccomp(data_get($productForm, 'root_discount_percentage'), 0) === 1)
                            <div>
                                <span class="line-through">
                                    {{ Number::currency(number: $productForm->root_price_flat ?? 0, locale: app()->getLocale()) }}
                                    *
                                </span>
                                <span>
                                    {{ __('Total discount of :percentage %', ['percentage' => bcmul($productForm->root_discount_percentage, 100, 2)]) }}
                                </span>
                            </div>
                        @endif
                    </div>
                    @show
                    <div class="text-secondary-400 text-2xs">
                        @if (auth()->user()?->priceList?->is_net)
                            * {{ __('All prices net plus VAT') }}
                        @else
                            * {{ __('All prices gross including VAT') }}
                        @endif
                    </div>
                    <div
                        class="grid w-full grid-cols-2 gap-4"
                        x-data="{ amount: 1 }"
                    >
                        <x-number step="1" x-model="amount" />
                        <x-button
                            x-on:click="$wire.dispatch('cart:add', {products: {id: $wire.productForm.id, name: $wire.productForm.name, price: $wire.productForm.price, amount: amount}})"
                            color="indigo"
                            class="w-full"
                            :text="__('Add to cart')"
                        />
                    </div>
                @endcan
            @endif

            <div class="flex flex-col gap-4">
                @foreach ($productForm->meta as $name => $value)
                    <x-card class="!px-0 !py-0">
                        <x-slot:title>
                            <div
                                class="font-semiboldl w-full"
                                x-on:click="showMeta = showMeta === '{{ $name }}' ? null : '{{ $name }}'"
                            >
                                {{ $name }}
                            </div>
                        </x-slot>
                        <x-slot:header>
                            <x-button
                                color="secondary"
                                light
                                icon="chevron-down"
                                x-on:click="showMeta = showMeta === '{{ $name }}' ? null : '{{ $name }}'"
                            />
                        </x-slot>
                        <div
                            class="px-2 py-5 md:px-4"
                            x-cloak
                            x-show="showMeta === '{{ $name }}'"
                            x-collapse
                        >
                            {!! $value !!}
                        </div>
                    </x-card>
                @endforeach

                @if ($productForm->bundle_products)
                    <x-card :header="__('Bundle Products')">
                        @foreach ($productForm->bundle_products as $bundleProduct)
                            <a
                                href="{{ route('portal.products.show', [$bundleProduct['id']]) }}"
                            >
                                <div class="flex gap-4">
                                    <div class="flex flex-col gap-1.5">
                                        <h3 class="font-semibold">
                                            {{ bcround($bundleProduct['count'] ?? 1) }}
                                            x {{ $bundleProduct['name'] }}
                                        </h3>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </x-card>
                @endif

                @if ($productForm->additionalMedia)
                    <h2 class="font-semibold">
                        {{ __('Additional Media') }}
                    </h2>
                    @foreach ($productForm->additionalMedia ?? [] as $collection => $media)
                        <x-card class="!px-0 !py-0">
                            <x-slot:title>
                                <div
                                    class="font-semiboldl w-full"
                                    x-on:click="showMedia = showMedia === '{{ $collection }}' ? null : '{{ $collection }}'"
                                >
                                    {{ __($collection) }}
                                </div>
                            </x-slot>
                            <x-slot:header>
                                <div class="flex justify-end gap-1.5">
                                    <x-button
                                        :text="__('Download folder')"
                                        color="indigo"
                                        icon="save"
                                        wire:click="downloadMedia({{ \Illuminate\Support\Js::from(array_keys($media)) }}, '{{ $collection }}')"
                                    />
                                    <x-button
                                        color="secondary"
                                        light
                                        icon="chevron-down"
                                        x-on:click="showMedia = showMedia === '{{ $collection }}' ? null : '{{ $collection }}'"
                                    />
                                </div>
                            </x-slot>
                            <div
                                class="flex flex-col px-2 py-5 md:px-4"
                                x-cloak
                                x-show="showMedia === '{{ $collection }}'"
                                x-collapse
                            >
                                @foreach ($media as $item)
                                    <div
                                        wire:click="downloadMedia({{ $item['id'] }})"
                                        class="cursor-pointer"
                                    >
                                        {{ $item['name'] }}
                                    </div>
                                @endforeach
                            </div>
                        </x-card>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
    <div class="flex flex-col gap-4">
        <div class="flex gap-8">
            @foreach ($productForm->product_cross_sellings ?? [] as $crossSelling)
                <div>
                    <h2
                        x-bind:class="showCrossSelling === {{ $crossSelling['id'] }} && 'underline'"
                        class="cursor-pointer text-xl font-semibold"
                        x-on:click="showCrossSelling = {{ $crossSelling['id'] }}"
                    >
                        {{ $crossSelling['name'] }}
                    </h2>
                </div>
            @endforeach
        </div>
        @foreach ($productForm->product_cross_sellings ?? [] as $crossSellingProducts)
            <div
                x-cloak
                x-show="showCrossSelling === {{ $crossSellingProducts['id'] }}"
                class="grid grid-cols-1 gap-4 pt-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6"
            >
                @foreach ($crossSellingProducts['products'] as $product)
                    <livewire:portal.shop.product-list-card
                        :product="$product"
                        :key="$crossSelling['id'] . '-' . $product['id']"
                    />
                @endforeach
            </div>
        @endforeach
    </div>
</div>
