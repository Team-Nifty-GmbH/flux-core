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
                        <div
                            class="opacity-40 transition-opacity hover:opacity-100">{{ $contact['customer_number'] }}</div>
                        {{ implode(', ', array_filter([$contact['main_address']['company'], trim($contact['main_address']['firstname'] . ' ' . $contact['main_address']['lastname'])], function($value) {return $value !== '';})) }}
                    </h1>
                </div>
            </div>
            <div class="justify-stretch mt-6 flex flex-col-reverse space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3">
                @can('action.contact.delete')
                    <x-button negative label="{{ __('Delete') }}" x-on:click="
                        window.$wireui.confirmDialog({
                        title: '{{ __('Delete contact') }}',
                        description: '{{ __('Do you really want to delete this contact?') }}',
                        icon: 'error',
                        accept: {
                            label: '{{ __('Delete') }}',
                            method: 'delete',
                        },
                        reject: {
                            label: '{{ __('Cancel') }}',
                        }
                        }, $wire.__instance.id)
                    "/>
                @endcan
            </div>
        </div>
        <x-tabs
            wire:model.live="tab"
            :tabs="$tabs"
            wire:ignore
        >
            <div class="w-full lg:col-start-1 xl:col-span-2 xl:flex xl:space-x-6">
                <section class="w-full lg:pt-0">
                    <x-errors />
                    <x-spinner />
                    <x-dynamic-component :component="'contact.' . $tab" />
                </section>
            </div>
        </x-tabs>
    </main>
</div>
