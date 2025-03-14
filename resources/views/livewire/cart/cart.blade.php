@extends('flux::livewire.portal.shop.cart')
@section('cart-sidebar.footer.buttons')
    @parent
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
    @endsection

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
@endsection
