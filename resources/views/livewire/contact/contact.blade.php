<div id="contact" class="min-h-full">
    {{ $this->renderCreateDocumentsModal() }}
    <main class="py-10">
        <div
            class="mx-auto px-4 sm:px-6 md:flex md:items-center md:justify-between md:space-x-5 lg:px-8">
            <div class="flex items-center space-x-5">
                @section('contact.title')
                    @section('contact.title.avatar')
                        <label for="avatar" class="cursor-pointer">
                            <x-avatar xl src="{{ $avatar }}" />
                        </label>
                        <input type="file" accept="image/*" id="avatar" class="hidden" wire:model.live="avatar"/>
                    @show
                    @section('contact.title.name')
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                                <div class="opacity-40 transition-opacity hover:opacity-100">{{ $contact->customer_number }}</div>
                                {{ data_get($contact->main_address, 'name') }}
                            </h1>
                        </div>
                    @endsection
                @show
            </div>
            <div class="justify-stretch mt-6 flex flex-col-reverse space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3">
                @section('contact.buttons')
                    @canAction(\FluxErp\Actions\Contact\UpdateContact::class)
                        <div x-cloak x-show="$wire.edit">
                            <x-button
                                x-on:click="$wire.edit = false; $wire.reloadContact()"
                                :label="__('Cancel')"
                            />
                            <x-button
                                x-on:click="$wire.save()"
                                primary
                                :label="__('Save')"
                            />
                        </div>
                        <div x-cloak x-show="! $wire.edit">
                            <x-button
                                x-on:click="$wire.edit = true;"
                                primary
                                :label="__('Edit')"
                            />
                        </div>
                    @endCanAction
                    @canAction(\FluxErp\Actions\Contact\DeleteContact::class)
                        <x-button negative label="{{ __('Delete') }}"
                                  wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Contact')]) }}"
                                  wire:click="delete()"
                        />
                    @endCanAction
                @show
            </div>
        </div>
        <x-flux::tabs
            wire:model.live="tab"
            :$tabs
            wire:ignore
        />
    </main>
</div>
