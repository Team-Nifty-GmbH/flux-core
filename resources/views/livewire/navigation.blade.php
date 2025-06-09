<div>
    @if ($setting)
        <style>
            @if(($setting['nav']['active_item'] ?? false))
                nav .nav-item-active {
                background: {{ $setting['nav']['active_item'] }} !important;
            }
            @endif

            @if(($setting['nav']['icon_size'] ?? false))
                nav i {
                font-size: {{ $setting['nav']['icon_size'] }}px !important;
            }
            @endif

            @if(($setting['nav']['hover_item'] ?? false))
                nav a:hover {
                background: {{ $setting['nav']['hover_item'] }} !important;
            }
            @endif
        </style>
    @endif

    <div
        id="main-navigation"
        x-on:menu-force-open.window="menuOpen ? closeMenu(true) : showMenu(true)"
        x-data="{
            init() {
                document.addEventListener('livewire:navigating', () => {
                    this.closeMenu(true)
                })
            },
            open: [],
            toggleMenu(key) {
                if (this.isOpen(key)) {
                    this.open = this.open.filter((item) => item !== key)
                } else {
                    this.open.push(key)
                }
            },
            isOpen(key) {
                return this.open.includes(key) && this.menuOpen
            },
            showMenu(force = null) {
                if (this.forced && ! force) {
                    return
                }

                this.menuOpen = true
                if (force) {
                    this.forced = true
                }
            },
            closeMenu(force = null) {
                if (this.forced && ! force) {
                    return
                }

                this.menuOpen = false
            },
            forced: false,
            menuOpen: false,
            frequentlyVisitedOpen: false,
            favoritesOpen: false,
        }"
    >
        <x-flux::nav.nav :background="$background">
            <nav
                class="flex flex-1 flex-col gap-6 space-y-2 overflow-x-hidden overflow-y-hidden px-2 py-4 hover:overflow-y-auto"
            >
                <div>
                    @foreach ($navigations as $key => $navigation)
                        <div>
                            <a
                                @if ((! data_get($navigation, "is_virtual_uri") && data_get($navigation, "children")) || data_get($navigation, "route_name") === "dashboard")
                                    )
                                    wire:current.exact="bg-indigo-500 dark:bg-indigo-700 text-white! hover:bg-indigo-600 nav-item-active"
                                @else
                                    wire:current="bg-indigo-500 dark:bg-indigo-700 text-white! hover:bg-indigo-600 nav-item-active"
                                @endif
                                href="{{ data_get($navigation, "uri", "#") }}"
                                @if ($navigation["children"] ?? false)
                                    x-on:click.prevent="toggleMenu('{{ $key }}')"
                                    target="_blank"
                                @else
                                    target="{{ $navigation["target_blank"] ?? false ? "_blank" : "" }}"
                                @endif
                                class="dark:text-light dark:hover:bg-indigo flex items-center rounded-md py-2 text-gray-500 transition-colors hover:bg-gray-800/50"
                            >
                                <div class="w-16 flex-none">
                                    <div
                                        class="flex w-full justify-center text-white"
                                    >
                                        <x-icon
                                            :name="$navigation['icon'] ?? 'no-symbol'"
                                            class="h-4 w-4"
                                        />
                                    </div>
                                </div>
                                <span class="truncate text-sm text-white">
                                    {{ __($navigation["label"] ?? $key) }}
                                </span>
                                @if ($navigation["children"] ?? false)
                                    <span
                                        aria-hidden="true"
                                        class="ml-auto pl-2 pr-2"
                                    >
                                        <x-icon
                                            name="chevron-left"
                                            class="h-4 w-4 transform text-white transition-transform"
                                            x-bind:class="{ '-rotate-90': isOpen('{{ $key }}') }"
                                        />
                                    </span>
                                @endif
                            </a>
                            @if ($navigation["children"] ?? false)
                                <div
                                    x-show="isOpen('{{ $key }}')"
                                    x-cloak
                                    x-collapse.duration.200ms
                                    class="mt-2 space-y-2 overflow-x-hidden text-white"
                                >
                                    @foreach ($navigation["children"] as $child)
                                        <a
                                            @if ((! data_get($navigation, "is_virtual_uri") && data_get($navigation, "children")) || data_get($navigation, "route_name") === "dashboard")
                                                )
                                                wire:current.exact="rounded-md bg-indigo-600/50 dark:bg-indigo-700/5 hover:bg-indigo-600/10"
                                            @else
                                                wire:current="rounded-md bg-indigo-600/50 dark:bg-indigo-700/5 hover:bg-indigo-600/10"
                                            @endif
                                            href="{{ $child["uri"] }}"
                                            class="dark:hover:text-light block truncate rounded-md p-2 pl-20 text-sm transition-colors duration-200 hover:bg-gray-800/50"
                                        >
                                            {{ __($child["label"]) }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if (! is_null($visits))
                    <div class="whitespace-nowrap">
                        <div
                            x-on:click="frequentlyVisitedOpen = ! frequentlyVisitedOpen"
                            class="dark:text-light dark:hover:bg-indigo flex cursor-pointer items-center rounded-md py-2 text-gray-500 text-white transition-colors hover:bg-gray-800/50"
                        >
                            <div class="w-16 flex-none">
                                <div
                                    class="flex w-full justify-center text-white"
                                >
                                    <x-icon name="clock" class="h-4 w-4" />
                                </div>
                            </div>
                            <span class="truncate text-sm text-white">
                                {{ __("Frequently visited") }}
                            </span>
                            <span aria-hidden="true" class="ml-auto pl-2 pr-2">
                                <x-icon
                                    name="chevron-left"
                                    class="h-4 w-4 transform text-white transition-transform"
                                    x-bind:class="frequentlyVisitedOpen && '-rotate-90'"
                                />
                            </span>
                        </div>
                        <div
                            x-show="frequentlyVisitedOpen"
                            x-cloak
                            x-collapse
                        >
                            @foreach ($visits as $visit)
                                <a
                                    wire:navigate
                                    href="{{ $visit }}"
                                    class="dark:text-light dark:hover:bg-indigo flex items-center rounded-md py-2 text-gray-500 text-white transition-colors hover:bg-gray-800/50"
                                >
                                    <div class="w-16 flex-none">
                                        <div
                                            class="flex w-full justify-center text-white"
                                        >
                                            <x-icon
                                                :name="$navigations->first(fn ($item) => str_starts_with($visit, data_get($item, 'uri')) && data_get($item, 'uri') !== '/')['icon'] ?? 'no-symbol'"
                                                class="h-4 w-4"
                                            />
                                        </div>
                                    </div>
                                    <span class="truncate text-sm text-white">
                                        {{ $visit }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (! is_null($favorites))
                    <div class="whitespace-nowrap">
                        <div
                            x-on:click="favoritesOpen = ! favoritesOpen"
                            class="dark:text-light dark:hover:bg-indigo flex cursor-pointer items-center rounded-md py-2 text-gray-500 text-white transition-colors hover:bg-gray-800/50"
                        >
                            <div class="w-16 flex-none">
                                <div
                                    class="flex w-full justify-center text-white"
                                >
                                    <x-icon
                                        name="star"
                                        variant="solid"
                                        class="h-4 w-4 fill-amber-400"
                                    />
                                </div>
                            </div>
                            <span class="truncate text-sm text-white">
                                {{ __("Favorites") }}
                            </span>
                            <span aria-hidden="true" class="ml-auto pl-2 pr-2">
                                <x-icon
                                    name="chevron-left"
                                    class="h-4 w-4 transform text-white transition-transform"
                                    x-bind:class="favoritesOpen && '-rotate-90'"
                                />
                            </span>
                        </div>
                        <div
                            x-show="favoritesOpen"
                            x-cloak
                            x-collapse
                            class="max-w-full"
                        >
                            @foreach ($favorites as $favorite)
                                <div class="flex justify-between">
                                    <a
                                        wire:navigate
                                        href="{{ $favorite["url"] }}"
                                        class="dark:text-light dark:hover:bg-indigo flex flex-1 items-center overflow-hidden rounded-md py-2 text-gray-500 text-white transition-colors hover:bg-gray-800/50"
                                    >
                                        <div class="w-16 flex-none">
                                            <div
                                                class="flex w-full justify-center text-white"
                                            >
                                                <x-icon
                                                    :name="$navigations->first(fn ($item) => str_starts_with($favorite['url'], data_get($item, 'uri')) && data_get($item, 'uri') !== '/')['icon'] ?? 'no-symbol'"
                                                    class="h-4 w-4"
                                                />
                                            </div>
                                        </div>
                                        <div
                                            class="truncate text-sm text-white"
                                        >
                                            {{ $favorite["name"] }}
                                        </div>
                                    </a>
                                    <div
                                        class="truncate"
                                        x-show="menuOpen"
                                        x-transition
                                        x-cloak
                                    >
                                        <x-button.circle
                                            xs
                                            color="red"
                                            icon="trash"
                                            wire:click="deleteFavorite({{ $favorite['id'] }})"
                                            wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Favorite')]) }}"
                                        />
                                    </div>
                                </div>
                            @endforeach

                            <x-button
                                x-bind:class="! menuOpen && 'invisible'"
                                color="emerald"
                                class="w-full"
                                icon="plus"
                                :text="__('Add')"
                                wire:click="addFavorite(window.location.pathname + window.location.search, $promptValue())"
                                wire:flux-confirm.prompt="{{  __('New Favorite') }}||{{  __('Cancel') }}|{{  __('Save') }}"
                            />
                        </div>
                    </div>
                @endif
            </nav>
        </x-flux::nav.nav>
    </div>
</div>
