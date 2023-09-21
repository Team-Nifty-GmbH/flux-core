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
