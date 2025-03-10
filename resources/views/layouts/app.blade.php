<!DOCTYPE html>
@props(['navigation' => request()->boolean('no-navigation')])
<html x-data="tallstackui_darkTheme()" @class([
        'sort-scrollbar',
        'h-full',
        'text-sm'
    ]
) lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ $title ?? config('app.name', 'Flux ERP') }}</title>
    <x-flux::layouts.head.head/>
</head>
<body x-bind:class="{ 'dark bg-secondary-800': darkTheme, 'bg-slate-50': !darkTheme }" class="h-full transition duration-300 text-secondary-600 dark:text-secondary-50">
    @section('wire.navigate.spinner')
        @persist('spinner')
            <div id="loading-overlay" class="fixed inset-0 overflow-y-auto p-4 hidden" style="z-index: 1000;">
                <div id="loading-overlay-spinner" class="fixed inset-0 bg-secondary-400 dark:bg-secondary-700 bg-opacity-60 dark:bg-opacity-60 flex items-center justify-center transition-opacity opacity-0 duration-200">
                    <x-flux::spinner-svg />
                </div>
            </div>
        @endpersist
    @show
    @section('layout.app.body')
        @persist('notifications')
            <div id="{{ \Illuminate\Support\Str::uuid() }}" x-on:tallstackui:toast-upsert.window="$tallstackuiToast($el.id).upsertToast($event)">
                <x-toast z-index="z-50"></x-toast>
            </div>
            <x-dialog z-index="z-40" blur="md" align="center"/>
        @endpersist
        <x-flux::flash />
        @auth('web')
            @persist('mail')
                <div id="mail">
                    <livewire:edit-mail lazy />
                </div>
                <x-modal
                    id="detail-modal"
                    size="7xl"
                    x-on:close="$el.querySelector('iframe').src = 'data:text/html;charset=utf-8,%3Chtml%3E%3Cbody%3E%3C%2Fbody%3E%3C%2Fhtml%3E'"
                >
                    <div
                        class="w-full"
                        x-data="{
                            openUrl() {
                                let urlObj = new URL($el.querySelector('iframe').src);
                                urlObj.searchParams.delete('no-navigation');

                                window.open(urlObj);
                                close();
                            }
                        }"
                    >
                        <x-card class="grid h-screen">
                            <iframe class="object-contain" height="100%" width="100%" id="detail-modal-iframe" src="data:text/html;charset=utf-8,%3Chtml%3E%3Cbody%3E%3C%2Fbody%3E%3C%2Fhtml%3E">
                            </iframe>
                            <x-slot:footer>
                                <x-button color="secondary" light :text="__('Cancel')" x-on:click="$modalClose('detail-modal')"/>
                                <x-button color="indigo" :text="__('Open')" x-on:click="openUrl()"/>
                            </x-slot:footer>
                        </x-card>
                    </div>
                </x-modal>
            @endpersist
        @endauth
        @section('layout.app.body.content')
            <div x-data="{ open: false }" x-on:keydown.window.escape="open = false" class="flex h-screen w-full flex-col">
                @section('layout.app.body.content.navigation')
                    @if(auth()->check() && method_exists(auth()->guard(), 'getName') && ! $navigation)
                        @php($navigation = true)
                        @persist('navigation')
                            <div id="nav">
                                <livewire:navigation />
                            </div>
                        @endpersist
                    @endif
                @show
                @section('layout.app.body.content.main')
                    <div @if($navigation) class="md:pl-20" @endif>
                        <main @if($navigation) class="px-1.5 md:px-8 pb-1.5 md:pb-8" @endif>
                            {{ $slot }}
                        </main>
                    </div>
                @show
            </div>
        @show
    @show
</body>
</html>
