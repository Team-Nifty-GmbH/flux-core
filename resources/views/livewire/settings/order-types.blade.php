<div class="py-6"
     x-data="{
        orderTypes: $wire.entangle('orderTypes'),
        orderTypeSettings: $wire.entangle('orderTypeSettings').defer,
        orderType: $wire.entangle('orderType').defer,
        }"
         >
    <x-modal.card wire:model="detailModal">
        <x-slot name="title">
{{ __('Order Type Settings') }}
</x-slot>
<template x-for="(orderType, name) in orderTypes">
    <div class="space-y-2 pb-6">
        <div class="flex space-x-1.5">
            <x-checkbox x-model="orderType.is_active"></x-checkbox>
            <div x-text="orderType.name"></div>
        </div>
    </div>
</template>
<x-slot name="footer">
    <div class="w-full">
        <div
            class="flex justify-end gap-x-4">
            <div class="flex">
                <x-button flat :label="__('Cancel')" x-on:click="close"/>
                <x-button primary :label="__('Save')" wire:click="save()"/>
            </div>
        </div>
    </div>
</x-slot>
</x-modal.card>
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold">{{ __('Order Types') }}</h1>
            <div class="mt-2 text-sm text-gray-300">{{__('Here you can manage order type settings..')}}</div>
        </div>
    </div>
    <x-table>
        <x-slot name="header">
            <th class="col-span-2">{{ __('Order Type') }}</th>
        </x-slot>
        <template x-for="(orderType, key) in orderTypeSettings">
            <x-table.row>
                <td>
                    <div x-text="key"></div>
                </td>
                <td>
                    <x-button primary :label="__('Edit')" x-on:click="$wire.show(key)" />
                </td>
            </x-table.row>
        </template>
    </x-table>
</div>
</div>
