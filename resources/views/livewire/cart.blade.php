@extends('flux::livewire.portal.shop.cart')
@section('cart-sidebar.footer.buttons')
    @parent
    @section('cart-sidebar.footer.buttons.buy')
        <x-button wire:click="addToCurrentOrder()" primary class="w-full" :label="__('Add to current order')"/>
        <x-button
            :label="__('Clear cart')"
            wire:click="clear()"
            wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Cart Items')]) }}"
            negative
            class="w-full"
        />
    @endsection
    <x-select
        class="w-full"
        :label="__('Load a watchlist')"
        option-label="name"
        option-value="id"
        :options="array_filter($watchlists, fn(array $watchlist) => $watchlist['id'] ?? false)"
        wire:model.live.numeric="loadWatchlist"
    />
@endsection
