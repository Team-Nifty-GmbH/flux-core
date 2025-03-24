<div id="contact" class="min-h-full">
    {{ $this->renderCreateDocumentsModal() }}
    <main class="py-10">
        <div
            class="mx-auto px-4 sm:px-6 md:flex md:items-center md:justify-between md:space-x-5 lg:px-8"
        >
            <div class="flex items-center space-x-5">
                @section('contact.title')
                @section('contact.title.avatar')
                <label for="avatar" class="cursor-pointer">
                    <x-avatar xl :image="$avatar" />
                </label>
                <input
                    type="file"
                    accept="image/*"
                    id="avatar"
                    class="hidden"
                    wire:model.live="avatar"
                />
                @show
                @section('contact.title.name')
                <div>
                    <h1
                        class="text-2xl font-bold text-gray-900 dark:text-gray-50"
                    >
                        <div
                            class="opacity-40 transition-opacity hover:opacity-100"
                        >
                            {{ $contact->customer_number }}
                        </div>
                        {{ data_get($contact->main_address, 'name') }}
                    </h1>
                </div>
                @show
                @show
            </div>
            <div
                class="mt-6 flex flex-col-reverse justify-stretch gap-2 space-y-reverse sm:flex-row-reverse sm:justify-end sm:gap-x-2 sm:space-y-0 sm:space-x-reverse md:mt-0 md:flex-row md:gap-x-2"
            >
                @section('contact.buttons')
                @canAction(\FluxErp\Actions\Contact\UpdateContact::class)
                    <div x-cloak x-show="$wire.edit" class="flex gap-x-2">
                        <x-button
                            color="secondary"
                            light
                            x-on:click="$wire.edit = false; $wire.reloadContact()"
                            :text="__('Cancel')"
                        />
                        <x-button
                            x-on:click="$wire.save()"
                            color="indigo"
                            :text="__('Save')"
                        />
                    </div>
                    <div x-cloak x-show="! $wire.edit">
                        <x-button
                            x-on:click="$wire.edit = true;"
                            color="indigo"
                            :text="__('Edit')"
                        />
                    </div>
                @endcanAction

                @canAction(\FluxErp\Actions\Contact\DeleteContact::class)
                    <x-button
                        color="red"
                        :text="__('Delete') "
                        wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Contact')]) }}"
                        wire:click="delete()"
                    />
                @endcanAction

                @show
            </div>
        </div>
        <x-flux::tabs wire:model.live="tab" :$tabs wire:ignore />
    </main>
</div>
