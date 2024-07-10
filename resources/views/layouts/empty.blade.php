@php

use FluxErp\Facades\Asset;
use FluxErp\Providers\ViewServiceProvider;

@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
{{--Todo: this approach regarding livewire doesent work - its loaded twice --}}
</head>
<body>
{{--{{ Asset::toHtml(--}}
{{--     ViewServiceProvider::getRealPackageAssetPath(--}}
{{--         '/resources/js/alpine.js',--}}
{{--         'team-nifty-gmbh/flux-erp'--}}
{{--     )--}}
{{-- )--}}
{{--}}--}}
@vite('packages/flux-core/resources/js/alpine.js')
@livewireScripts
{{ $slot}}
</body>
</html>
