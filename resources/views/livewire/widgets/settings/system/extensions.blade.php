<div class="flex flex-col gap-4 p-4">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div
                class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900"
            >
                <x-icon
                    name="cpu-chip"
                    class="h-4 w-4 text-emerald-600 dark:text-emerald-400"
                />
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white">
                {{ __('PHP Extensions') }}
            </h3>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
        <template x-for="extension in $wire.loadedExtensions">
            <x-badge color="green">
                <x-slot:text>
                    <span x-text="extension"></span>
                </x-slot>
            </x-badge>
        </template>
    </div>
</div>
