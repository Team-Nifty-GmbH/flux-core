@extends('flux::livewire.order.order')
@section('modals')
    @parent
    <div id="invoice-view">
        <x-modal max-width="7xl" fullscreen="true">
            <x-card class="grid h-screen">
                <embed class="object-contain" height="100%" width="100%" type="{{  $order['invoice']['mime_type'] ?? null }}" src="{{ $order['invoice']['original_url'] ?? null }}">
            </x-card>
        </x-modal>
    </div>
@endsection
@section('state-card')
    <x-card>
        <div class="space-y-3">
            <x-select
                :label="__('Authorisation user')"
                wire:model="order.approval_user_id"
                option-value="id"
                option-label="label"
                :clearable="false"
                :disabled="$order['is_locked'] || $order['is_confirmed'] || auth()->user()?->id !== $order['approval_user_id']"
                :template="[
                        'name'   => 'user-option',
                    ]"
                :async-data="[
                        'api' => route('search', \FluxErp\Models\User::class),
                    ]"
            />
            <x-checkbox x-model="order.is_confirmed" :label="__('Confirmed')" :disabled="auth()->user()?->id !== $order['approval_user_id'] || $order['is_locked']" />
            <x-inputs.number min="1" step="1" wire:model="order.payment_target" :label="__('Payment target')" :disabled="$order['is_locked']" class="w-full"/>
            <x-inputs.number min="1" step="1" wire:model="order.payment_discount_target" :label="__('Payment discount target')" :disabled="$order['is_locked']" class="w-full"/>
            <x-inputs.number step="0.01" min="0.01" max="99.99" wire:model="order.payment_discount_percent" :label="__('Payment discount')" :disabled="$order['is_locked']" class="w-full"/>
            <x-select wire:model="order.bank_connection_id"
                  :label="__('Bank connection')"
                  :disabled="$order['is_locked']" class="w-full"
                  :options="$order['contact']['bank_connections'] ?? []"
                  option-value="id"
                  option-label="iban"
          />
        </div>
    </x-card>
    @parent
@endsection
@section('content.right')
    @parent
    @section('content.right.order_dates')
        <x-input wire:model="order.invoice_number" :label="__('Invoice number')" :disabled="$order['is_locked']" class="w-full"/>
        @parent
    @endsection
    @section('content.right.invoice_preview')
        <x-card class="space-y-3">
            <div>
                <embed class="object-contain" width="100%" height="400px" type="{{  $order['invoice']['mime_type'] ?? null }}" src="{{ $order['invoice']['original_url'] ?? null }}">
            </div>
            <x-button primary :label="__('View')" x-on:click="Alpine.$data(document.getElementById('invoice-view').querySelector('[wireui-modal]')).open()" />
        </x-card>
    @show
@endsection
