@php
    $property = $attributes->wire('model')->value();

    if (blank($property)) {
        throw new InvalidArgumentException('The "wire:model" attribute is required for this component.');
    }
@endphp

<div
    {{ $attributes->except(['wire:model']) }}
    @if ($request)
        data-request="{{ json_encode($request) }}"
    @endif
    x-data="pillbox('{{ $property }}', {{ $lazy }})"
>
    @if ($label)
        <x-label :label="$label" />
    @endif

    <div class="flex gap-1" x-cloak x-show="values.length > 0">
        <template x-for="value in values">
            <x-badge flat color="indigo">
                <x-slot:text>
                    <span x-text="value"></span>
                </x-slot:text>
                <x-slot name="right" class="relative flex h-2 w-2 items-center">
                    <button type="button" x-on:click="remove(value)">
                        <x-icon name="x-mark" class="h-4 w-4" />
                    </button>
                </x-slot>
            </x-badge>
        </template>
    </div>

    <div class="relative" x-on:click.outside="close()">
        <x-input
            :placeholder="$placeholder"
            class="w-full"
            autocomplete="off"
            x-model.debounce.250ms="search"
            x-on:keydown="onKeydown($event)"
            x-effect="
                search;
                onInput();
            "
        />
        <div
            x-cloak
            x-show="open"
            class="dark:bg-dark-700 absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white shadow-lg ring-1 ring-black/5"
        >
            <ul dusk="flux_pillbox_options">
                <li
                    x-cloak
                    x-show="loading"
                    class="px-3 py-2 text-sm text-gray-500"
                >
                    {{ __('Loading...') }}
                </li>
                <li
                    x-cloak
                    x-show="!loading && items.length === 0"
                    class="px-3 py-2 text-sm text-gray-500"
                >
                    {{ __('No results…') }}
                </li>
                <template x-for="(item, index) in items" x-bind:key="index">
                    <li
                        dusk="flux_pillbox_option"
                        class="dark:hover:bg-dark-600 flex cursor-pointer items-center gap-2 px-3 py-2 hover:bg-gray-100"
                        x-bind:class="{
                            'dark:bg-dark-600 bg-gray-100':
                                highlighted === index,
                        }"
                        x-on:mouseenter="highlighted = index"
                        x-on:click="add(item.value)"
                    >
                        <template x-if="item.image">
                            <img
                                class="h-6 w-6 rounded-full"
                                x-bind:src="item.image"
                                alt=""
                            />
                        </template>
                        <div class="flex flex-col">
                            <span
                                class="dark:text-dark-100 text-sm text-gray-900"
                                x-text="item.value"
                            ></span>
                            <template x-if="item.description">
                                <span
                                    class="text-xs text-gray-500"
                                    x-text="item.description"
                                ></span>
                            </template>
                        </div>
                    </li>
                </template>
            </ul>
        </div>
    </div>
</div>
