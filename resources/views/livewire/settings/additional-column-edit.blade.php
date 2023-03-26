<div>
    <div class="space-y-8 divide-y divide-gray-200">
        <div class="space-y-8 divide-y divide-gray-200">
            <div>
                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6" x-data="{isNew: @entangle('isNew').defer, hideModel: @entangle('hideModel').defer}">
                    <div class="sm:col-span-6">
                        <x-input label="{{ __('Name') }}"
                                 placeholder="{{ __('Name') }}"
                                 wire:model.defer="additionalColumn.name"/>
                    </div>
                    <div class="sm:col-span-6" x-show="isNew && !hideModel" x-transition x-cloak>
                        <x-select
                            label="{{ __('Model') }}"
                            placeholder="{{ __('Model') }}"
                            wire:model.defer="additionalColumn.model_type"
                            :options="$models"
                        />
                    </div>
                    <div class="sm:col-span-6">
                        <x-select
                            label="{{ __('Field Type') }}"
                            placeholder="{{ __('Field Type') }}"
                            wire:model.defer="additionalColumn.field_type"
                            :options="$fieldTypes"
                        />
                    </div>
                    <div class="sm:col-span-6">
                        <x-input label="{{ __('Label') }}"
                                 placeholder="{{ __('Label') }}"
                                 wire:model.defer="additionalColumn.label"/>
                    </div>
                    <div class="sm:col-span-6">
                        <x-checkbox label="{{ __('Is visible in frontend') }}"
                                    wire:model.defer="additionalColumn.is_frontend_visible"/>
                    </div>
                    <div class="sm:col-span-6">
                        <x-checkbox label="{{ __('Is editable in customer portal') }}"
                                 wire:model.defer="additionalColumn.is_customer_editable"/>
                    </div>
                    <div class="sm:col-span-6">
                        <x-checkbox label="{{ __('Is translatable') }}"
                                    wire:model.defer="additionalColumn.is_translatable"/>
                    </div>
                    <div class="sm:col-span-6">
                        <x-select
                            label="{{ __('Validations') }}"
                            placeholder="{{ __('Validations') }}"
                            multiselect
                            wire:model.defer="additionalColumn.validations"
                            :options="$availableValidationRules"
                        />
                    </div>
                    <div class="sm:col-span-6">
                        <x-input label="{{ __('Values') }}"
                                 placeholder="{{ __('Values') }}"
                                 disabled
                                 wire:model.defer="additionalColumn.values"/>
                    </div>
                    <div class="sm:col-span-6">
                        <x-button.circle class="mr-2" primary icon="plus" wire:click="addEntry" />
                    </div>
                    @foreach($additionalColumn['values'] as $index => $value)
                        <div class="sm:col-span-5">
                            <x-input wire:model="additionalColumn.values.{{$index}}" />
                        </div>
                        <div class="ml-1 flex sm:col-span-1">
                            <x-button.circle negative icon="trash" wire:click="removeEntry({{$index}})" />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
