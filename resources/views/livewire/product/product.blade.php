<div x-data="{
        additionalColumns: $wire.entangle('additionalColumns'),
        edit: false,
        priceLists: $wire.entangle('priceLists')
    }"
>
    <div
        class="mx-auto md:flex md:items-center md:justify-between md:space-x-5">
        <div class="flex items-center space-x-5">
            <x-avatar xl :src="$product->avatar_url ?? ''"></x-avatar>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    <div class="flex">
                        <x-heroicons x-cloak x-show="$wire.product.is_locked" variant="solid" name="lock-closed" />
                        <x-heroicons x-cloak x-show="! $wire.product.is_locked" variant="solid" name="lock-open" />
                        <div class="pl-2">
                            <span x-text="$wire.product.name">
                            </span>
                            <span class="opacity-40 transition-opacity hover:opacity-100" x-text="$wire.product.product_number">
                            </span>
                        </div>
                    </div>
                </h1>
                <a wire:navigate class="flex gap-1.5 font-semibold opacity-40 dark:text-gray-200" x-bind:href="$wire.product.parent?.url" x-cloak x-show="$wire.product.parent?.url">
                    <x-heroicons name="link" class="w-4 h-4" />
                    <span x-text="$wire.product.parent?.label">
                    </span>
                </a>
            </div>
        </div>
        <div class="justify-stretch mt-6 flex flex-col-reverse space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3">
            @canAction(\FluxErp\Actions\CartItem\CreateCartItem::class)
                <x-button
                    x-on:click="$wire.$dispatch('cart:add', {products: $wire.product.id})"
                    primary
                    icon="shopping-cart"
                    label="+"
                />
            @endCanAction
            @canAction(\FluxErp\Actions\Product\DeleteProduct::class)
                <x-button
                    negative
                    label="{{ __('Delete') }}"
                    wire:click="delete()"
                    wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Product')]) }}"
                />
            @endCanAction
            @canAction(\FluxErp\Actions\Product\UpdateProduct::class)
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
            @endCanAction
        </div>
    </div>
    <x-tabs
        wire:model.live="tab"
        wire:loading="tab"
        :$tabs
        wire:ignore
    />
</div>
