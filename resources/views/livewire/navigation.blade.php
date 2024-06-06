<div>
    @if($setting)
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
    <div id="main-navigation" x-data="{
            init() {
                document.addEventListener('livewire:navigating', () => {
                    this.closeMenu(true);
                });
                this.activeItem = window.location.pathname.split('/')[1] || 'dashboard';
            },
            activeItem: 'dashboard',
            activeSubItem: window.location.pathname,
            open: [],
            toggleMenu(key) {
                if (this.isOpen(key)) {
                    this.open = this.open.filter(item => item !== key)
                } else {
                    this.open.push(key)
                }
            },
            isOpen(key) {
                return this.open.includes(key) && this.menuOpen;
            },
            showMenu(force = null) {
                if (this.forced && ! force) {
                    return;
                }

                this.menuOpen = true;
                if (force) {
                    this.forced = true;
                }
            },
            closeMenu(force = null) {
                if (this.forced && ! force) {
                    return;
                }

                this.menuOpen = false;
            },
            forced: false,
            menuOpen: false,
            frequentlyVisitedOpen: false,
            favoritesOpen: false,
        }"
    >
        <div class="pb-4 md:pt-4 md:pr-8 md:pl-28">
            @if($showSearchBar)
                <div>
                    <x-card class="flex w-full gap-2">
                        <x-button
                            class="block md:hidden"
                            icon="menu"
                            primary
                            x-on:click="showMenu(true)"
                        />
                        <livewire:features.search-bar />
                        <div class="flex gap-1.5" wire:ignore>
                            <livewire:work-time lazy />
                            <livewire:features.notifications lazy />
                        </div>
                    </x-card>
                </div>
            @else
                <x-card class="block md:hidden">
                    <x-button primary icon="menu" x-on:click="showMenu(true)" />
                </x-card>
            @endif
        </div>
        <x-nav.nav :background="$background">
            <nav class="flex-1 space-y-2 overflow-x-hidden overflow-y-hidden px-2 py-4 hover:overflow-y-auto flex flex-col gap-6">
                <div>
                    @foreach($navigations as $key => $navigation)
                        <div>
                            <a
                                x-bind:class="activeItem === '{{ $key }}' && 'bg-primary-500 dark:bg-primary-700 !text-white hover:bg-primary-600 nav-item-active'"
                                @if($navigation['children'] ?? false)
                                    x-on:click.prevent="toggleMenu('{{ $key }}')"
                                @else
                                    href="{{ data_get($navigation, 'uri', '#') }}"
                                    target="{{ ($navigation['target_blank'] ?? false) ? '_blank' : '' }}"
                                    x-on:click="activeItem = '{{ $key }}'; activeSubItem = ''"
                                @endif
                                class="dark:text-light dark:hover:bg-primary flex items-center rounded-md py-2 text-white text-gray-500 transition-colors hover:bg-gray-800/50"
                                role="button" aria-haspopup="true"
                            >
                                <div class="w-16 flex-none">
                                    <div class="flex w-full justify-center text-white">
                                        <x-heroicons :name="$navigation['icon'] ?? 'no-symbol'" class="h-4 w-4" />
                                    </div>
                                </div>
                                <span class="truncate text-sm text-white"> {{ __($navigation['label'] ?? $key) }} </span>
                                @if($navigation['children'] ?? false)
                                    <span aria-hidden="true" class="ml-auto pl-2 pr-2">
                                        <x-icon
                                            name="chevron-left"
                                            class="h-4 w-4 text-white transform transition-transform"
                                            x-bind:class="{ '-rotate-90': isOpen('{{ $key }}') }"
                                        />
                                    </span>
                                @endif
                            </a>
                            @if($navigation['children'] ?? false)
                                <div
                                    x-show="isOpen('{{ $key }}')"
                                    x-cloak
                                    x-transition
                                    class="mt-2 space-y-2 overflow-x-hidden text-white"
                                >
                                    @foreach($navigation['children'] as $child)
                                        <a x-on:click="activeSubItem = '{{ data_get($child, 'uri', '#') }}'; activeItem = '{{ $key }}'"
                                           href="{{ $child['uri'] }}" role="menuitem"
                                           :class="activeSubItem === '{{ data_get($child, 'uri', '#') }}' && 'rounded-md bg-primary-600/50 dark:bg-primary-700/5 hover:bg-primary-600/10'"
                                           class="dark:hover:text-light block truncate rounded-md p-2 pl-20 text-sm transition-colors duration-200 hover:bg-gray-800/50">
                                            {{ __($child['label']) }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if(! is_null($visits))
                    <div class="whitespace-nowrap">
                        <div x-on:click="frequentlyVisitedOpen = ! frequentlyVisitedOpen" class="cursor-pointer dark:text-light dark:hover:bg-primary flex items-center rounded-md py-2 text-white text-gray-500 transition-colors hover:bg-gray-800/50">
                            <div class="w-16 flex-none">
                                <div class="flex w-full justify-center text-white">
                                    <x-heroicons name="clock" class="h-4 w-4" />
                                </div>
                            </div>
                            <span class="truncate text-sm text-white">{{ __('Frequently visited') }}</span>
                            <span aria-hidden="true" class="ml-auto pl-2 pr-2">
                                <x-icon
                                    name="chevron-left"
                                    class="h-4 w-4 text-white transform transition-transform"
                                    x-bind:class="frequentlyVisitedOpen && '-rotate-90'"
                                />
                            </span>
                        </div>
                        <div x-show="frequentlyVisitedOpen" x-cloak x-collapse>
                            @foreach($visits as $visit)
                                <a
                                    wire:navigate
                                    href="{{ $visit }}"
                                    class="dark:text-light dark:hover:bg-primary flex items-center rounded-md py-2 text-white text-gray-500 transition-colors hover:bg-gray-800/50"
                                >
                                    <div class="w-16 flex-none">
                                        <div class="flex w-full justify-center text-white">
                                            <x-heroicons
                                                :name="$navigations->first(fn ($item) => str_starts_with($visit, data_get($item, 'uri')) && data_get($item, 'uri') !== '/')['icon'] ?? 'no-symbol'"
                                                class="h-4 w-4"
                                            />
                                        </div>
                                    </div>
                                    <span class="truncate text-sm text-white"> {{ $visit }} </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if(! is_null($favorites))
                    <div class="whitespace-nowrap">
                        <div x-on:click="favoritesOpen = ! favoritesOpen" class="cursor-pointer dark:text-light dark:hover:bg-primary flex items-center rounded-md py-2 text-white text-gray-500 transition-colors hover:bg-gray-800/50">
                            <div class="w-16 flex-none">
                                <div class="flex w-full justify-center text-white">
                                    <x-heroicons name="star" variant="solid" class="h-4 w-4 fill-warning-400" />
                                </div>
                            </div>
                            <span class="truncate text-sm text-white">{{ __('Favorites') }}</span>
                            <span aria-hidden="true" class="ml-auto pl-2 pr-2">
                            <x-icon
                                name="chevron-left"
                                class="h-4 w-4 text-white transform transition-transform"
                                x-bind:class="favoritesOpen && '-rotate-90'"
                            />
                        </span>
                        </div>
                        <div x-show="favoritesOpen" x-cloak x-collapse class="max-w-full">
                            @foreach($favorites as $favorite)
                                <div class="flex justify-between">
                                    <a
                                        wire:navigate
                                        href="{{ $favorite['url'] }}"
                                        class="flex-1 overflow-hidden dark:text-light dark:hover:bg-primary flex items-center rounded-md py-2 text-white text-gray-500 transition-colors hover:bg-gray-800/50"
                                    >
                                        <div class="w-16 flex-none">
                                            <div class="flex w-full justify-center text-white">
                                                <x-heroicons
                                                    :name="$navigations->first(fn ($item) => str_starts_with($favorite['url'], $item['uri']) && $item['uri'] !== '/')['icon'] ?? 'no-symbol'"
                                                    class="h-4 w-4"/>
                                            </div>
                                        </div>
                                        <div class="truncate text-sm text-white"> {{ $favorite['name'] }} </div>
                                    </a>
                                    <div class="truncate" x-show="menuOpen" x-transition x-cloak>
                                        <x-button.circle
                                            xs
                                            negative
                                            icon="trash"
                                            wire:click="deleteFavorite({{ $favorite['id'] }})"
                                            wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Favorite')]) }}"
                                        />
                                    </div>
                                </div>
                            @endforeach
                            <x-button
                                x-bind:class="! menuOpen && 'invisible'"
                                positive
                                class="w-full"
                                icon="plus"
                                :label="__('Add')"
                                wire:click="addFavorite(window.location.pathname + window.location.search, $promptValue())"
                                wire:flux-confirm.prompt="{{  __('New Favorite') }}||{{  __('Cancel') }}|{{  __('Save') }}"
                            />
                        </div>
                    </div>
                @endif
            </nav>
        </x-nav.nav>
    </div>
</div>
