<div class="flex min-h-full flex-col" x-data="{
    edit: false,
}">
    <div
        class="mx-auto w-full md:flex md:items-center md:justify-between md:space-x-5"
    >
        <div class="flex items-center space-x-5">
            @section('lead.title')
            @section('lead.title.avatar')
            <x-avatar xl :image="$leadForm->avatar" />
            @show
            @section('lead.title.name')
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    <div class="flex">
                        <div class="pl-2">
                            <span x-text="$wire.leadForm.name"></span>
                        </div>
                    </div>
                </h1>
                <a
                    class="flex gap-1.5 font-semibold opacity-40"
                    x-bind:href="$wire.leadForm.addressUrl"
                    x-cloak
                    x-show="$wire.leadForm.addressUrl"
                    wire:navigate
                >
                    <x-icon name="link" class="h-4 w-4" />
                    <span x-text="$wire.leadForm.addressLabel"></span>
                </a>
            </div>
            @show
            @show
        </div>
        <div
            class="mt-6 flex flex-col-reverse justify-stretch space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-x-3 sm:space-y-0 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3"
        >
            <x-button
                :text="__('Edit')"
                color="indigo"
                x-cloak
                x-show="!edit"
                class="w-full"
                x-on:click="edit = true"
            />
            <x-button
                :text="__('Save')"
                color="indigo"
                loading="save"
                x-cloak
                x-show="edit"
                class="w-full"
                x-on:click="$wire.save().then((success) => {
                    edit = false;
                });"
            />
            <x-button
                :text="__('Cancel')"
                color="secondary"
                loading="save"
                light
                flat
                x-cloak
                x-show="edit"
                class="w-full"
                x-on:click="edit = false; $wire.resetForm();"
            />
        </div>
    </div>
    <x-flux::tabs wire:model.live="tab" :$tabs />
</div>
