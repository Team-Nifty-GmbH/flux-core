@props([
    'label' => null,
    'placeholder' => null,
    'request' => null,
    'lazy' => 0,
])

@php
    $property = $attributes->wire('model')->value();

    if (blank($property)) {
        throw new InvalidArgumentException('The "wire:model" attribute is required for this component.');
    }
@endphp

<div
    {{ $attributes->except(['wire:model', 'label', 'placeholder', 'request', 'lazy']) }}
    @if ($request)
        data-request="{{ json_encode($request) }}"
    @endif
    x-data="{
        search: '',
        items: [],
        open: false,
        loading: false,
        highlighted: -1,
        abortController: null,
        get values() {
            return (
                '{{ $property }}'
                    .split('.')
                    .reduce((carry, key) => carry?.[key], $wire) ?? []
            )
        },
        add(value) {
            value = (value ?? '').trim()

            if (! value) {
                return
            }

            const email = value.match(/<([^>]*)>/)
            if (email && email[1]) {
                value = email[1]
            }

            if (! this.values.includes(value)) {
                this.values.push(value)
            }

            this.search = ''
            this.close()
        },
        remove(value) {
            this.values.splice(this.values.indexOf(value), 1)
        },
        close() {
            this.open = false
            this.highlighted = -1
            this.abortController?.abort()
            this.loading = false
        },
        onKeydown($event) {
            if ($event.key === 'Escape') {
                this.close()

                return
            }

            if ($event.key === 'ArrowDown') {
                $event.preventDefault()
                this.highlighted = Math.min(
                    this.highlighted + 1,
                    this.items.length - 1,
                )

                return
            }

            if ($event.key === 'ArrowUp') {
                $event.preventDefault()
                this.highlighted = Math.max(this.highlighted - 1, -1)

                return
            }

            if ($event.key === 'Enter' || $event.key === ',') {
                $event.preventDefault()

                if (this.open && this.highlighted >= 0) {
                    this.add(this.items[this.highlighted]?.value)
                } else {
                    // read the raw input value - the debounced model may
                    // lag behind on a fast enter
                    this.add($event.target.value)
                }

                $event.target.value = ''
            }
        },
        onInput() {
            @if ($request)
                if (this.search.length < {{ (int) $lazy }}) {
                    this.close()

                    return
                }

                this.fetchItems()
            @endif
        },
        fetchItems() {
            this.abortController?.abort()
            this.abortController = new AbortController()
            this.loading = true
            this.open = true

            const request = JSON.parse(this.$root.dataset.request)
            const url = new URL(request.url, window.location.origin)
            const init = {
                signal: this.abortController.signal,
                method: (request.method ?? 'get').toUpperCase(),
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name=csrf-token]')
                        ?.getAttribute('content'),
                },
            }

            if (init.method === 'POST') {
                init.headers['Content-Type'] = 'application/json'
                init.body = JSON.stringify({
                    search: this.search,
                    ...(request.params ?? {}),
                })
            } else {
                url.searchParams.set('search', this.search)
            }

            fetch(url, init)
                .then((response) =>
                    response.ok ? response.json() : Promise.reject(response),
                )
                .then((data) => {
                    this.items = (Array.isArray(data) ? data : (data?.data ?? []))
                        .filter((item) => item?.value)
                    this.highlighted = this.items.length > 0 ? 0 : -1
                    this.loading = false
                })
                .catch((reason) => {
                    if (reason?.name === 'AbortError') {
                        return
                    }

                    this.items = []
                    this.loading = false
                })
        },
    }"
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
                        x-bind:class="
                            highlighted === index &&
                            'dark:bg-dark-600 bg-gray-100'
                        "
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
