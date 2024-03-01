<div id="contact" class="min-h-full">
    <main class="py-10">
        <div
            class="mx-auto px-4 sm:px-6 md:flex md:items-center md:justify-between md:space-x-5 lg:px-8">
            <div class="flex items-center space-x-5">
                <label for="avatar" style="cursor: pointer">
                    <x-avatar xl src="{{ $avatar }}" />
                </label>
                <input type="file" accept="image/*" id="avatar" class="hidden" wire:model.live="avatar"/>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                        <div class="opacity-40 transition-opacity hover:opacity-100">{{ $contact->customer_number }}</div>
                        {{ $contact->main_address['name'] }}
                    </h1>
                </div>
            </div>
            <div class="justify-stretch mt-6 flex flex-col-reverse space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3">
                @if(resolve_static(\FluxErp\Actions\Contact\UpdateContact::class, 'canPerformAction', [false]))
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
                @endif
                @if(resolve_static(\FluxErp\Actions\Contact\DeleteContact::class, 'canPerformAction', [false]))
                    <x-button negative label="{{ __('Delete') }}"
                              wire:confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Contact')]) }}"
                              wire:click="delete()"
                    />
                @endif
            </div>
        </div>
        <x-tabs
            wire:model.live="tab"
            :$tabs
            wire:ignore
        />
    </main>
</div>
