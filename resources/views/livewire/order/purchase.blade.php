@extends('flux::livewire.order.order')
@section('state-card')
    <x-card>
        <div class="space-y-3">
            <x-select
                :label="__('Authorisation user')"
                wire:model="order.approval_user_id"
                option-value="id"
                option-label="label"
                :clearable="false"
                :disabled="$order->is_locked || $order->is_confirmed || auth()->user()?->id !== $order->approval_user_id"
                :template="[
                        'name'   => 'user-option',
                    ]"
                :async-data="[
                        'api' => route('search', \FluxErp\Models\User::class),
                    ]"
            />
            <x-checkbox wire:model="order.is_confirmed" :label="__('Confirmed')" :disabled="auth()->user()?->id !== $order->approval_user_id || $order->is_locked" />
            <x-inputs.number min="1" step="1" wire:model="order.payment_target" :label="__('Payment target')" :disabled="$order->is_locked" class="w-full"/>
            <x-inputs.number min="1" step="1" wire:model="order.payment_discount_target" :label="__('Payment discount target')" :disabled="$order->is_locked" class="w-full"/>
            <x-inputs.number step="0.01" min="0.01" max="99.99" wire:model="order.payment_discount_percent" :label="__('Payment discount')" :disabled="$order->is_locked" class="w-full"/>
        </div>
    </x-card>
    @parent
@endsection
@section('content.right')
    @parent
    @section('content.right.order_dates')
        <x-input wire:model="order.invoice_number" :label="__('Invoice number')" :disabled="$order->is_locked" class="w-full"/>
        <x-datetime-picker wire:model="order.invoice_date" :clearable="false" :without-time="true" :disabled="$order->is_locked" :label="__('Invoice Date')" />
        <x-datetime-picker wire:model="order.system_delivery_date" :clearable="false" :without-time="true" :disabled="$order->is_locked" :label="__('Performance/Delivery date')" />
        <x-datetime-picker wire:model="order.system_delivery_date_end" :without-time="true" :disabled="$order->is_locked" :label="__('Performance/Delivery date end')" />
        <x-datetime-picker wire:model="order.order_date" :without-time="true" :disabled="$order->is_locked" :label="__('Order Date')" />
        <x-input wire:model="order.commission" :disabled="$order->is_locked" :label="__('Commission')" />
    @endsection
    @section('content.right.invoice_preview')
        <x-card class="space-y-3">
            <div>
                <embed class="object-contain" width="100%" height="400px" type="{{  $order->invoice['mime_type'] ?? null }}" src="{{ $order->invoice['url'] ?? null }}">
            </div>
            <x-button primary :label="__('View')" x-on:click="$openDetailModal($wire.order.invoice.url)" />
        </x-card>
    @show
@endsection
