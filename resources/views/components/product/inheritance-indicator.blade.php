@if (! $shouldRenderChrome())
    {{ $slot }}
@else
    <div class="space-y-1">
        <div class="flex items-center gap-2">
            @if ($isOverridden)
                <x-badge color="amber" sm :text="__('Overridden')" />
                <x-button
                    icon="arrow-uturn-left"
                    color="secondary"
                    flat
                    sm
                    :title="__('Reset to inherited')"
                    wire:click="resetFields('{{ $field }}')"
                />
            @else
                <x-badge color="gray" sm :text="__('Inherited')" />
            @endif
        </div>
        {{ $slot }}
    </div>
@endif
