<div>
    <div class="space-y-8 divide-y divide-gray-200">
        <div class="space-y-8 divide-y divide-gray-200">
            <div>
                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6" x-data="{isNew: @entangle('isNew').defer}">
                    <div class="sm:col-span-6">
                        <x-input label="{{ __('Name') }}"
                                 placeholder="{{ __('Name') }}"
                                 wire:model.defer="ticketType.name"/>
                    </div>
                    <div class="sm:col-span-6" x-show="isNew" x-transition x-cloak>
                        <x-select
                            label="{{ __('Model') }}"
                            placeholder="{{ __('Model') }}"
                            wire:model.defer="ticketType.model_type"
                            :options="$models"
                        />
                    </div>
                </div>
                <x-errors></x-errors>
            </div>
        </div>
    </div>
</div>
