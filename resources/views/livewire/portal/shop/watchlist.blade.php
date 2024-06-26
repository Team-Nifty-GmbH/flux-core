<div class="flex flex-col gap-8" x-data="{showCart: null}">
    @forelse($carts as $cart)
        <x-card class="!px-0 !py-0">
            <x-slot:title>
                <div class="w-full font-semiboldl" x-on:click="showCart = showCart === {{ $cart->id }} ? null : {{ $cart->id }}">
                    {{ $cart->name }}
                </div>
            </x-slot:title>
            <x-slot:action>
                <x-button icon="chevron-down" x-on:click="showCart = showCart === {{ $cart->id }} ? null : {{ $cart->id }}" />
            </x-slot:action>
            <div class="flex gap-4 px-2 py-5 md:px-4" x-cloak x-show="showCart === {{ $cart->id }}" x-collapse>
                @foreach($cart->products as $cartItem)
                    <div class="max-w-96 relative z-0">
                        @if(! $cart->is_portal_public)
                            <x-button.circle
                                xs
                                negative
                                icon="x"
                                wire:click="removeProduct({{ $cart->id }}, {{ $cartItem['id'] }})"
                                class="absolute right-2 top-2 h-4 w-4 z-10"
                            />
                        @endif
                        <livewire:portal.shop.product-list-card
                            :product="$cartItem"
                            :key="$cart->id . '_' . $cartItem['id']"
                        />
                    </div>
                @endforeach
            </div>
            <x-slot:footer>
                <div class="flex justify-end gap-1.5">
                    @if(! $cart->is_portal_public)
                        <x-button
                            negative
                            wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Watchlist')]) }}"
                            wire:click="deleteCart({{ $cart->id }})"
                            :label="__('Delete')"
                        />
                    @endif
                    <x-button
                        primary
                        wire:click="addToCart({{ $cart->id }})"
                        :label="__('Add products to cart')"
                    />
                </div>
            </x-slot:footer>
        </x-card>
    @empty
        <div class="flex flex-col gap-8 justify-center">
            <div>
                <h1 class="pt-5 pb-10 text-5xl font-bold text-center">
                    {{ __('Your watchlist is empty') }}
                </h1>
            </div>
        </div>
    @endforelse
</div>
