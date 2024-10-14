@php
    $client = auth()->user()?->contact->client ?? app(\FluxErp\Models\Client::class)->first();
    $setting = $client?->settings()->where('key', 'customerPortal')->first()?->toArray() ?? [];
@endphp
<!DOCTYPE html>
<HTML class="font-portal h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-flux::layouts.head.head>
        <x-slot:title>
            {{ $client?->name . ' Portal' }}
        </x-slot:title>
        <style>
            {{ $setting['settings']['custom_css'] ?? '' }}
        </style>
    </x-flux::layouts.head.head>
</head>
<body class="dark:bg-secondary-900 h-full bg-gray-50 text-xs">
    <x-notifications z-index="z-50"></x-notifications>
    <x-dialog z-index="z-40" blur="md" align="center"/>
    <x-dialog z-index="z-40" blur="md" align="center" id="prompt">
        <x-input id="prompt-value" />
    </x-dialog>
    <x-flux::flash />
    <div class="absolute right-4 top-6 flex gap-1.5">
        <a href="{{ $client?->website }}" target="_blank" class="flex items-center dark:text-gray-50">
            <x-heroicons name="arrow-up-right" class="h-4 w-4" />
            <div class="return-to-website pl-4 font-bold">
                {{ __('Return to website') }}
            </div>
        </a>
        @auth('address')
            @can(route_to_permission('portal.checkout'))
                <x-button icon="heart" wire:navigate :href="route('portal.watchlist')" />
                @persist('cart')
                    <livewire:portal.shop.cart />
                @endpersist
            @endcan
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
