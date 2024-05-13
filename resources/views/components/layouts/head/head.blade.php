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
{{$slot}}
