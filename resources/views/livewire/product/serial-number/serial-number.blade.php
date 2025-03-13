<div
    class="dark:text-white"
    x-data="{
        productImage: $wire.entangle('productImage'),
    }"
>
    <!-- Page header -->
    <div
        class="mx-auto px-4 sm:px-6 md:flex md:items-center md:justify-between md:space-x-5 lg:px-8"
    >
        <div class="flex items-center space-x-5">
            <label for="avatar">
                <x-avatar
                    xl
                    :label="$productImage === '' ? strtoupper(substr($serialNumber->id ?? '', 0, 2)) : false"
                    :image="$productImage"
                />
            </label>
            <input
                type="file"
                accept="image/*"
                id="avatar"
                class="hidden"
                wire:model.live="productImage"
                disabled
            />
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    <div
                        class="opacity-40 transition-opacity hover:opacity-100"
                        x-text="$wire.serialNumber.product.name"
                    ></div>
                    <span x-text="$wire.serialNumber.serial_number"></span>
                </h1>
            </div>
        </div>
        <div
            class="mt-6 flex flex-col-reverse justify-stretch space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-x-3 sm:space-y-0 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3"
        >
            @can("action.serial-number.update")
                <template x-if="serialNumber.id && $wire.edit === false">
                    <x-button
                        color="indigo"
                        :text="__('Edit') "
                        x-on:click="$wire.startEdit()"
                    />
                </template>
                <template x-if="$wire.edit === true">
                    <div>
                        <x-button
                            color="indigo"
                            :text="__('Save') "
                            x-on:click="$wire.save()"
                        />
                        <x-button
                            color="secondary"
                            light
                            :text="__('Cancel') "
                            x-on:click="$wire.cancel()"
                        />
                    </div>
                </template>
            @endcan
        </div>
    </div>
    <x-flux::tabs wire:model.live="tab" :$tabs />
</div>
