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
    <x-nav.nav :background="$background">
        <nav x-data="{activeItem: window.location.pathname.split('/')[1] || 'dashboard', activeSubItem: window.location.pathname}"
             class="flex-1 space-y-2 overflow-x-hidden overflow-y-hidden px-2 py-4 hover:overflow-y-auto">
            <div
                x-on:activatesubitem="activeSubItem = $event.detail.subitem, activeItem = $event.detail.item, mobileOpen = false">
                @foreach($navigations as $key => $navigation)
                    <div x-data="{open: false, desktopOpen: false}" x-on:desktop-toggled.window="desktopOpen = $event.detail.desktopOpen">
                        <!-- active & hover classes 'bg-primary-100 dark:bg-primary' -->
                        <a
                           x-bind:class="activeItem === '{{ $key }}' && 'bg-primary-500 dark:bg-primary-700 !text-white hover:bg-primary-600 nav-item-active'"
                           @if(count($navigation['children']) > 1)
                               @click.prevent="open = !open"
                                href="#"
                           @else
                               href="{{ $navigation['uri'] }}"
                                target="{{ ($navigation['target_blank'] ?? false) ? '_blank' : '' }}"
                                x-on:click="activeItem = '{{ $key }}'; activeSubItem = ''; mobileOpen = false"
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
                                    <!-- active class 'rotate-180' -->
                                    <svg class="h-4 w-4 transform transition-transform" :class="{ 'rotate-180': open }"
                                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </span>
                            @endif
                        </a>
                        @if(count($navigation['children']) > 1)
                            <div x-show="open && desktopOpen" x-transition class="mt-2 space-y-2 overflow-x-hidden text-white" role="menu"
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
