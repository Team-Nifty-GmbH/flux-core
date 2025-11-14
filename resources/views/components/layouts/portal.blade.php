@php
    $tenant = auth()->user()?->contact->tenant ?? resolve_static(\FluxErp\Models\Tenant::class, 'default');
@endphp

<!DOCTYPE html>
<html
    x-data="tallstackui_darkTheme()"
    class="font-portal h-full"
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
>
    <head>
        <x-flux::layouts.head.head>
            <x-slot:title>
                {{ $tenant?->name . ' Portal' }}
            </x-slot>
        </x-flux::layouts.head.head>
    </head>
    <body class="h-full bg-gray-50 text-xs dark:bg-secondary-900">
        <x-toast z-index="z-50"></x-toast>
        <x-dialog z-index="z-40" blur="md" align="center" />
        <x-dialog z-index="z-40" blur="md" align="center" id="prompt">
            <x-input id="prompt-value" />
        </x-dialog>
        <x-flux::flash />
        <div
            x-data="{
                openUrl() {
                    let urlObj = new URL($el.querySelector('iframe').src)
                    urlObj.searchParams.delete('no-navigation')

                    window.open(urlObj)
                    $modalClose('detail-modal')
                },
            }"
        >
            <x-modal
                id="detail-modal"
                size="7xl"
                x-on:close="$el.querySelector('iframe').src = 'data:text/html;charset=utf-8,%3Chtml%3E%3Cbody%3E%3C%2Fbody%3E%3C%2Fhtml%3E'"
            >
                <div class="grid h-screen w-full">
                    <iframe
                        class="object-contain"
                        height="100%"
                        width="100%"
                        id="detail-modal-iframe"
                        src="data:text/html;charset=utf-8,%3Chtml%3E%3Cbody%3E%3C%2Fbody%3E%3C%2Fhtml%3E"
                    ></iframe>
                </div>
                <x-slot:footer>
                    <x-button
                        color="secondary"
                        light
                        :text="__('Cancel')"
                        x-on:click="$modalClose('detail-modal')"
                    />
                    <x-button
                        color="indigo"
                        :text="__('Open')"
                        x-on:click="openUrl()"
                    />
                </x-slot>
            </x-modal>
        </div>
        <div
            class="flex w-full items-center justify-between gap-1.5 p-2 shadow"
        >
            <x-button
                flat
                icon="bars-4"
                x-on:click="$dispatch('menu-force-open')"
            />
            <div class="flex gap-1.5">
                <x-link
                    icon="arrow-up-right"
                    :href="$tenant?->website"
                    target="_blank"
                    :text="__('Return to website')"
                    class="font-bold"
                />
                @auth('address')
                    @can(route_to_permission('portal.watchlists'))
                        <x-button
                            color="secondary"
                            light
                            icon="heart"
                            wire:navigate
                            :href="route('portal.watchlists')"
                        />
                    @endcan

                    @can(route_to_permission('portal.checkout'))
                        @persist('cart')
                            <livewire:portal.shop.cart />
                        @endpersist
                    @endcan
                @endauth
            </div>
        </div>
        @auth('address')
            <div id="nav">
                <livewire:navigation :show-search-bar="false" />
            </div>
        @endauth

        <div class="flex flex-1 flex-col md:pl-20">
            @section('main')
            <div class="h-full w-full p-4 lg:p-8">
                {{ $slot }}
            </div>
            @show
        </div>
    </body>
</html>
