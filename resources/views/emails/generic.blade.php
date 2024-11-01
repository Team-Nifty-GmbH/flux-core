<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="$client->website">
            <img
                style="max-height: 100px; max-width: 100px; width: auto; height: auto;"
                alt="logo"
                src="{{ $client->getFirstMediaUrl('logo_small') }}"
            />
        </x-mail::header>
    </x-slot:header>
    {{-- Body --}}
    {!! data_get($mailMessageForm, 'html_body') !!}

    {{-- Subcopy --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {{ $subcopy }}
            </x-mail::subcopy>
        </x-slot:subcopy>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            © {{ date('Y') }} {{ $client->name }}. @lang('All rights reserved.')
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>
