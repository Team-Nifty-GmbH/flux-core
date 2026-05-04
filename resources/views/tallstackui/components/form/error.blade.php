@php
    $customization = $classes();
@endphp

@if($property)
    <span
        x-cloak
        x-show="typeof $wire?.$errors?.has === 'function'
            ? $wire.$errors.has('{{ $property }}')
            : false"
        x-text="typeof $wire?.$errors?.first === 'function'
            ? ($wire.$errors.first('{{ $property }}') ?? '')
            : ''"
        class="{{ $customization['text'] }}"
    ></span>
@endif
