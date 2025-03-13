<div x-data="{
    table: @js($table),
}">
    <div
        x-bind:class="table && 'table w-full table-auto border-spacing-y-3'"
        class="space-y-2.5"
    >
        @foreach ($additionalColumns as $additionalColumn)
            @if ($additionalColumn["is_customer_editable"] || auth()->user() instanceof \FluxErp\Models\User)
                @if ($additionalColumn["values"] ?? false)
                    <x-select.styled
                        x-on:select="$wire.{{ $wire }}['{{ $additionalColumn['name'] }}'] = $event.detail.select.value"
                        wire:model="{{ $wire }}.{{ $additionalColumn['name'] }}"
                        :label="__($additionalColumn['label'] ?? $additionalColumn['name'])"
                        :options="$additionalColumn['values']"
                    />
                @elseif ($additionalColumn["field_type"] === "checkbox")
                    <x-checkbox
                        x-bind:disabled="!edit"
                        wire:model="{{ $wire }}.{{ $additionalColumn['name'] }}"
                        :label="__($additionalColumn['label'] ?? $additionalColumn['name'])"
                    />
                @else
                    <x-input
                        type="{{ $additionalColumn['field_type'] ?? 'text' }}"
                        x-bind:readonly="!edit"
                        wire:model="{{ $wire }}.{{ $additionalColumn['name'] }}"
                        :label="__($additionalColumn['label'] ?? $additionalColumn['name'])"
                    />
                @endif
            @else
                @if ($additionalColumn["field_type"] === "checkbox")
                    <x-checkbox
                        disabled
                        wire:model="{{ $wire }}.{{ $additionalColumn['name'] }}"
                        :label="__($additionalColumn['label'] ?? $additionalColumn['name'])"
                    />
                @else
                    <x-label
                        x-bind:class="table && 'table-cell'"
                        :label="__($additionalColumn['label'] ?? $additionalColumn['name'])"
                        x-bind:for="{{ $additionalColumn['name'] }}"
                    />
                    <span
                        x-text="{{ $wire }}.{{ $additionalColumn["name"] }}"
                        x-bind:for="{{ $additionalColumn["name"] }}"
                    />
                @endif
            @endif
        @endforeach
    </div>
</div>
