<div x-data="{ show: false, showWatchlist: false }">
    @section('icon')
    <x-button
        color="secondary"
        light
        rounded
        :text="count($this->cart->cartItems)"
        primary
        icon="shopping-cart"
        x-on:click="show = true"
    />
    @show
    @section('cart-sidebar')
    @teleport('body')
        <x-flux::sidebar x-show="show">
            <div class="flex flex-col gap-4 text-gray-900 dark:text-gray-50">
                @section('cart-sidebar.header')
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    {{ __('Cart :item_count positions', ['item_count' => count($this->cart->cartItems)]) }}
                </h1>
                @show
                @section('cart-sidebar.content')
                @foreach ($this->cart?->cartItems ?? [] as $key => $cartItem)
                    <x-flux::shop.cart-item
                        :cartItem="$cartItem"
                        :key="$cartItem->id"
                    />
                @endforeach

                @show
                <hr />
                @section('cart-sidebar.total')
                <div class="flex justify-between gap-2 font-semibold">
                    <div>{{ __('Total') }}</div>
                    <div>
                        {{ Number::currency(number: $this->cart->cart_items_sum_total ?? 0, locale: app()->getLocale()) }}
                        *
                    </div>
                </div>
                <div class="text-2xs text-secondary-400">
                    @if (auth()->user()?->priceList?->is_net || resolve_static(\FluxErp\Models\PriceList::class, 'default')?->is_net)
                        * {{ __('All prices net plus VAT') }}
                    @else
                        * {{ __('All prices gross including VAT') }}
                    @endif
                </div>
                @show
            </div>
            <x-slot:footer>
                @section('cart-sidebar.footer')
                <div class="flex w-full flex-col gap-2">
                    @section('cart-sidebar.footer.buttons')
                    <x-button
                        color="secondary"
                        light
                        class="w-full"
                        :text="__('Close')"
                        x-on:click="show = false;"
                    />
                    @if ($this->cart->cartItems->isNotEmpty())
                        @section('cart-sidebar.footer.buttons.buy')
                        <x-button
                            wire:click="addToCurrentOrder()"
                            color="indigo"
                            class="w-full"
                            :text="__('Add to current order')"
                        />
                        <x-button
                            light
                            :text="__('Clear cart')"
                            wire:click="clear()"
                            wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Cart Items')]) }}"
                            color="red"
                            class="w-full"
                        />
                        @show
                        @section('cart-sidebar.footer.buttons.watchlist')
                        <x-button
                            color="secondary"
                            light
                            class="w-full"
                            icon="chevron-down"
                            :text="__('Add items to watchlist')"
                            x-on:click="showWatchlist = ! showWatchlist"
                        />
                        <div
                            x-cloak
                            x-show="showWatchlist"
                            x-collapse
                            class="flex flex-col gap-1.5 pt-4"
                        >
                            <x-select.styled
                                class="w-full"
                                required
                                :label="__('Select a watchlist')"
                                wire:model="selectedWatchlist"
                                select="label:name|value:id"
                                :options="$watchlists"
                            />
                            <div
                                x-cloak
                                x-show="$wire.selectedWatchlist === 0"
                            >
                                <x-input
                                    class="w-full"
                                    :label="__('Watchlist Name')"
                                    wire:model="watchlistName"
                                />
                            </div>
                            <x-button
                                color="indigo"
                                wire:click="saveToWatchlist().then((success) => {if (success) showWatchlist = false;})"
                                :text="__('Save to watchlist')"
                                class="w-full"
                            />
                        </div>
                        @show
                    @endif

                    <x-select.styled
                        class="w-full"
                        :label="__('Load a watchlist')"
                        wire:model.live.numeric="loadWatchlist"
                        select="label:name|value:id"
                        :options="array_filter($watchlists, fn (array $watchlist) => $watchlist['id'] ?? false)"
                    />
                    <x-button
                        :text="__('Edit watchlists')"
                        :href="route('watchlists')"
                        wire:navigate
                        x-on:click="show = false"
                        color="indigo"
                        class="w-full"
                    />

                    @show
                </div>
                @show
            </x-slot>
        </x-flux::sidebar>
    @endteleport

    @show
</div>
