<div>
    <x-card class="z-0 flex flex-col justify-between gap-1.5">
        <a
            href="{{ route('portal.products.show', ['product' => $productForm->id]) }}"
            class="flex flex-col justify-between"
        >
            @section('image')
            <div
                class="relative flex h-1/2 w-full justify-items-center overflow-hidden rounded-md bg-gray-200 group-hover:opacity-75 lg:h-72 xl:h-80"
            >
                @section('image.badges')
                <div class="absolute z-10 flex flex-col gap-1 p-1.5">
                    @if ($productForm->is_highlight)
                        <x-badge color="amber" :text="__('Highlight')" />
                    @endif

                    @if (bccomp(data_get($productForm, 'root_discount_percentage'), 0) === 1)
                        <x-badge color="red" :text="__('%')" />
                    @endif
                </div>
                @show
                @section('image.image')
                <img
                    src="{{ $productForm->cover_url }}"
                    alt="{{ $productForm->name }}"
                    class="z-0 w-full object-contain"
                />
                @show
            </div>
            @show
            <div class="flex h-1/2 flex-col justify-between gap-1.5">
                @section('title')
                <div class="mt-3">
                    <span class="text-secondary-400 text-xs">
                        {{ $productForm->product_number }}
                    </span>
                    <h3 class="font-semibold">
                        {{ $productForm->name }}
                    </h3>
                </div>
                <p class="mt-1 text-gray-500">
                    {!! str($productForm->description)->limit(140) !!}
                </p>
                @show
                @section('price')
                @if ($productForm->children_count === 0 && $productForm->buy_price && $productForm->root_price_flat)
                    @can(route_to_permission('portal.checkout'))
                        <div
                            class="flex flex-col gap-1.5 text-center text-gray-900 dark:text-gray-50"
                        >
                            <div class="mt-3 text-sm font-semibold">
                                {{ Number::currency(number: $productForm->buy_price, locale: app()->getLocale()) }}
                            </div>
                            @if (bccomp(data_get($productForm, 'root_discount_percentage'), 0) === 1)
                                <div>
                                    <span class="line-through">
                                        {{ Number::currency(number: $productForm->root_price_flat, locale: app()->getLocale()) }}
                                    </span>
                                    <span>
                                        {{ __('Total discount of :percentage %', ['percentage' => bcmul($productForm->root_discount_percentage, 100, 2)]) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endcan
                @else
                    <x-button
                        :text="__('View variants')"
                        color="indigo"
                        class="w-full"
                        :href="route('portal.products.show', [$productForm->id])"
                    />
                @endif
                @show
            </div>
        </a>
        @section('add-to-cart')
        @if ($productForm->children_count === 0 && $productForm->buy_price)
            @can(route_to_permission('portal.checkout'))
                <div class="mt-4 flex items-center gap-1.5">
                    @if ($cartItemId)
                        <x-number
                            step="1"
                            wire:model.live="productForm.amount"
                        />
                    @else
                        <x-number step="1" wire:model="productForm.amount" />
                    @endif
                    <x-button
                        x-on:click="$wire.$dispatch('cart:add', {products: {id: $wire.productForm.id, amount: $wire.productForm.amount}})"
                        color="indigo"
                        class="w-full"
                        :text="__('Add to cart')"
                    />
                </div>
            @endcan
        @endif

        @show
    </x-card>
</div>
