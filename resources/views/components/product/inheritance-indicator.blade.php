@if(! $shouldRenderChrome())
    {{ $slot }}
@else
    <div class="space-y-1">
        <div class="flex items-center gap-2">
            @if($isOverridden)
                <x-badge color="amber" sm> {{ __('Überschrieben') }} </x-badge>
                <x-button
                    icon="arrow-uturn-left"
                    color="secondary"
                    flat
                    sm
                    :title="__('Auf geerbt zurücksetzen')"
                    wire:click="{{ $resetMethod }}('{{ $field }}')"
                />
            @else
                <x-badge color="gray" sm> {{ __('Vererbt') }} </x-badge>
            @endif
        </div>
        {{ $slot }}
    </div>
@endif
