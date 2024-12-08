<!-- Static sidebar for desktop -->
<div
    style="{!! $background !!}"
    x-on:mouseover.away="closeMenu()"
    x-on:mouseover="showMenu()"
    x-bind:class="menuOpen && '!w-full md:!w-72'"
    class="z-10 bg-flux-secondary-500 fixed w-0 md:w-20 inset-y-0 md:block overflow-y-auto soft-scrollbar md:flex md:flex-col top-0 transition-all duration-500 ease-in-out"
>
    <!-- Sidebar component, swap this element with another sidebar if you like -->
    <div class="soft-scrollbar flex flex-grow flex-col overflow-x-hidden">
        <div class="flex relative flex h-16 shrink-0 justify-center p-2 px-4">
            <x-flux::logo fill="#D7E3EC" />
            <x-mini-button icon="x-mark" x-on:click="closeMenu(true)" class="absolute top-6 right-6 block md:hidden" />
        </div>
        <!-- User Menu -->
        <div>
            <div class="flex flex-1 flex-col pt-0">
                <div class="whitespace-nowrap pb-8 pt-4">
                    <x-dropdown align="left">
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
                                         x-on:click="document.getElementById('logout-form-desktop').submit()"/>
                        <x-dropdown.item>
                            <livewire:toggle-dark-mode />
                        </x-dropdown.item>
                    </x-dropdown>
                    <div class="hidden">
                        <!-- Authentication -->
                        <form id="logout-form-desktop" method="POST" class="text-white hover:bg-gray-500"
                              action="{{ route('logout', absolute: false) }}">
                            @csrf
                        </form>
                    </div>
                </div>
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
