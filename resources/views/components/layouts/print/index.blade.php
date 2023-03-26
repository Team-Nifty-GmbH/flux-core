<!DOCTYPE html>
<html lang="en">
<x-dynamic-component :component="view()->exists('components.layouts.print.head.head_custom') ? 'layouts.print.head.head_custom' : 'layouts.print.head.head'">
    {{ $head ?? '' }}
</x-dynamic-component>
<body>
<div class="title">
    <x-dynamic-component :component="view()->exists('components.layouts.print.header.header_custom') ? 'layouts.print.header.header_custom' : 'layouts.print.header.header'">
        {{ $header ?? '' }}
    </x-dynamic-component>
</div>
<div class="footer">
    <x-dynamic-component :component="view()->exists('components.layouts.print.footer.footer_custom') ? 'layouts.print.footer.footer_custom' : 'layouts.print.footer.footer'">
        {{ $footer ?? '' }}
    </x-dynamic-component>
</div>
{{ $slot }}
</body>
</html>
