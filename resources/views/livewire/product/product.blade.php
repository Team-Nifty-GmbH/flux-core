<div x-data="{
        product: $wire.entangle('product'),
        additionalColumns: $wire.entangle('additionalColumns'),
        edit: false,
        priceLists: $wire.entangle('priceLists'),
        currency: $wire.entangle('currency'),
        vatRates: $wire.entangle('vatRates')
    }"
>
    <div
        class="mx-auto md:flex md:items-center md:justify-between md:space-x-5">
        <div class="flex items-center space-x-5">
            <x-avatar xl :src="$product['avatar_url'] ?? ''"></x-avatar>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    <div class="flex">
                        <x-heroicons x-show="product.is_locked" variant="solid" name="lock-closed" />
                        <x-heroicons x-show="! product.is_locked" variant="solid" name="lock-open" />
                        <div class="pl-2">
                            <span x-text="product.name">
                            </span>
                            <span class="opacity-40 transition-opacity hover:opacity-100" x-text="product.product_number">
                            </span>
                        </div>
                    </div>
                </h1>
                <a class="flex gap-1.5 font-semibold opacity-40" x-bind:href="product.parent?.url" x-show="product.parent?.url">
                    <x-heroicons name="link" class="w-4 h-4" />
                    <span x-text="product.parent?.label">
                    </span>
                </a>
            </div>
        </div>
        <div class="justify-stretch mt-6 flex flex-col-reverse space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3">
            @if(user_can('api.product.{id}.delete') && $product['id'] && ! $product['is_locked'])
                <x-button negative label="{{ __('Delete') }}" x-on:click="
                              window.$wireui.confirmDialog({
                              title: '{{ __('Delete product') }}',
                    description: '{{ __('Do you really want to delete this product?') }}',
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
            @endif
            <x-button
                primary
                x-show="!edit"
                class="w-full"
                x-on:click="edit = true"
                :label="__('Edit')"
            />
            <x-button
                x-cloak
                primary
                x-show="edit"
                class="w-full"
                x-on:click="$wire.save().then((success) => {
                    edit = false;
                });"
                :label="__('Save')"
            />
            <x-button
                x-cloak
                primary
                x-show="edit"
                class="w-full"
                x-on:click="edit = false"
                :label="__('Cancel')"
            />
        </div>
    </div>
    <x-tabs
        wire:model.live="tab"
        :$tabs
        wire:ignore
    />
</div>
