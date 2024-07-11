@php
    $client = auth()->user()?->contact->client ?? app(\FluxErp\Models\Client::class)->first();
    $setting = $client?->settings()->where('key', 'customerPortal')->first()?->toArray() ?? [];
@endphp
<!DOCTYPE html>
<HTML class="font-portal h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ $title ?? $client?->name . ' Portal' }}</title>
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
    <style>
        {{ $setting['settings']['custom_css'] ?? '' }}
    </style>
</head>
<body class="dark:bg-secondary-900 h-full bg-gray-50 text-xs">
    <x-notifications z-index="z-50" />
    <x-dialog z-index="z-40" blur="md" align="center"/>
    <x-flux::flash />
    <div class="absolute right-4 top-6 flex gap-1.5">
        <a href="{{ $client?->website }}" target="_blank" class="flex items-center dark:text-gray-50">
            <x-icon name="arrow-up-right" class="h-4 w-4" />
            <div class="return-to-website pl-4 font-bold">
                {{ __('Return to website') }}
            </div>
        </a>
        @auth('address')
            <x-button icon="heart" wire:navigate :href="route('portal.watchlist')" />
            @persist('cart')
                <livewire:portal.shop.cart />
            @endpersist
        @endauth
    </div>
    @auth('address')
        <div id="nav">
            <livewire:navigation :show-search-bar="false" :setting="$setting"/>
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
