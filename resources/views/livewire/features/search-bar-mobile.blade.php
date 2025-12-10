<div class="sm:hidden">
    <x-button.circle
        color="indigo"
        icon="magnifying-glass"
        x-on:click="$modalOpen('search-bar-mobile-modal')"
    />

    <x-modal
        id="search-bar-mobile-modal"
        size="xl"
        x-on:open="$focusOn('search-mobile-input')"
    >
        <div class="flex flex-col gap-4">
            <x-input
                id="search-mobile-input"
                autocomplete="off"
                icon="magnifying-glass"
                wire:model.live.debounce.500ms="search"
                placeholder="{{ __('Search everywhere...') }}"
            />
            <x-flux::features.search.search-results />
        </div>
    </x-modal>
</div>
