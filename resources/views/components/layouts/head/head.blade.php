<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="apple-mobile-web-app-capable" content="yes">
<link rel="manifest" href="/manifest.json">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="pusher-key" content="{{ config('broadcasting.connections.pusher.key') }}">
<meta name="pusher-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}">
<meta name="webpush-key" content="{{ config('webpush.vapid.public_key') }}">
<meta name="currency-code" content="{{ $defaultCurrency?->iso }}">
<link rel="icon" href="/favicon.svg">
<meta http-equiv="refresh" content="{{ config('session.lifetime') * 60 + 1 }}">
@stack('meta')
{!! WireUi::directives()->scripts(absolute: false) !!}
{{ \FluxErp\Facades\Asset::toHtml() }}
@stack('scripts')
{{$slot}}
