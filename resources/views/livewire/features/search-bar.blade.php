<div
    x-data="{
        init() {
            $watch('show', value => {
                if (! value) {
                    showDetails(null, null);
                }
            })
        },
        show: false,
        search: $wire.entangle('search', true),
        detailModel: null,
        detailId: null,
        result: $wire.entangle('return'),
        modelLabels: $wire.entangle('modelLabels'),
        showDetails(model, id) {
            if (model === this.detailModel && id === this.detailId) {
                return;
            }

            this.detailModel = model;
            this.detailId = id;
            $dispatch('render-search-bar-widget', {model: model, id: id})
        }
    }"
    x-on:click.outside="show = false"
    x-on:keydown.escape.window="show = false"
    class="relative flex-1"
>
    <x-input shadowless
         autocomplete="off"
         icon="search"
         class="w-full border-0"
         x-on:click="show = true"
         x-on:keydown="show = true"
         x-on:keydown.enter="show = false"
         wire:model.live.debounce.500ms="search"
         placeholder="{{ __('Search everywhere...') }}"
    />
    <div class="absolute z-[9] w-full pt-6" x-show="show" x-transition x-cloak>
        <x-card class="relative !px-0 !py-0 pb-2">
            <x-label x-show="search.length && ! Object.keys(result).length" x-cloak class="flex w-full items-center justify-center py-1.5">
                <x-icon name="search" class="mr-2 h-5 w-5" />
                <div>
                    {{ __('No results…') }}
                </div>
            </x-label>
            <x-spinner />
            <div class="dark:divide-secondary-600 flex flex-row divide-x divide-gray-100">
                <ul class="sm:basis-1/2">
                    <template x-for="(items, model) in result">
                        <li>
                            <div
                                class="dark:bg-secondary-600 w-full bg-gray-100 py-2.5 px-4 text-xs font-semibold text-gray-900 dark:text-gray-50"
                                x-text="modelLabels[model].label"
                            >
                            </div>
                            <ul class="mt-2 text-sm text-gray-800" role="none">
                                <template x-for="item in items">
                                    <li x-on:mouseover.debounce.500ms="showDetails(model, item.id)"
                                        class="hover:bg-primary-600 flex cursor-pointer select-none items-center space-x-1.5 px-4 py-2 hover:text-white"
                                        x-on:click="show = false; $wire.showDetail(model, item.id)"
                                    >
                                        <x-avatar src="#" xs x-bind:src="item.src" x-cloak x-show="item.src" />
                                        <div class="inline-block align-middle hover:text-white dark:text-gray-50" x-text="item.label">
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </li>
                    </template>
                </ul>
                <div wire:ignore class="hidden basis-1/2 sm:block">
                    <livewire:widgets.search-bar lazy />
                </div>
            </div>
        </x-card>
    </div>
</div>
