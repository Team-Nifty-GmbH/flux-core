<!DOCTYPE html>

@props(['navigation' => request()->boolean('no-navigation')])
@php($skipPersistedBlocks = request()->hasHeader('X-Livewire-Navigate'))
<html
    x-data="tallstackui_darkTheme()"
    @class([
        'sort-scrollbar',
        'h-full',
        'text-sm',
    ])
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
>
<head>
    <title>{{ $title ?? config('app.name', 'Flux ERP') }}</title>
    <x-flux::layouts.head.head />
</head>
<body
    x-bind:class="{
        'dark bg-secondary-800': darkTheme,
        'bg-slate-50': !darkTheme,
    }"
    x-bind:data-color-scheme="darkTheme ? 'dark' : 'light'"
    class="text-secondary-600 dark:text-secondary-50 h-full transition duration-300"
>
    @section('wire.navigate.spinner')
        @persist('spinner')
            @unless ($skipPersistedBlocks)
                <div
                    id="loading-overlay"
                    class="fixed inset-0 hidden overflow-y-auto p-4"
                    style="z-index: 1000"
                >
                    <div
                        id="loading-overlay-spinner"
                        class="bg-secondary-400 bg-opacity-60 dark:bg-secondary-700 dark:bg-opacity-60 fixed inset-0 flex items-center justify-center opacity-0 transition-opacity duration-200"
                    >
                        <x-flux::spinner-svg />
                    </div>
                </div>
            @endunless
        @endpersist

    @show
    @persist('notifications')
        @unless ($skipPersistedBlocks)
            @if (auth()->check() && auth()->id())
                <div
                    id="{{ \Illuminate\Support\Str::uuid() }}"
                    x-on:ts-ui:toast-upsert.window="
                        $tallstackuiToast($el.id).upsertToast($event)
                    "
                >
                    <x-toast />
                </div>
            @endif
            <x-dialog />
            <x-nuxbe-lightbox />
        @endunless
    @endpersist

    @auth('web')
        @persist('mail')
            @unless ($skipPersistedBlocks)
                <div id="mail">
                    <livewire:edit-mail lazy />
                </div>
                <div
                    x-data="{
                        openUrl() {
                            let urlObj = new URL(
                                $el.querySelector('iframe').src,
                            );
                            urlObj.searchParams.delete('no-navigation');

                            window.open(urlObj);
                            $tsui.close.modal('detail-modal');
                        },
                    }"
                >
                    <x-modal
                        id="detail-modal"
                        size="7xl"
                        x-on:close="
                            $el.querySelector('iframe').src =
                                'data:text/html;charset=utf-8,%3Chtml%3E%3Cbody%3E%3C%2Fbody%3E%3C%2Fhtml%3E'
                        "
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
                                x-on:click="$tsui.close.modal('detail-modal')"
                            />
                            <x-button
                                color="indigo"
                                :text="__('Open')"
                                x-on:click="openUrl()"
                            />
                        </x-slot:footer>
                    </x-modal>
                </div>
            @endunless
        @endpersist
        @persist('record-merging')
            @unless ($skipPersistedBlocks)
                <livewire:record-merging lazy />
            @endunless
        @endpersist
        @persist('layout-global-components')
            @unless ($skipPersistedBlocks)
                @stack('layout-global-components')
            @endunless
        @endpersist
    @endauth

    <x-flux::layout>
        @if (
            ! $navigation
            && auth()->check()
            && auth()->id()
            && ! request()->routeIs('logout')
            && ! request()->routeIs('two-factor.setup')
        )
            <x-slot:header>
                <x-layout.header without-mobile-button>
                    <x-button
                        flat
                        class="md:hidden"
                        icon="bars-4"
                        x-on:click="$dispatch('menu-force-open')"
                    />
                    @auth('web')
                        <div
                            x-persist="layout.header.search - bar"
                            class="hidden grow sm:block"
                        >
                            <livewire:features.search-bar />
                        </div>
                    @endauth

                    <div class="grow sm:hidden"></div>

                    <div class="flex shrink-0 gap-2">
                        @auth('web')
                            <div
                                x-persist="layout.header.search - bar - mobile"
                            >
                                <livewire:features.search-bar
                                    :mobile="true"
                                    lazy
                                />
                            </div>
                        @endauth

                        <x-button
                            x-data
                            x-show="$nuxbe.isAppMode()"
                            x-cloak
                            flat
                            icon="arrow-path"
                            :title="__('Refresh')"
                            x-on:click="window.location.reload()"
                        />

                        @if (resolve_static(\FluxErp\Models\PriceList::class, 'default'))
                            @persist('layout.header.cart')
                                @unless ($skipPersistedBlocks)
                                    @canAction(\FluxErp\Actions\Cart\CreateCart::class)
                                        <livewire:cart.cart lazy />
                                    @endcanAction
                                @endunless
                            @endpersist
                        @endif

                        @auth('web')
                            @persist('layout.header.work-time')
                                @unless ($skipPersistedBlocks)
                                    @canAction(\FluxErp\Actions\WorkTime\CreateWorkTime::class)
                                        <livewire:work-time lazy />
                                    @endcanAction
                                @endunless
                            @endpersist
                        @endauth

                        @persist('layout.header.notifications')
                            @unless ($skipPersistedBlocks)
                                <livewire:features.notifications lazy />
                            @endunless
                        @endpersist
                    </div>
                </x-layout.header>
            </x-slot:header>
        @endif

        @if (
            ! $navigation
            && auth()->check()
            && auth()->id()
            && ! request()->routeIs('logout')
            && ! request()->routeIs('two-factor.setup')
            && method_exists(auth()->guard(), 'getName')
        )
            <x-slot:menu>
                @php($navigation = true)
                @persist('navigation')
                    @unless ($skipPersistedBlocks)
                        <div id="nav">
                            <livewire:navigation />
                        </div>
                    @endunless
                @endpersist
            </x-slot:menu>
        @endif

        {{ $slot }}
    </x-flux::layout>
</body>
</html>
