@extends('flux::livewire.portal.shop.cart')
@section('cart-sidebar.footer.buttons')
    @parent
    @section('cart-sidebar.footer.buttons.buy')
        <x-button wire:click="addToCurrentOrder()" color="indigo" class="w-full" :text="__('Add to current order')"/>
        <x-button color="secondary" light
            :text="__('Clear cart')"
            wire:click="clear()"
            wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Cart Items')]) }}"
            negative
            class="w-full"
        />
    @endsection
    <x-select.styled
        class="w-full"
        :text="__('Load a watchlist')"
        select="label:name|value:id"
        :options="array_filter($watchlists, fn (array $watchlist) => $watchlist['id'] ?? false)"
        wire:model.live.numeric="loadWatchlist"
    />
    <x-button
        :text="__('Edit watchlists')"
        :href="route('watchlists')"
        wire:navigate
        x-on:click="show = false"
        color="indigo"
        class="w-full"
    />
@endsection
