<div class="py-6">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold dark:text-white">{{ __('Form Builder - Forms') }}</h1>
                <div
                    class="mt-2 text-sm text-gray-300">{{ __('Here you can manage all Forms created with Form Builder...') }}</div>
            </div>
        </div>
        <livewire:data-tables.form-builder-form-list/>
    </div>
    <x-modal.card wire:model="showModal">
        <x-slot name="title">
            {{ __('Form Builder - Form') }}
        </x-slot>
        <div>
            <div>
                <div class="col-span-1 sm:col-span-2">
                    <x-input wire:model="form.title" label="Title"/>
                </div>
                <div class="col-span-1 sm:col-span-2">

                </div>
            </div>
            <div class="pt-2">
                <div>Preview</div>
                <x-button wire:click="" primary>
                    {{ __('Add Element') }}
                </x-button>
            </div>
        </div>
        <x-slot name="footer">
            <x-button primary wire:click="saveForm">
                {{ __('Save') }}
            </x-button>
            <x-button secondary wire:click="closeModal">
                {{ __('Close') }}
            </x-button>
        </x-slot>
    </x-modal.card>
</div>
