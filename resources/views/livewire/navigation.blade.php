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
    <div  x-data="{
            activeItem: window.location.pathname.split('/')[1] || 'dashboard',
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
            menuOpen: false
        }"
    >
        <div class="pb-4 md:pt-4 md:pr-8 md:pl-28">
            @if($showSearchBar)
                <livewire:features.search-bar />
            @else
                <x-card class="block md:hidden">
                    <x-button primary icon="menu" x-on:click="showMenu(true)" />
                </x-card>
            @endif
        </div>
        <x-nav.nav :background="$background">
            <nav class="flex-1 space-y-2 overflow-x-hidden overflow-y-hidden px-2 py-4 hover:overflow-y-auto">
                <div>
                    @foreach($navigations as $key => $navigation)
                        <div>
                            <a
                                x-bind:class="activeItem === '{{ $key }}' && 'bg-primary-500 dark:bg-primary-700 !text-white hover:bg-primary-600 nav-item-active'"
                                @if(count($navigation['children']) > 1)
                                    x-on:click.prevent="toggleMenu('{{ $key }}')"
                                @else
                                    href="{{ $navigation['uri'] }}"
                                    target="{{ ($navigation['target_blank'] ?? false) ? '_blank' : '' }}"
                                    x-on:click="activeItem = '{{ $key }}'; activeSubItem = ''"
                                @endif
                                class="dark:text-light dark:hover:bg-primary flex items-center rounded-md py-2 text-white text-gray-500 transition-colors hover:bg-gray-800/50"
                                role="button" aria-haspopup="true">
                                <div class="w-16 flex-none">
                                    <div class="flex w-full justify-center text-white">
                                        <x-heroicons :name="$navigation['icon'] ?? 'no-symbol'" class="h-4 w-4" />
                                    </div>
                                </div>
                                <span class="truncate text-sm text-white"> {{ __($navigation['label']) }} </span>
                                @if(count($navigation['children']) > 1)
                                    <span aria-hidden="true" class="ml-auto pl-2 pr-2">
                                    <x-icon name="chevron-left" class="h-4 w-4 text-white transform transition-transform" x-bind:class="{ '-rotate-90': isOpen('{{ $key }}') }" />
                                </span>
                                @endif
                            </a>
                            @if(count($navigation['children']) > 1)
                                <div x-show="isOpen('{{ $key }}')" x-transition class="mt-2 space-y-2 overflow-x-hidden text-white" role="menu"
                                     aria-label="Authentication" x-cloak>
                                    @foreach($navigation['children'] as $child)
                                        <a x-on:click="$dispatch('activatesubitem', {subitem: '{{ $child['uri'] }}', item: '{{ $key }}'})"
                                           href="{{ $child['uri'] }}" role="menuitem"
                                           :class="activeSubItem === '{{ $child['uri'] }}' && 'rounded-md bg-primary-600/50 dark:bg-primary-700/5 hover:bg-primary-600/10'"
                                           class="dark:hover:text-light block truncate rounded-md p-2 pl-20 text-sm transition-colors duration-200 hover:bg-gray-800/50">
                                            {{ __($child['name']) }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </nav>
        </x-nav.nav>
    </div>
</div>
