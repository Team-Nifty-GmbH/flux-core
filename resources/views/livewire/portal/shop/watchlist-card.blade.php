<x-card class="!px-0 !py-0">
    <x-slot:title>
        <div class="w-full font-semibold" x-on:click="showCart = showCart === {{ $cartForm->id ?? 'null' }} ? null : {{ $cartForm->id }}">
            {{ $cartForm->name }}
        </div>
    </x-slot:title>
    <x-slot:header>
        <x-button color="secondary" light icon="chevron-down" x-on:click="showCart = showCart === {{ $cartForm->id ?? 'null' }} ? null : {{ $cartForm->id ?? 'null' }}" />
    </x-slot:header>
    <div
        class="flex gap-4 px-2 py-5 md:px-4"
         @if($cartForm->isUserOwned()) x-sort="$wire.reOrder($item, $position);" @endif
        x-cloak
        x-show="showCart === {{ $cartForm->id ?? 'null' }}"
        x-collapse
    >
        @foreach($cartForm->cart_items ?? [] as $cartFormItem)
            @if(is_null($cartFormItem))
                @continue
            @endif
            <div
                class="max-w-96 relative z-0"
                @if($cartForm->isUserOwned()) x-sort:item="{{ $cartFormItem['cart_item_id'] }}" @endif
            >
                @if($cartForm->isUserOwned())
                    <x-button.circle
                        xs
                        color="red"
                        icon="x-mark"
                        wire:click="removeProduct({{ $cartFormItem['id'] }})"
                        class="absolute right-2 top-2 h-4 w-4 z-10"
                    />
                @endif
                <livewire:portal.shop.product-list-card
                    :product="$cartFormItem"
                    :cart-item-id="$cartForm->isUserOwned() ? $cartFormItem['cart_item_id'] : null"
                    :key="$cartForm->id . '_' . $cartFormItem['id']"
                />
            </div>
        @endforeach
    </div>
    @if(auth()->user()?->getMorphClass() === 'user' && $cartForm->isUserOwned())
        <hr>
        <div class="p-4">
            <x-toggle :id="uniqid()" :text="__('Is Public')" wire:model.live="cartForm.is_public" />
            <x-toggle :id="uniqid()" :label="__('Is Portal Public')" wire:model.live="cartForm.is_portal_public" />
        </div>
    @endif
    <x-slot:footer>
        <div class="flex justify-end gap-1.5">
            @if($cartForm->isUserOwned())
                <x-button
                    color="red"
                    wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Watchlist')]) }}"
                    wire:click="delete()"
                    :text="__('Delete')"
                />
            @endif
            <x-button
                color="indigo"
                wire:click="addToCart()"
                :text="__('Add products to cart')"
            />
        </div>
    </x-slot:footer>
</x-card>
