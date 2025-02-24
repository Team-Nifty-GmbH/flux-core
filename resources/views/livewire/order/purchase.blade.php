@extends('flux::livewire.order.order')
@section('state-card')
    <x-card>
        <div class="space-y-3">
            <x-select.styled
                :label="__('Authorisation user')"
                wire:model="order.approval_user_id"
                required
                :disabled="$order->is_confirmed || (auth()->user()?->id !== $order->approval_user_id && $order->is_locked && ! is_null($order->approval_user_id))"
                :request="[
                    'url' => route('search', \FluxErp\Models\User::class),
                    'method' => 'POST',
                ]"
            />
            <x-checkbox wire:model.live="order.is_confirmed" :label="__('Confirmed')" :disabled="auth()->user()?->id !== $order->approval_user_id" />
            <x-number min="1" step="1" wire:model="order.payment_target" :label="__('Payment target')" :disabled="$order->is_locked" class="w-full"/>
            <x-number min="1" step="1" wire:model="order.payment_discount_target" :label="__('Payment discount target')" :disabled="$order->is_locked" class="w-full"/>
            <x-number step="0.01" min="0.01" max="99.99" wire:model="order.payment_discount_percent" :label="__('Payment discount')" :disabled="$order->is_locked" class="w-full"/>
        </div>
    </x-card>
    @parent
@endsection
@section('content.right')
    @parent
    @section('content.right.summary.profit')
    @endsection
    @section('content.right.order_dates')
        <x-input wire:model="order.invoice_number" :label="__('Invoice number')" :disabled="$order->is_locked" class="w-full"/>
        <x-date wire:model="order.invoice_date" required :without-time="true" :disabled="$order->is_locked" :label="__('Invoice Date')" />
        <x-date wire:model="order.system_delivery_date" required :without-time="true" :disabled="$order->is_locked" :label="__('Performance/Delivery date')" />
        <x-date wire:model="order.system_delivery_date_end" :without-time="true" :disabled="$order->is_locked" :label="__('Performance/Delivery date end')" />
        <x-date wire:model="order.order_date" :without-time="true" :disabled="$order->is_locked" :label="__('Order Date')" />
        <x-input wire:model="order.commission" :label="__('Commission')" />
    @endsection
    @section('content.right.invoice_preview')
        <x-card class="space-y-3">
            <div>
                <iframe class="object-contain" width="100%" height="400px" type="{{  $order->invoice['mime_type'] ?? null }}" src="{{ $order->invoice['url'] ?? null }}">
                </iframe>
            </div>
            <x-button color="indigo" :text="__('View')" x-on:click="$openDetailModal($wire.order.invoice.url)" />
        </x-card>
    @show
@endsection
