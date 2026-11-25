<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta
    name="viewport"
    content="width=device-width, initial-scale=1, maximum-scale=1"
/>
<meta name="mobile-web-app-capable" content="yes" />
<link rel="manifest" href="{{ route('manifest') }}" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
<meta
    name="user-authenticated"
    content="{{ auth()->check() ? 'true' : 'false' }}"
/>
<meta
    name="ws-broadcaster"
    content="{{ resolve_static(\FluxErp\Support\Broadcasting\BrowserBroadcaster::class, 'name') }}"
/>
<meta
    name="ws-mercure-hub"
    content="{{ config('broadcasting.connections.mercure.public_url') }}"
/>
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
@stack('scripts')
{{ $slot }}
