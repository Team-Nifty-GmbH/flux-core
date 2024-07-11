<!DOCTYPE html>
@props(['navigation' => request()->get('no-navigation', false)])
<html @class([
        'sort-scrollbar',
        'h-full',
        'text-sm',
        'dark' => auth()->check() && auth()->user()->is_dark_mode,
    ]
) lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ $title ?? config('app.name', 'Flux ERP') }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="manifest" href="{{ route('manifest') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="ws-key" content="{{ config('flux.vite.reverb_app_key') }}">
    <meta name="ws-broadcaster" content="{{ config('broadcasting.default', 'reverb') }}">
    <meta name="ws-host" content="{{ config('flux.vite.reverb_host') }}">
    <meta name="ws-port" content="{{ config('flux.vite.reverb_port') }}">
    <meta name="ws-protocol" content="{{ config('flux.vite.reverb_protocol') }}">
    <meta name="webpush-key" content="{{ config('webpush.vapid.public_key') }}">
    <meta name="currency-code" content="{{ $defaultCurrency?->iso }}">
    <link rel="icon" href="{{ route('favicon') }}">
    <meta http-equiv="refresh" content="{{ config('session.lifetime') * 60 + 1 }}">
    @stack('meta')
    <script>
        {!! (new \WireUi\Support\BladeDirectives())->hooksScript() !!}
    </script>
    {{ \FluxErp\Facades\Asset::toHtml() }}
    @stack('scripts')
</head>
<body class="dark:bg-secondary-900 h-full bg-slate-50 transition duration-300">
    @persist('notifications')
        <x-notifications z-index="z-50"></x-notifications>
        <x-dialog align="center"/>
        <x-dialog align="center" id="prompt">
            <x-input id="prompt-value" />
        </x-dialog>
    @endpersist
    @auth('web')
        @persist('mail')
            <div id="mail">
                <livewire:edit-mail lazy />
            </div>
            <x-modal
                name="detail-modal"
                max-width="7xl"
                x-on:close="$el.querySelector('embed').src = 'data:text/html;charset=utf-8,%3Chtml%3E%3Cbody%3E%3C%2Fbody%3E%3C%2Fhtml%3E'"
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
                        <embed class="object-contain" height="100%" width="100%" id="detail-modal-embed" src="data:text/html;charset=utf-8,%3Chtml%3E%3Cbody%3E%3C%2Fbody%3E%3C%2Fhtml%3E" />
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
    @endauth
    <div x-data="{ open: false }" x-on:keydown.window.escape="open = false" class="flex h-screen w-full flex-col">
        @if(auth()->check() && method_exists(auth()->guard(), 'getName') && ! $navigation)
            @php($navigation = true)
            @persist('navigation')
                <div id="nav">
                    <livewire:navigation />
                </div>
            @endpersist
        @endif
        <div @if($navigation) class="md:pl-20" @endif>
            <main @if($navigation) class="px-1.5 md:px-8 pb-1.5 md:pb-8" @endif>
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
