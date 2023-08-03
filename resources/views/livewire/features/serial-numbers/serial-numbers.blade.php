<div class="px-4 pt-8 sm:px-6 lg:px-8"
     x-data="{
        serialNumber: @entangle('serialNumber').defer,
        serialNumbers: @entangle('serialNumbers').defer,
    }"
>
    <x-modal.card z-index="z-30" title="{{ __('Edit serial number') }}" blur wire:model.defer="cardModal">
        <div class="grid grid-cols-1 gap-4">
            <x-input autofocus label="{{ __('Serial number') }}"
                     placeholder="{{ __('Enter new or chose an existing…') }}"
                     x-model="serialNumber.serial_number"/>
            <x-features.search.search-bar x-on:click="$wire.set('serialNumber.product_id', result.id);"
                                          searchResultComponent="features.search.product">
                <template x-if="serialNumber.product">
                    <div class="col-span-1 flex rounded-md pt-4 shadow-sm">
                        <div
                            class="flex flex-1 items-center justify-between truncate rounded-r-md border-t border-r border-b border-gray-200 bg-white">
                            <div class="flex-1 truncate px-4 py-2 text-sm">
                                <a href="#"
                                   class="font-medium text-gray-900 hover:text-gray-600" x-text="serialNumber.product.product_number"></a>
                                <p class="text-gray-500" x-text="serialNumber.product.name"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </x-features.search.search-bar>
            <x-additional-columns :model="\FluxErp\Models\SerialNumber::class" :id="$serialNumber['id'] ?? null" wire="serialNumber"/>
            <x-errors />
            @if($serialNumber['id'] ?? false)
                <livewire:folder-tree wire:key="{{ uniqid() }}" :model-type="\FluxErp\Models\SerialNumber::class" :model-id="$serialNumber['id'] ?? null" />
            @endif
        </div>
        <x-slot name="footer">
            <div class="flex justify-between gap-x-4">
                @can('action.serial-number.delete')
                    <x-button flat negative label="{{ __('Delete') }}" @click="
                                                        window.$wireui.confirmDialog({
                                                            title: '{{ __('Delete serial number') }}',
                                                            description: '{{ __('Do you really want to delete this serial number?') }}',
                                                            icon: 'error',
                                                            accept: {
                                                                label: '{{ __('Delete') }}',
                                                                method: 'delete',
                                                            },
                                                            reject: {
                                                                label: '{{ __('Cancel') }}',
                                                            }
                                                        }, '{{ $this->id }}')
                                                        " label="{{ __('Delete') }}"/>
                @endcan
                <div class="flex">
                    <x-button flat label="{{ __('Cancel') }}" x-on:click="close"/>
                    <x-button primary label="{{ __('Save') }}" wire:click="save"/>
                </div>
            </div>
        </x-slot>
    </x-modal.card>
    <div class="flex items-center justify-end pb-5">
        <div class="mt-3 sm:mt-0 sm:ml-4">
            @can('action.serial-number.create')
                <x-button wire:click="create" primary>{{ __('Assign serial number') }}</x-button>
            @endcan
        </div>
    </div>
    <div class="flex flex-col">
        <div class="-my-2 -mx-4 sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle">
                <div class="shadow-sm ring-1 ring-black ring-opacity-5">
                    <table class="max-h-96 min-w-full border-separate overflow-scroll" style="border-spacing: 0">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:pl-6 lg:pl-8">{{ __('No.') }}</th>
                            <th scope="col"
                                class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">{{ __('Product') }}</th>
                            <th scope="col"
                                class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 py-3.5 pr-4 pl-3 backdrop-blur backdrop-filter sm:pr-6 lg:pr-8"></th>
                        </tr>
                        </thead>
                        <tbody class="bg-white">
                        <template x-for="serialNumber in serialNumbers">
                            <tr>
                                <td class="whitespace-nowrap border-b border-gray-200 py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8" x-text="serialNumber.serial_number" />
                                <td class="whitespace-nowrap border-b border-gray-200 px-3 py-4 text-sm text-gray-500"  x-text="serialNumber.product?.name"/>
                                <td class="relative cursor-pointer whitespace-nowrap border-b border-gray-200 py-4 pr-4 pl-3 text-right text-sm font-medium sm:pr-6 lg:pr-8">
                                    <x-button icon="pencil" x-on:click="$wire.edit(serialNumber.id)">
                                        {{ __('Edit') }}
                                    </x-button>
                                </td>
                            </tr>
                        </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
