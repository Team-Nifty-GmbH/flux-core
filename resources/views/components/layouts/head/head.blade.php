<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="apple-mobile-web-app-capable" content="yes">
<link rel="manifest" href="{{ asset('/flux/manifest.json') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
@stack('meta')
<title>{{ $title ?? config('app.name', 'TNConnect') }}</title>
<livewire:scripts/>
{!! WireUi::directives()->scripts(absolute: false) !!}
<datatable:scripts />
@section('test')
    a
@endsection
@vite([
    'resources/js/app.js',
    'resources/js/alpine.js',
    'resources/css/app.css',
    'resources/js/tribute.js',
], 'flux/build')
<livewire:styles/>
@stack('scripts')
{{$slot}}
