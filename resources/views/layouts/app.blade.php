<!DOCTYPE html>
@props(['navigation' => request()->get('no-navigation', false)])
<html class="soft-scrollbar h-full text-sm" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-layouts.head.head/>
</head>
<body class="dark:bg-secondary-900 h-full bg-slate-50 transition duration-300">
    <x-notifications z-index="z-50"></x-notifications>
    <x-dialog z-index="z-40" blur="md" align="center"/>
    <x-dialog z-index="z-40" blur="md" align="center" id="prompt">
        <x-input id="prompt-value" />
    </x-dialog>
    @if(auth('web')->check() && method_exists(auth()->guard(), 'getName'))
        @persist('mail')
            <div id="mail">
                <livewire:edit-mail/>
            </div>
        @endpersist
        @persist('detail-modal')
            <x-modal
                name="detail-modal"
                max-width="7xl"
                x-on:close="$el.querySelector('embed').src = '#'"
            >
                <div
                    class="w-full"
                    x-data="{
                        openUrl() {
                            let urlObj = new URL($el.querySelector('embed').src);
                            urlObj.searchParams.delete('no-navigation');

                            window.open(urlObj);
                            close();
                        }
                    }"
                >
                    <x-card class="grid h-screen">
                        <embed class="object-contain" height="100%" width="100%" id="detail-modal-embed" src="#" />
                        <x-slot:footer>
                            <div class="w-full flex justify-end gap-1.5">
                                <x-button :label="__('Cancel')" x-on:click="close"/>
                                <x-button primary :label="__('Open')" x-on:click="openUrl()"/>
                            </div>
                        </x-slot:footer>
                    </x-card>
                </div>
            </x-modal>
        @endpersist
    @endif
    <div x-data="{ open: false }" x-on:keydown.window.escape="open = false" class="flex h-screen w-full flex-col">
        @if(auth()->check() && ! request()->get('no-navigation', false))
            @php($navigation = true)
            @persist('navigation')
                <div id="nav">
                    <livewire:navigation/>
                </div>
            @endpersist
        @endif
        <div @if(! $navigation) class="md:pl-20" @endif>
            <main @if($navigation) class="px-1.5 md:px-8 pb-1.5 md:pb-8" @endif>
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
