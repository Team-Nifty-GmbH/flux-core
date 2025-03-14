<div
    x-data="{
        additionalColumns: $wire.entangle('additionalColumns'),
        edit: false,
        priceLists: $wire.entangle('priceLists'),
    }"
>
    <div
        class="mx-auto md:flex md:items-center md:justify-between md:space-x-5"
    >
        <div class="flex items-center space-x-5">
            <x-avatar xl :image="$product->avatar_url ?? ''"></x-avatar>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    <div class="flex">
                        <div class="pl-2">
                            <span x-text="$wire.product.name"></span>
                            <span
                                class="opacity-40 transition-opacity hover:opacity-100"
                                x-text="$wire.product.product_number"
                            ></span>
                        </div>
                    </div>
                </h1>
                <a
                    wire:navigate
                    class="flex gap-1.5 font-semibold opacity-40 dark:text-gray-200"
                    x-bind:href="$wire.product.parent?.url"
                    x-cloak
                    x-show="$wire.product.parent?.url"
                >
                    <x-icon name="link" class="h-4 w-4" />
                    <span x-text="$wire.product.parent?.label"></span>
                </a>
            </div>
        </div>
        <div
            class="mt-6 flex flex-col-reverse justify-stretch space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-x-3 sm:space-y-0 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3"
        >
            @if (resolve_static(\FluxErp\Actions\CartItem\CreateCartItem::class, 'canPerformAction', [false]) && ! $product->children_count > 0)
                <x-button
                    x-on:click="$wire.$dispatch('cart:add', {products: $wire.product.id})"
                    color="indigo"
                    icon="shopping-cart"
                    label="+"
                />
            @endif

            @canAction(\FluxErp\Actions\Product\DeleteProduct::class)
                <x-button
                    color="red"
                    :text="__('Delete') "
                    wire:click="delete()"
                    wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Product')]) }}"
                />
            @endcanAction

            @canAction(\FluxErp\Actions\Product\UpdateProduct::class)
                <x-button
                    color="indigo"
                    x-show="!edit"
                    class="w-full"
                    x-on:click="edit = true"
                    :text="__('Edit')"
                />
                <x-button
                    x-cloak
                    color="indigo"
                    x-show="edit"
                    class="w-full"
                    x-on:click="$wire.save().then((success) => {
                        edit = false;
                    });"
                    :text="__('Save')"
                />
                <x-button
                    x-cloak
                    color="indigo"
                    x-show="edit"
                    class="w-full"
                    x-on:click="edit = false; $wire.resetProduct()"
                    :text="__('Cancel')"
                />
            @endcanAction
        </div>
    </div>
    <x-flux::tabs wire:model.live="tab" wire:loading="tab" :$tabs wire:ignore />
</div>
