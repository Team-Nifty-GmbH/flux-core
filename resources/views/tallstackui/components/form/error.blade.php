@php
    $customization = $classes();
@endphp

@if($property)
    <span
        x-cloak
        x-show="$wire?.$errors?.has('{{ $property }}')"
        x-text="$wire?.$errors?.first('{{ $property }}')"
        class="{{ $customization['text'] }}"
    >
    </span>
@endif
