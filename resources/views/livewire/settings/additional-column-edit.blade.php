<div>
    <div class="space-y-8 divide-y divide-gray-200">
        <div class="space-y-8 divide-y divide-gray-200">
            <div>
                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6" x-data="{isNew: @entangle('isNew'), hideModel: @entangle('hideModel')}">
                    <div class="sm:col-span-6">
                        <x-input label="{{ __('Name') }}"
                                 placeholder="{{ __('Name') }}"
                                 wire:model="additionalColumn.name"/>
                    </div>
                    <div class="sm:col-span-6" x-show="isNew && !hideModel" x-transition x-cloak>
                        <x-select.styled
                            label="{{ __('Model') }}"
                            placeholder="{{ __('Model') }}"
                            wire:model="additionalColumn.model_type"
                            :options="$models"
                            select="label:value|value:label"
                        />
                    </div>
                    <div class="sm:col-span-6">
                        <x-select.styled
                            label="{{ __('Field Type') }}"
                            placeholder="{{ __('Field Type') }}"
                            wire:model="additionalColumn.field_type"
                            :options="$fieldTypes"
                        />
                    </div>
                    <div class="sm:col-span-6">
                        <x-input label="{{ __('Label') }}"
                                 placeholder="{{ __('Label') }}"
                                 wire:model="additionalColumn.label"/>
                    </div>
                    <div class="sm:col-span-6">
                        <x-checkbox label="{{ __('Is visible in frontend') }}"
                                    wire:model="additionalColumn.is_frontend_visible"/>
                    </div>
                    <div class="sm:col-span-6">
                        <x-checkbox label="{{ __('Is editable in customer portal') }}"
                                 wire:model="additionalColumn.is_customer_editable"/>
                    </div>
                    <div class="sm:col-span-6">
                        <x-checkbox label="{{ __('Is translatable') }}"
                                    wire:model="additionalColumn.is_translatable"/>
                    </div>
                    <div class="sm:col-span-6">
                        <x-select.styled
                            label="{{ __('Validations') }}"
                            placeholder="{{ __('Validations') }}"
                            multiselect
                            wire:model="additionalColumn.validations"
                            :options="$availableValidationRules"
                        />
                    </div>
                    <div class="sm:col-span-6">
                        <x-input label="{{ __('Values') }}"
                                 placeholder="{{ __('Values') }}"
                                 disabled
                                 wire:model="additionalColumn.values"/>
                    </div>
                    <div class="sm:col-span-6">
                        <x-button.circle class="mr-2" color="indigo" icon="plus" wire:click="addEntry" />
                    </div>
                    @foreach($additionalColumn->values ?? [] as $index => $value)
                        <div class="sm:col-span-5">
                            <x-input wire:model.live="additionalColumn.values.{{$index}}" />
                        </div>
                        <div class="ml-1 flex sm:col-span-1">
                            <x-button.circle color="red" icon="trash" wire:click="removeEntry({{$index}})" />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
