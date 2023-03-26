<div x-data="{mobileOpen: false, desktopOpen: false}">
    <div x-show="mobileOpen" x-cloak class="fixed inset-0 z-40 flex"
         x-ref="dialog"
         aria-modal="true">
        <div x-show="mobileOpen" x-cloak x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-600"
             @click="mobileOpen = false"
             aria-hidden="true">
        </div>

        <div x-show="mobileOpen" x-cloak x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
            {{ $attributes->merge(['class' => 'relative flex-1 flex flex-col sm:max-w-xs w-full pt-5 pb-4 secondary-container']) }}
        >

            <div x-show="mobileOpen"
                 x-cloak
                 x-transition:enter="ease-in-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in-out duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute top-0 right-0 -mr-12 pt-2">
                <button type="button"
                        class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                        @click="mobileOpen = false">
                    <span class="sr-only">Close sidebar</span>
                    <svg class="h-6 w-6 text-white"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="ml-12 flex h-24 place-content-center p-1 px-4">
                <x-logo class="h-24" />
            </div>
            <div class="mt-5 h-0 flex-1 overflow-y-auto">
                <div class="whitespace-nowrap pb-8 pt-4">
                    <x-dropdown>
                        <x-slot name="trigger">
                            <div class="-ml-2 flex cursor-pointer px-4">
                                <div class="flex w-16 flex-none justify-center">
                                    <div class="">
                                        <x-avatar lg :src="auth()->user()->getAvatarUrl()" />
                                    </div>
                                </div>
                                <div class="easy-in-out flex content-center pl-2 text-white transition">
                                    <div>
                                        <div>{{ __('Logged in as:') }}</div>
                                        <div
                                            class="font-bold">{{ auth()->user()->name }}</div>
                                    </div>
                                    <div class="flex items-center pl-5">
                                        <x-icon name="chevron-down" class="h-4 w-4 text-white" />
                                    </div>
                                </div>
                            </div>
                        </x-slot>
                        <x-dropdown.item :label="__('My profile')" href="/my-profile" />
                        <x-dropdown.item :label="__('Logout')"
                                         @click="document.getElementById('logout-form').submit()"/>
                        <x-dropdown.item>
                            <livewire:toggle-dark-mode />
                        </x-dropdown.item>
                    </x-dropdown>
                    <div class="hidden">
                        <form id="logout-form" method="POST" class="text-white hover:bg-gray-500"
                              action="{{ route('logout') }}">
                            @csrf
                        </form>
                    </div>
                </div>
                {{ $slot }}
            </div>
        </div>
        <div class="w-14 flex-shrink-0" aria-hidden="true">
            <!-- Dummy element to force sidebar to shrink to fit close icon -->
        </div>
    </div>
    <div class="dark:bg-secondary-900 top-0 z-20 flex h-12 flex-shrink-0 bg-white shadow dark:text-gray-50 md:hidden">
        <div class="flex flex-1 justify-between px-4 align-middle">
            <button type="button"
                    class="h-full border-r border-gray-200 px-4 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
                    @click="mobileOpen = true; $dispatch('desktop-toggled', {desktopOpen: true})">
                <svg class="h-6 w-6"
                     xmlns="http://www.w3.org/2000/svg"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h7"></path>
                </svg>
            </button>
        </div>
    </div>
    <!-- Static sidebar for desktop -->
    <div
        style="{!! $background !!}"
        x-on:mouseover.away="desktopOpen = false; $dispatch('desktop-toggled', {desktopOpen: false})"
        x-on:mouseover="desktopOpen = true; $dispatch('desktop-toggled', {desktopOpen: true})"
        class="relative z-10 hidden bg-gray-700 md:fixed md:inset-y-0 md:block md:flex md:flex-col">
        <!-- Sidebar component, swap this element with another sidebar if you like -->
        <div
            :style="desktopOpen && {width: '18rem'}"
            class="soft-scrollbar flex w-20 flex-grow flex-col overflow-y-auto overflow-x-hidden transition-all duration-500 ease-in-out">
            <div class="flex- relative flex h-16 shrink-0 justify-center p-2 px-4">
                <x-logo fill="#FFFFFF" />
            </div>
            <!-- User Menu -->
            <div>
                <div class="flex flex-1 flex-col pt-0">
                    <div class="whitespace-nowrap pb-8 pt-4">
                        <x-dropdown>
                            <x-slot name="trigger">
                                <div class="-ml-2 flex cursor-pointer px-4">
                                    <div class="flex w-16 flex-none justify-center">
                                        <div class="">
                                            <x-avatar lg :src="auth()->user()->getAvatarUrl()" />
                                        </div>
                                    </div>
                                    <div class="easy-in-out flex content-center pl-2 text-white transition">
                                        <div>
                                            <div>{{ __('Logged in as:') }}</div>
                                            <div
                                                class="font-bold">{{ auth()->user()->name }}</div>
                                        </div>
                                        <div class="flex items-center pl-5">
                                            <x-icon name="chevron-down" class="h-4 w-4 text-white" />
                                        </div>
                                    </div>
                                </div>
                            </x-slot>
                            <x-dropdown.item :label="__('My profile')" href="/my-profile" />

                            <x-dropdown.item :label="__('Logout')"
                                             @click="document.getElementById('logout-form').submit()"/>
                            <x-dropdown.item>
                                <livewire:toggle-dark-mode />
                            </x-dropdown.item>
                        </x-dropdown>
                        <div class="hidden">
                            <!-- Authentication -->
                            <form id="logout-form-desktop" method="POST" class="text-white hover:bg-gray-500"
                                  action="{{ route('logout') }}">
                                @csrf
                            </form>
                        </div>
                    </div>
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>
