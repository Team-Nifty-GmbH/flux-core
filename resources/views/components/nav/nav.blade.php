<!-- Static sidebar for desktop -->
<div
    style="{!! $background !!}"
    x-on:mouseover.away="closeMenu()"
    x-on:mouseover="showMenu()"
    x-bind:class="menuOpen && '!w-full md:!w-72'"
    class="soft-scrollbar fixed inset-y-0 top-0 z-20 w-0 overflow-y-auto bg-flux-secondary-500 transition-all duration-500 ease-in-out md:block md:flex md:w-20 md:flex-col"
>
    <!-- Sidebar component, swap this element with another sidebar if you like -->
    <div class="soft-scrollbar flex flex-grow flex-col overflow-x-hidden">
        <div class="relative flex h-16 shrink-0 justify-center p-2 px-4">
            <x-flux::logo fill="#D7E3EC" />
            <x-button.circle
                color="secondary"
                light
                icon="x-mark"
                x-on:click="closeMenu(true)"
                class="absolute right-6 top-6 block md:hidden"
            />
        </div>
        <!-- User Menu -->
        <div>
            <div class="flex flex-1 flex-col pt-0">
                <div class="whitespace-nowrap pb-8 pt-4">
                    <x-dropdown>
                        <x-slot:action>
                            <div
                                class="-ml-2 flex cursor-pointer px-4"
                                x-on:click="show = !show"
                            >
                                <div class="flex w-16 flex-none justify-center">
                                    <div class="">
                                        <x-avatar
                                            lg
                                            :image="auth()->user()->getAvatarUrl()"
                                        />
                                    </div>
                                </div>
                                <div
                                    class="easy-in-out flex content-center pl-2 text-white transition"
                                >
                                    <div>
                                        <div>{{ __("Logged in as:") }}</div>
                                        <div class="font-bold">
                                            {{ auth()->user()->name }}
                                        </div>
                                    </div>
                                    <div class="flex items-center pl-5">
                                        <x-icon
                                            name="chevron-down"
                                            class="h-4 w-4 text-white"
                                        />
                                    </div>
                                </div>
                            </div>
                        </x-slot>
                        <a href="{{ route("my-profile") }}">
                            <x-dropdown.items :text="__('My profile')" />
                        </a>
                        <x-dropdown.items
                            x-on:click="document.getElementById('logout-form-desktop').submit()"
                            :text="__('Logout')"
                        />
                        <div class="mb-2 ml-4">
                            <x-theme-switch />
                        </div>
                    </x-dropdown>
                    <div class="hidden">
                        <!-- Authentication -->
                        <form
                            id="logout-form-desktop"
                            method="POST"
                            class="text-white hover:bg-gray-500"
                            action="{{ route("logout", absolute: false) }}"
                        >
                            @csrf
                        </form>
                    </div>
                </div>
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
