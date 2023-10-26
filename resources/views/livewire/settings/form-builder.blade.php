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
                    <x-input wire:model="form.slug" label="Slug"/>
                </div>
                <div class="col-span-1 sm:col-span-1">
                    <x-input wire:model="form.description" label="Description"/>
                </div>
                <div class="col-span-1 sm:col-span-1">
                    <x-checkbox wire:model="form.is_active" label="Is Active"/>
                </div>
                <div class="col-span-1 sm:col-span-1">
                    <x-datetime-picker wire:model="form.start_date" label="Start Date"/>
                </div>
                <div class="col-span-1 sm:col-span-1">
                    <x-datetime-picker wire:model="form.end_date" label="End Date"/>
                </div>
            </div>
            <div class="mt-2 pt-2 border-t">
                @if($formData === [])
                    <div class="flex justify-center items-center">
                        <div class="text-gray-400 text-sm">{{ __('No Sections') }}</div>
                        <div class="col-span-1 sm:col-span-2">
                            <x-button wire:click="addSection" label="{{ __('Add Section') }}"/>
                        </div>
                    </div>
                @else
                    @foreach($formData as $sectionIndex => $section)
                        <div class="grid grid-cols-6 gap-4">
                            <div class="col-span-1 sm:col-span-1">
                                <x-input wire:model="formData.{{$sectionIndex}}.name" label="{{__('Name')}}"/>
                            </div>
                            <div class="col-span-1 sm:col-span-1">
                                <x-input type="number" wire:model="formData.{{$sectionIndex}}.ordering"
                                         label="{{__('Ordering')}}"/>
                            </div>
                            <div class="col-span-1 sm:col-span-1">
                                <x-input type="number" wire:model="formData.{{$sectionIndex}}.columns" label="{{__('Columns')}}"/>
                            </div>
                            <div class="col-span-1 sm:col-span-1">
                                <x-input wire:model="formData.{{$sectionIndex}}.description"
                                         label="{{__('Description')}}"/>
                            </div>
                            <div class="col-span-1 sm:col-span-1">
                                <x-select type="icon" wire:model="formData.{{$sectionIndex}}.description" label="{{__('Icon')}}"/>
                            </div>
                            <div class="col-span-1 sm:col-span-1">
                                <x-checkbox wire:model="formData.{{$sectionIndex}}.aside" label="{{__('Aside')}}"/>
                                <x-checkbox wire:model="formData.{{$sectionIndex}}.compact" label="{{__('Compact')}}"/>
                            </div>
                        </div>
                        @if(!array_key_exists('fields', $formData[$sectionIndex]))
                            <div class="flex justify-center items-center">
                                <div class="text-gray-400 text-sm">{{ __('No Fields') }}</div>
                                <div class="col-span-1 sm:col-span-2">
                                    <x-button wire:click="addFormField({{$sectionIndex}})" label="{{ __('Add Field') }}"/>
                                </div>
                            </div>
                        @else
                            @foreach($formData[$sectionIndex]['fields'] as $fieldIndex => $field)
                                <div class="grid grid-cols-5 gap-4">
                                    <div class="col-span-1 sm:col-span-1">
                                        <x-input wire:model="formData.{{$sectionIndex}}.fields.{{$fieldIndex}}.name" label="{{__('Name')}}"/>
                                    </div>
                                    <div class="col-span-1 sm:col-span-1">
                                        <x-input wire:model="formData.{{$sectionIndex}}.fields.{{$fieldIndex}}.description" label="{{__('Description')}}"/>
                                    </div>
                                    <div class="col-span-1 sm:col-span-1">
                                        <x-select :options="$fieldTypes" option-label="name" option-value="value" wire:model="formData.{{$sectionIndex}}.fields.{{$fieldIndex}}.type" label="{{__('Type')}}"/>
                                    </div>
                                    <div class="col-span-1 sm:col-span-1">
                                        <x-input wire:model="formData.{{$sectionIndex}}.fields.{{$fieldIndex}}.ordering" label="{{__('Ordering')}}"/>
                                    </div>
                                    <div class="col-span-1 sm:col-span-1">
                                        <x-input wire:model="formData.{{$sectionIndex}}.fields.{{$fieldIndex}}.options" label="{{__('Options')}}"/>
                                    </div>
                                </div>
                            @endforeach
                            <div class="col-span-1 sm:col-span-2 pt-2 mt-2">
                                <x-button wire:click="addFormField({{$sectionIndex}})" label="{{ __('Add Field') }}"/>
                            </div>
                        @endif
                    @endforeach
                    <div class="col-span-1 sm:col-span-2 pt-2 mt-2 border-t">
                        <x-button wire:click="addSection" label="{{ __('Add Section') }}"/>
                    </div>
                    <div class="pt-2 mt-2 border-t">
                        <div>Preview</div>

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
</div>
