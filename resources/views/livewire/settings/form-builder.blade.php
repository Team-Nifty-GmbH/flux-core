<div class="py-6">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold dark:text-white">{{ __('Form Builder - Forms') }}</h1>
                <div
                    class="mt-2 text-sm text-gray-300">{{ __('Here you can manage all Forms created with Form Builder...') }}</div>
            </div>
        </div>
        <div>
            @include('tall-datatables::livewire.data-table')
        </div>
    </div>
    <x-modal.card wire:model="showModal" fullscreen>
        <x-slot name="title">
            {{ __('Form Builder - Form') }}
        </x-slot>
        <div>
            <div class="grid grid-cols-6 gap-4">
                <div class="col-span-1 sm:col-span-1">
                    <x-input wire:model="form.name" label="Name"/>
                </div>
                <div class="col-span-1 sm:col-span-1">
                    <x-textarea wire:model="form.description" label="Description"/>
                </div>
                <div class="col-span-1 sm:col-span-1">
                    <x-select
                        label="{{ __('Model') }}"
                        placeholder="{{ __('Model') }}"
                        wire:model="form.model_type"
                        :options="$models"
                    />
                    <x-input wire:model="form.model_id" label="Model ID"/>
                </div>
                <div class="col-span-1 sm:col-span-1">
                    <x-datetime-picker wire:model="form.start_date" label="Start Date"/>
                    <x-datetime-picker wire:model="form.end_date" label="End Date"/>
                </div>
                <div class="col-span-1 sm:col-span-1">
                    <x-checkbox wire:model="form.is_active" label="Is Active"/>
                </div>
            </div>
            <div class="mt-2 pt-2 border-t">
                @if($form->sections === [])
                    <div class="flex justify-center items-center">
                        <div class="text-gray-400 text-sm">{{ __('No Sections') }}</div>
                        <div class="col-span-1 sm:col-span-2">
                            <x-button wire:click="addSection" label="{{ __('Add Section') }}"/>
                        </div>
                    </div>
                @else
                    @foreach($form->sections as $sectionIndex => $section)
                        <div class="grid grid-cols-6 gap-4">
                            <div class="col-span-1 sm:col-span-1">
                                <x-input wire:model="form.sections.{{$sectionIndex}}.name" label="{{__('Name')}}"/>
                            </div>
                            <div class="col-span-1 sm:col-span-1">
                                <x-input wire:model="form.sections.{{$sectionIndex}}.description"
                                         label="{{__('Description')}}"/>
                            </div>
                            <div class="col-span-1 sm:col-span-1">
                                <x-input wire:model="form.sections.{{$sectionIndex}}.columns"
                                         label="{{__('Columns')}}"/>
                            </div>
                            <div class="col-span-1 sm:col-span-1 pt-2">
                                <x-button icon="chevron-up" wire:click="sectionSortUp({{$sectionIndex}})"/>
                                <x-button icon="chevron-down" wire:click="sectionSortDown({{$sectionIndex}})"/>
                                <x-button icon="trash" wire:click="removeFormSection({{$sectionIndex}})"/>
                            </div>
                        </div>

                        @foreach($section['fields'] as $fieldIndex => $field)
                            <div class="grid grid-cols-5 gap-4 mt-2"
                                 x-data="{
                                 field: @entangle( 'form.sections.' . $sectionIndex . '.fields.' . $fieldIndex),
                                 fieldOptions: [],

                                 }">
                                <div class="col-span-1 sm:col-span-1">
                                    <x-input
                                        wire:model="form.sections.{{$sectionIndex}}.fields.{{$fieldIndex}}.name"
                                        label="{{__('Name')}}"/>
                                </div>
                                <div class="col-span-1 sm:col-span-1">
                                    <x-input
                                        wire:model="form.sections.{{$sectionIndex}}.fields.{{$fieldIndex}}.description"
                                        label="{{__('Description')}}"/>
                                </div>
                                <div class="col-span-1 sm:col-span-1">
                                    <x-select x-on:selected="$wire.getFieldOptions($event.target.value).then((response) => {
            fieldOptions = response;
        });" :options="$fieldTypes" option-label="name" option-value="value"
                                              wire:model="form.sections.{{$sectionIndex}}.fields.{{$fieldIndex}}.type"
                                              x-model="field.type"
                                              label="{{__('Type')}}"/>
                                </div>
                                <div class="col-span-1 sm:col-span-1">
                                    <template x-for="(fieldOption, name) in fieldOptions">
                                        <div class="flex">
                                            <span x-text="name"></span>
                                            <x-input x-value="fieldOption"/>
                                        </div>
                                    </template>
                                </div>
                                <div class="col-span-1 sm:col-span-1 pt-2">
                                    {{--                                <x-button icon="chevron-up" wire:click="fieldSortUp({{$sectionIndex}}, {{$fieldIndex}})" />--}}
                                    {{--                                <x-button icon="chevron-down" wire:click="fieldSortDown({{$sectionIndex}}, {{$fieldIndex}})" />--}}
                                    <x-button icon="trash"
                                              wire:click="removeFormField({{$sectionIndex}}, {{$fieldIndex}})"/>
                                </div>
                            </div>
                        @endforeach
                        <div class="col-span-1 sm:col-span-2 pt-2 mt-2">
                            <x-button wire:click="addFormField({{$sectionIndex}})" label="{{ __('Add Field') }}"/>
                        </div>
                    @endforeach
                    <div class="col-span-1 sm:col-span-2 pt-2 mt-2 border-t">
                        <x-button wire:click="addSection" label="{{ __('Add Section') }}"/>
                    </div>
                @endif
            </div>
        </div>
        <x-slot name="footer">
            <x-button primary wire:click="saveItem">
                {{ __('Save') }}
            </x-button>
            <x-button secondary wire:click="closeModal">
                {{ __('Close') }}
            </x-button>
            <x-button danger wire:click="debug">
                {{ __('Debug') }}
            </x-button>
        </x-slot>
    </x-modal.card>
{{--    <x-modal.card wire:model="showPreviewModal">--}}
{{--        <livewire:features.form-builder-show :formId="1"/>--}}
{{--    </x-modal.card>--}}
</div>
