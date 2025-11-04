<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta
    name="viewport"
    content="width=device-width, initial-scale=1, maximum-scale=1"
/>
<meta name="mobile-web-app-capable" content="yes" />
<link rel="manifest" href="{{ route('manifest') }}" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
<meta name="ws-key" content="{{ config('flux.vite.reverb_app_key') }}" />
<meta
    name="ws-broadcaster"
    content="{{ config('broadcasting.default', 'reverb') }}"
/>
<meta name="ws-host" content="{{ config('flux.vite.reverb_host') }}" />
<meta name="ws-port" content="{{ config('flux.vite.reverb_port') }}" />
<meta name="ws-protocol" content="{{ config('flux.vite.reverb_protocol') }}" />
<meta name="webpush-key" content="{{ config('webpush.vapid.public_key') }}" />
<meta
    name="currency-code"
    content="{{ \Illuminate\Support\Number::defaultCurrency() }}"
/>
<link rel="icon" href="{{ route('favicon') }}" />
<meta
    http-equiv="refresh"
    content="{{ config('session.lifetime') * 60 + 1 }}"
/>
@stack('meta')
<tallstackui:script />
@vite([
    \FluxErp\Providers\ViewServiceProvider::getRealPackageAssetPath(
        'resources/css/app.css',
        'team-nifty-gmbh/flux-erp',
    ),
    \FluxErp\Providers\ViewServiceProvider::getRealPackageAssetPath(
        'resources/js/app.js',
        'team-nifty-gmbh/flux-erp',
    ),
    \FluxErp\Providers\ViewServiceProvider::getRealPackageAssetPath(
        'resources/js/apex-charts.js',
        'team-nifty-gmbh/flux-erp',
    ),
    \FluxErp\Providers\ViewServiceProvider::getRealPackageAssetPath(
        'resources/js/alpine.js',
        'team-nifty-gmbh/flux-erp',
    ),
    \FluxErp\Providers\ViewServiceProvider::getRealPackageAssetPath(
        'resources/js/sw.js',
        'team-nifty-gmbh/flux-erp',
    ),
    \FluxErp\Providers\ViewServiceProvider::getRealPackageAssetPath(
        'resources/js/nuxbe-bridge.js',
        'team-nifty-gmbh/flux-erp',
    ),
    \FluxErp\Providers\ViewServiceProvider::getRealPackageAssetPath(
        'resources/js/tall-datatables.js',
        'team-nifty-gmbh/tall-datatables',
    ),
])
@auth('web')
    @vite([
        \FluxErp\Providers\ViewServiceProvider::getRealPackageAssetPath(
            'resources/js/web-push.js',
            'team-nifty-gmbh/flux-erp',
        ),
    ])
    <script type="module">
        document.addEventListener(
            'livewire:navigated',
            () => {
                if (window.Echo && window.Echo.join) {
                    window.Echo.join('presence');
                }
            },
            { once: true },
        );
    </script>
@endauth

@stack('scripts')
{{ $slot }}
