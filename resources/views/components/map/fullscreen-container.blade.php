@props([
    'wireMethod' => 'loadMap',
    'autoload' => false,
])

<div
    x-data="{ isFullscreen: false }"
    class="transition-all duration-500 ease-in-out"
    x-bind:class="
        isFullscreen && $wire.showMap
            ? 'fixed inset-0 z-50 bg-white dark:bg-secondary-800 p-4 flex flex-col'
            : ''
    "
>
    <div
        x-on:load-map.window="$nextTick(() => onChange())"
        x-data="addressMap(
                    $wire,
                    '{{ $wireMethod }}',
                    {{ $autoload ? 'true' : 'false' }},
                    '{{ auth()->user() ?->getAvatarUrl() }}',
                )"
        x-cloak
        x-show="$wire.showMap"
        x-transition:enter="transition duration-300 ease-out"
        x-transition:enter-start="scale-95 transform opacity-0"
        x-transition:enter-end="scale-100 transform opacity-100"
        x-transition:leave="transition duration-200 ease-in"
        x-transition:leave-start="scale-100 transform opacity-100"
        x-transition:leave-end="scale-95 transform opacity-0"
        x-bind:class="isFullscreen ? 'flex-1 flex flex-col min-h-0' : 'z-0 py-4'"
    >
        <div
            class="w-full"
            x-bind:class="isFullscreen ? 'flex-1 flex flex-col min-h-0' : ''"
        >
            <div class="flex items-center justify-between gap-4 pb-4">
                <div class="flex items-center gap-4">
                    {{ $controls ?? '' }}
                    <x-select.styled
                        :label="__('Limit')"
                        wire:model.live="mapLimit"
                        x-on:select="$nextTick(() => onChange())"
                        select="label:label|value:value"
                        :options="[
                            ['label' => '100', 'value' => 100],
                            ['label' => '250', 'value' => 250],
                            ['label' => '500', 'value' => 500],
                            ['label' => '1000', 'value' => 1000],
                            ['label' => __('All'), 'value' => null],
                        ]"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <x-button.circle
                        color="secondary"
                        light
                        x-on:click="isFullscreen = !isFullscreen; $nextTick(() => resizeMap())"
                        icon="arrows-pointing-out"
                    />
                    <x-button.circle
                        color="secondary"
                        light
                        x-on:click="isFullscreen = false; $wire.$set('showMap', false, true)"
                        icon="x-mark"
                    />
                </div>
            </div>
            <div
                x-intersect.once="onChange()"
                x-bind:class="isFullscreen ? 'flex-1 min-h-0 flex flex-col' : ''"
                class="overflow-hidden rounded-lg border border-secondary-200 dark:border-secondary-600"
            >
                <div
                    id="map"
                    x-bind:class="isFullscreen ? 'flex-1 w-full' : 'h-96 min-w-96'"
                ></div>
            </div>
        </div>
    </div>
</div>
