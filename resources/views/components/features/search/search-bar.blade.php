<div x-data="{show: @entangle('show').live}">
    {{ $prepend ?? '' }}
    <div x-on:click.outside="show = false" x-on:keydown.escape.window="show = false">
        <x-input :label="__('Products')" icon="magnifying-glass" x-on:click="show = true"
                 onclick="this.setSelectionRange(0, this.value.length)"
                 placeholder="{{ __('Enter a search phrase…') }}" wire:model.live="search"/>
        <div>
        </div>
        <div class="relative z-10 w-full flex-col rounded-md border bg-white pt-3 shadow-2xl" x-show="show" x-transition
             x-cloak>
            <div
                wire:loading.class="opacity-60"
                x-data="{results: @entangle('return').live}"
                @click="show = false"
            >
                <template x-for="result in results">
                    <x-dynamic-component
                        {{ $attributes->merge(['wire:click'  => '', 'x-on:click' => '']) }} component="{{ $searchResultComponent }}"/>
                </template>
                <div class="flex w-full items-center justify-center pb-3 text-gray-500" x-show="!results">
                    <x-icon name="magnifying-glass" class="h-4 w-4"/>
                    <span>
                        {{ __('No results…') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    {{ $slot }}
</div>
