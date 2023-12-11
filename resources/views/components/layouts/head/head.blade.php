<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="apple-mobile-web-app-capable" content="yes">
<link rel="manifest" href="{{ asset('/flux/manifest.json') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="pusher-key" content="{{ config('broadcasting.connections.pusher.key') }}">
<meta name="pusher-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}">
<meta name="webpush-key" content="{{ config('webpush.vapid.public_key') }}">
<meta name="currency-code" content="{{ $defaultCurrency?->iso }}">
<meta http-equiv="refresh" content="{{ config('session.lifetime') * 60 + 1 }}">
@stack('meta')
<title>{{ $title ?? config('app.name', 'Flux ERP') }}</title>
{!! WireUi::directives()->scripts(absolute: false) !!}
@vite([
    'resources/js/app.js',
    'resources/js/alpine.js',
    'resources/js/apex-charts.js',
    'resources/css/app.css',
], 'flux/build')
@if(auth()->check() && in_array(\NotificationChannels\WebPush\HasPushSubscriptions::class, class_uses_recursive(auth()->user())))
    @vite('resources/js/web-push.js', 'flux/build')
@endif
@stack('scripts')
{{$slot}}
