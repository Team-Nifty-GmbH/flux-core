<div
    class="dark:text-white"
    x-data="{
        serialNumber: @entangle('serialNumber'),
        productImage: $wire.entangle('productImage'),
        edit: $wire.entangle('edit'),
    }
">
    <!-- Page header -->
    <div class="mx-auto px-4 sm:px-6 md:flex md:items-center md:justify-between md:space-x-5 lg:px-8">
        <div class="flex items-center space-x-5">
            <label for="avatar" style="cursor: pointer">
                <x-avatar xl :label="$productImage === '' ? strtoupper(substr($serialNumber['id'] ?? '', 0, 2)) : false" src="{{ $productImage }}" />
            </label>
            <input type="file" accept="image/*" id="avatar" class="hidden" wire:model.live="avatar"/>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    <div class="opacity-40 transition-opacity hover:opacity-100">
                        {{ $serialNumber['product']['name'] ?? '' }}
                    </div>
                    <span x-text="serialNumber.serial_number ?? '{{ __('New') }}'"></span>
                </h1>
            </div>
        </div>
        <div
            class="justify-stretch mt-6 flex flex-col-reverse space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3">
            @if(user_can('action.serial-number.delete') && ($serialNumber['id'] ?? false))
                <x-button negative label="{{ __('Delete') }}" @click="
                              window.$wireui.confirmDialog({
                              title: '{{ __('Delete serial-number') }}',
                    description: '{{ __('Do you really want to delete this serial-number?') }}',
                    icon: 'error',
                    accept: {
                        label: '{{ __('Delete') }}',
                        method: 'delete',
                    },
                    reject: {
                        label: '{{ __('Cancel') }}',
                    }
                    }, $wire.__instance.id)
                    "/>
            @endcan
            @if(user_can('action.serial-number.create') && ($serialNumber['id'] ?? false))
                <x-button primary label="{{ __('New') }}" x-on:click="$wire.new()"/>
            @endcan
            @can('action.serial-number.update')
                <template x-if="serialNumber.id && edit === false">
                    <x-button primary label="{{ __('Edit') }}" x-on:click="$wire.startEdit()"/>
                </template>
                <template x-if="edit === true">
                    <div>
                        <x-button primary label="{{ __('Save') }}" x-on:click="$wire.save()"/>
                        <x-button label="{{ __('Cancel') }}" x-on:click="$wire.cancel()"/>
                    </div>
                </template>
            @endcan
        </div>
    </div>
    <x-tabs
        wire:model.live="tab"
        :tabs="[
                    'general' => __('General'),
                    'comments' => __('Comments'),
                ]"
    >
    </x-tabs>
    <div class="relative mx-auto">
        <div wire:loading wire:ignore class="absolute right-0 top-0 left-0 bottom-0 bg-white/30 backdrop-blur-sm" style="z-index: 1">
            <div class="absolute right-0 top-0 left-0 bottom-0 flex items-center justify-center">
                <x-spinner />
            </div>
        </div>
        <x-dynamic-component :component="'product.serial-number.' . $tab"/>
    </div>
</div>
