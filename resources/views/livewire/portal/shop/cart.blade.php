<div x-data="{show: false, showWatchlist: false}">
    @section('icon')
        <x-button :label="count($this->cart->cartItems)" primary icon="shopping-cart" s x-on:click="show = true"/>
    @show
    @section('cart-sidebar')
        <x-flux::sidebar x-show="show">
            <div class="flex flex-col gap-4">
                @section('cart-sidebar.header')
                    <h2 class="text-lg font-bold">{{ __('Cart :item_count positions', ['item_count' => count($this->cart->cartItems)]) }}</h2>
                @show
                @section('cart-sidebar.content')
                    @foreach($this->cart?->cartItems ?? [] as $key => $cartItem)
                        <x-flux::shop.cart-item :cartItem="$cartItem" :key="$cartItem->id"/>
                    @endforeach
                @show
                <hr>
                @section('cart-sidebar.total')
                    <div class="flex justify-between gap-2 font-semibold">
                        <div>{{ __('Total') }}</div>
                        <div>{{ Number::currency($this->cart->cart_items_sum_total ?? 0, $defaultCurrency->iso, app()->getLocale()) }} *</div>
                    </div>
                    <div class="text-2xs text-secondary-400">
                        @if(auth()->user()->contact->priceList->is_net)
                            * {{ __('All prices net plus VAT') }}
                        @else
                            * {{ __('All prices gross including VAT') }}
                        @endif
                    </div>
                @show
            </div>
            @if($this->cart->cartItems->isNotEmpty())
                <x-slot:footer>
                    @section('cart-sidebar.footer')
                        <div class="flex flex-col gap-1.5 w-full">
                            <x-button
                                class="w-full"
                                :label="__('Checkout')"
                                wire:navigate
                                x-on:click="show = false;"
                                :href="route('portal.checkout')"
                                primary
                            />
                            <x-button
                                class="w-full"
                                :label="__('Add items to watchlist')"
                                x-on:click="showWatchlist = ! showWatchlist"
                            />
                            <div x-cloak x-show="showWatchlist" x-collapse class="flex flex-col gap-1.5 pt-4">
                                <x-select
                                    class="w-full"
                                    :label="__('Select a watchlist')"
                                    option-label="name"
                                    option-value="id"
                                    :options="$watchlists"
                                    wire:model="selectedWatchlist"
                                />
                                <div x-cloak x-show="$wire.selectedWatchlist === 0">
                                    <x-input class="w-full" :label="__('Watchlist Name')" wire:model="watchlistName"/>
                                </div>
                                <x-button primary wire:click="saveToWatchlist().then((success) => {if (success) showWatchlist = false;})" :label="__('Save to watchlist')" class="w-full"/>
                            </div>
                        </div>
                    @show
                </x-slot:footer>
            @endif
        </x-flux::sidebar>
    @show
</div>
