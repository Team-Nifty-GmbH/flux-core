<head>
    <meta charset="UTF-8">
    <script src="https://unpkg.com/pagedjs/dist/paged.polyfill.js"></script>
    <title>{{ $title ?? '' }}</title>
    <x-dynamic-component
        :component="view()->exists('components.layouts.print.head.css.css_custom') ? 'layouts.print.head.css.css_custom' : 'layouts.print.head.css.css'"/>
    {{ $slot }}
</head>
