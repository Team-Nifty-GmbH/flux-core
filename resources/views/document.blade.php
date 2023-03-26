<!DOCTYPE html>
<html style="height:100%">
<head>
    <title></title>
</head>
<body style="margin: 0; height:100%; overflow: hidden">
@php
    empty($additionalComponents) ? $additionalComponents = [] : $additionalComponents;
    empty($additionalComponentsHead) ? $additionalComponentsHead = [] : $additionalComponentsHead;
@endphp
@foreach($additionalComponentsHead as $additionalComponent)
    <x-dynamic-component
        :component="view()->exists('components.' . $additionalComponent) ? $additionalComponent : 'layouts.empty'"/>
@endforeach
<iframe style="width: 100%; height: 100%; border: none;" src="{{ route('print.public-html-show', $uuid) }}"></iframe>

@foreach($additionalComponents as $additionalComponent)
    <x-dynamic-component
        :component="view()->exists('components.' . $additionalComponent) ? $additionalComponent : 'layouts.empty'"/>
@endforeach
</body>
</html>
