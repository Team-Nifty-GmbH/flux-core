<html
    class="h-full text-sm"
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
>
    @use('FluxErp\Facades\Asset')
    @use('FluxErp\Providers\ViewServiceProvider')
    <head>
        @section('head')
        @section('head.meta')
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta
            name="currency-code"
            content="{{ resolve_static(\FluxErp\Models\Currency::class, 'default')?->iso }}"
        />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <title>{{ $title ?? ($subject ?? '') }}</title>
        @show
        @section('head.assets')
        <tallstackui:script />
        @vite(ViewServiceProvider::getRealPackageAssetPath('/resources/css/app.css', 'team-nifty-gmbh/flux-erp'))
        @vite(ViewServiceProvider::getRealPackageAssetPath('/resources/js/print-editor.js', 'team-nifty-gmbh/flux-erp'))
        <link
            href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
            rel="stylesheet"
        />
        @show
        @show
    </head>
    <body
        class="flex h-screen w-screen items-center justify-center bg-gray-50 text-xs"
    >
        @persist('notifications')
            @if (auth()->check() && auth()->id())
                <div
                    id="{{ \Illuminate\Support\Str::uuid() }}"
                    x-on:tallstackui:toast-upsert.window="$tallstackuiToast($el.id).upsertToast($event)"
                >
                    <x-toast z-index="z-50"></x-toast>
                </div>
            @endif

            <x-dialog z-index="z-40" blur="md" align="center" />
        @endpersist

        {{ $slot }}
    </body>
</html>
